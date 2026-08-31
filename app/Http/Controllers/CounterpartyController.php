<?php

namespace App\Http\Controllers;

use App\Models\Counterparty;
use App\Services\InvoicePeriod;
use App\Support\Inn;
use Illuminate\Http\Request;

class CounterpartyController extends Controller
{
    public function index(Request $request)
    {
        $now = now();
        $selectedMonth = old('month', $now->format('Y-m'));

        $counterparties = Counterparty::query()
            ->where('user_id', auth()->id())
            ->orderBy('id')
            ->paginate(200);

        return view('counterparties.index', [
            'counterparties' => $counterparties,
            'months' => InvoicePeriod::options(),
            'selectedMonth' => $selectedMonth,
            'checkNumber' => old('check_number', $request->user()->check_number ?? 1),
            'archive' => session('archive'),
            'totalCounterparties' => $counterparties->total(),
        ]);
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
        $request->user()->counterparties()->create($validated);

        return redirect()
            ->route('home')
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
            ->route('home')
            ->with('status', 'Контрагент обновлён.');
    }

    public function destroy(Counterparty $counterparty)
    {
        abort_unless($counterparty->user_id === auth()->id(), 403);

        $counterparty->delete();

        return redirect()
            ->route('home')
            ->with('status', 'Контрагент удалён.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'inn' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! Inn::isValid((string) $value)) {
                        $fail('Укажите корректный ИНН (10 или 12 цифр).');
                    }
                },
            ],
            'contract_number' => ['required', 'string', 'max:100'],
            'contract_date' => ['required', 'date'],
            'contract_price' => ['required', 'decimal:0,2', 'min:0'],
        ]);
    }
}
