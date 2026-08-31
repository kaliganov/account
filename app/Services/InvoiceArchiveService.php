<?php

namespace App\Services;

use App\Models\Counterparty;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class InvoiceArchiveService
{
    public function __construct(private readonly InvoicePdfGenerator $generator) {}

    /**
     * @param  Collection<int, Counterparty>  $counterparties
     * @return array{month: string, start: int, next: int, count: int, token: string}
     */
    public function build(User $user, string $month, int $startNumber, Collection $counterparties): array
    {
        if ($counterparties->isEmpty()) {
            throw new RuntimeException('Нет контрагентов для формирования счетов.');
        }

        InvoicePeriod::parse($month);

        return DB::transaction(function () use ($user, $month, $startNumber, $counterparties) {
            /** @var User $locked */
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            $locked->check_number = $startNumber;
            $locked->save();

            $token = bin2hex(random_bytes(16));
            $dir = storage_path('app/tmp/invoices/'.$locked->id);
            File::ensureDirectoryExists($dir);
            $zipPath = $dir.'/invoices_'.$month.'_'.$token.'.zip';

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Не удалось создать ZIP-архив.');
            }

            try {
                $number = $startNumber;
                $issuedOn = InvoicePeriod::invoiceDate($month);

                foreach ($counterparties as $counterparty) {
                    $pdf = $this->generator->generate($counterparty, $month, $number, 1);
                    $zip->addFromString($pdf['filename'], $pdf['content']);

                    Invoice::query()->create([
                        'user_id' => $locked->id,
                        'counterparty_id' => $counterparty->id,
                        'number' => $number,
                        'period' => $month,
                        'issued_on' => $issuedOn->toDateString(),
                        'amount' => $counterparty->contract_price,
                        'filename' => $pdf['filename'],
                    ]);

                    $number++;
                }

                $zip->close();
            } catch (\Throwable $e) {
                $zip->close();
                File::delete($zipPath);
                throw $e;
            }

            $locked->check_number = $number;
            $locked->save();

            return [
                'month' => $month,
                'start' => $startNumber,
                'next' => $number,
                'count' => $counterparties->count(),
                'token' => $token,
            ];
        });
    }

    public function zipPath(User $user, string $month, string $token): string
    {
        return storage_path('app/tmp/invoices/'.$user->id.'/invoices_'.$month.'_'.$token.'.zip');
    }
}
