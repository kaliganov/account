<?php

namespace App\Http\Controllers;

use App\Models\Counterparty;
use Illuminate\Http\Request;

class CounterpartyController extends Controller
{
    public function index()
    {
        $counterparties = Counterparty::query()
            ->where('user_id', auth()->id())
            ->orderBy('id')
            ->paginate(20);

        return view('counterparties.index', compact('counterparties'));
    }

    public function create()
    {
        $counterparty = new Counterparty();

        return view('counterparties.form', [
            'counterparty' => $counterparty,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);
        $validated['user_id'] = $request->user()->id;

        Counterparty::create($validated);

        return redirect()
            ->route('counterparties.index')
            ->with('status', 'Контрагент добавлен.');
    }

    public function edit(Counterparty $counterparty)
    {
        abort_unless($counterparty->user_id === auth()->id(), 403);

        return view('counterparties.form', [
            'counterparty' => $counterparty,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Counterparty $counterparty)
    {
        abort_unless($counterparty->user_id === auth()->id(), 403);

        $validated = $this->validatedData($request);

        $counterparty->update($validated);

        return redirect()
            ->route('counterparties.index')
            ->with('status', 'Контрагент обновлён.');
    }

    public function destroy(Counterparty $counterparty)
    {
        abort_unless($counterparty->user_id === auth()->id(), 403);

        $counterparty->delete();

        return redirect()
            ->route('counterparties.index')
            ->with('status', 'Контрагент удалён.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'inn' => ['required', 'regex:/^\d+$/', 'max:20'],
            'contract_number' => ['required', 'string', 'max:100'],
            'contract_date' => ['required', 'date'],
            'contract_price' => ['required', 'decimal:0,2', 'min:0'],
        ]);
    }
}

