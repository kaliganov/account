<?php

namespace App\Http\Controllers;

use App\Models\Counterparty;
use App\Services\InvoicePdfGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $now = CarbonImmutable::now();
        $currentYear = (int) $now->format('Y');

        $months = [];
        for ($m = 12; $m >= 1; $m--) {
            $months[] = [
                'value' => sprintf('%04d-%02d', $currentYear, $m),
                'label' => CarbonImmutable::create($currentYear, $m, 1)->locale('ru')->translatedFormat('F Y'),
            ];
        }

        $selectedMonth = old('month', $now->format('Y-m'));

        return view('home.index', [
            'months' => $months,
            'selectedMonth' => $selectedMonth,
            'checkNumber' => old('check_number', $request->user()->check_number ?? 1),
            'archive' => session('archive'),
        ]);
    }

    public function generate(Request $request)
    {
        $now = CarbonImmutable::now();
        $currentYear = (int) $now->format('Y');

        $allowedMonths = [];
        for ($m = 12; $m >= 1; $m--) {
            $allowedMonths[] = sprintf('%04d-%02d', $currentYear, $m);
        }

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m', 'in:'.implode(',', $allowedMonths)],
            'check_number' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $user->check_number = (int) $validated['check_number'];
        $user->save();

        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Counterparty> $counterparties */
        $counterparties = Counterparty::query()
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->get();

        $start = (int) $user->check_number;
        $n = $start;

        $token = bin2hex(random_bytes(16));
        $dir = storage_path('app/tmp/invoices/'.$user->id);
        File::ensureDirectoryExists($dir);

        $zipPath = $dir."/invoices_{$validated['month']}_{$token}.zip";

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Не удалось создать ZIP-архив.');
        }

        /** @var InvoicePdfGenerator $generator */
        $generator = app(InvoicePdfGenerator::class);

        foreach ($counterparties as $c) {
            $pdf = $generator->generate($c, $validated['month'], $n, 1);
            $zip->addFromString($pdf['filename'], $pdf['content']);
            $n++;
        }

        $zip->close();

        $user->check_number = $n;
        $user->save();

        return redirect()
            ->route('home')
            ->withInput([
                'month' => $validated['month'],
                'check_number' => $user->check_number,
            ])
            ->with('archive', [
                'month' => $validated['month'],
                'start' => $start,
                'next' => $user->check_number,
                'count' => $counterparties->count(),
                'token' => $token,
            ])
            ->with('status', 'Архив счетов сформирован.');
    }

    public function downloadArchive(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'size:32'],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $user = $request->user();
        $dir = storage_path('app/tmp/invoices/'.$user->id);
        $zipPath = $dir."/invoices_{$validated['month']}_{$validated['token']}.zip";

        if (! File::exists($zipPath)) {
            abort(404, 'Архив не найден или уже скачан.');
        }

        $filename = "invoices_".substr($validated['month'], 5, 2).'-'.substr($validated['month'], 0, 4).'_'.rand(10000, 99999).".zip";

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }
}

