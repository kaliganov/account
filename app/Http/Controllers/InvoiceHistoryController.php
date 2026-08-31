<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::query()
            ->where('user_id', $request->user()->id)
            ->with(['counterparty' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('id')
            ->paginate(30);

        return view('invoices.index', [
            'invoices' => $invoices,
        ]);
    }
}
