<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateInvoicesRequest;
use App\Models\Counterparty;
use App\Services\InvoiceArchiveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    public function generate(GenerateInvoicesRequest $request, InvoiceArchiveService $archives)
    {
        $validated = $request->validated();
        $user = $request->user();
        $requestedIds = array_values(array_unique(array_map('intval', $request->input('counterparty_ids', []))));

        $query = Counterparty::query()
            ->where('user_id', $user->id)
            ->orderBy('id');

        if ($requestedIds !== []) {
            $query->whereIn('id', $requestedIds);
        }

        $counterparties = $query->get();

        if ($requestedIds !== [] && $counterparties->count() !== count($requestedIds)) {
            abort(403);
        }

        if ($counterparties->isEmpty()) {
            return back()
                ->withInput($request->only('month', 'check_number'))
                ->withErrors(['status' => 'Нет контрагентов для формирования счетов.']);
        }

        $archive = $archives->build(
            $user,
            $validated['month'],
            (int) $validated['check_number'],
            $counterparties,
        );

        $user->refresh();

        return redirect()
            ->route('home')
            ->withInput([
                'month' => $validated['month'],
                'check_number' => $user->check_number,
            ])
            ->with('archive', $archive)
            ->with('status', 'Архив счетов сформирован.');
    }

    public function downloadArchive(Request $request, InvoiceArchiveService $archives)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'size:32'],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $user = $request->user();
        $zipPath = $archives->zipPath($user, $validated['month'], $validated['token']);

        if (! File::exists($zipPath)) {
            abort(404, 'Архив не найден или уже скачан.');
        }

        $filename = 'invoices_'.substr($validated['month'], 5, 2).'-'.substr($validated['month'], 0, 4).'_'.random_int(10000, 99999).'.zip';

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }
}
