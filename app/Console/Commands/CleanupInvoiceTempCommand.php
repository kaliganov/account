<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanupInvoiceTempCommand extends Command
{
    protected $signature = 'invoices:cleanup-tmp {--hours=24 : Удалять ZIP старше N часов}';

    protected $description = 'Удаляет нескачанные временные ZIP со счетами';

    public function handle(): int
    {
        $root = storage_path('app/tmp/invoices');
        if (! File::isDirectory($root)) {
            $this->info('Каталог временных архивов пуст.');

            return self::SUCCESS;
        }

        $threshold = time() - ((int) $this->option('hours') * 3600);
        $deleted = 0;

        foreach (File::allFiles($root) as $file) {
            if ($file->getExtension() !== 'zip') {
                continue;
            }
            if ($file->getMTime() > $threshold) {
                continue;
            }
            File::delete($file->getPathname());
            $deleted++;
        }

        $this->info("Удалено архивов: {$deleted}.");

        return self::SUCCESS;
    }
}
