<?php

namespace App\Http\Controllers;

use App\Models\Counterparty;
use Illuminate\Http\Request;
use App\Services\InvoicePdfGenerator;

class InvoicePdfController extends Controller
{
    public function download(Request $request, Counterparty $counterparty)
    {
        abort_unless($counterparty->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'month' => ['required', 'date_format:m-Y'],
            'number' => ['required', 'integer', 'min:1'],
            'items_count' => ['nullable', 'integer', 'min:1'],
        ]);

        $month = $validated['month'];
        $number = (int) $validated['number'];
        $itemsCount = (int) ($validated['items_count'] ?? 1);
        $generator = app(InvoicePdfGenerator::class);
        $result = $generator->generate($counterparty, $month, $number, $itemsCount);

        return response($result['content'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$result['filename'].'"',
        ]);
    }
}

