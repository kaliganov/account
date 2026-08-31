<?php

namespace App\Http\Controllers;

use App\Models\Counterparty;
use App\Services\InvoicePdfGenerator;
use App\Services\InvoicePeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoicePdfController extends Controller
{
    public function download(Request $request, Counterparty $counterparty)
    {
        abort_unless($counterparty->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m', Rule::in(InvoicePeriod::allowedValues())],
        ]);

        $number = (int) ($request->user()->check_number ?? 1);
        $generator = app(InvoicePdfGenerator::class);
        $result = $generator->generate($counterparty, $validated['month'], $number, 1);

        return response($result['content'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$result['filename'].'"',
        ]);
    }
}
