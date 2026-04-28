<?php

namespace App\Http\Controllers;

use App\Models\Counterparty;
use App\Services\InvoicePdfGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CounterpartyController extends Controller
{
    public function index()
    {
        $counterparties = Counterparty::query()
            ->where('user_id', auth()->id())
            ->orderBy('id')
            ->paginate(100);

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

    public function downloadArchive(Request $request)
    {
        $validated = $request->validate([
            'counterparty_ids' => ['required', 'array', 'min:1'],
            'counterparty_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $requestedIds = array_map('intval', $validated['counterparty_ids']);

        $counterparties = Counterparty::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('id', $requestedIds)
            ->orderBy('id')
            ->get();

        abort_if($counterparties->count() !== count($requestedIds), 403);

        $user = $request->user();
        $month = CarbonImmutable::now()->format('Y-m');
        $startNumber = max(1, (int) ($user->check_number ?? 1));
        $currentNumber = $startNumber;

        $token = bin2hex(random_bytes(8));
        $dir = storage_path('app/tmp/invoices/'.$user->id);
        File::ensureDirectoryExists($dir);
        $zipPath = $dir."/selected_invoices_{$month}_{$token}.zip";

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Не удалось создать ZIP-архив.');
        }

        /** @var InvoicePdfGenerator $generator */
        $generator = app(InvoicePdfGenerator::class);

        foreach ($counterparties as $counterparty) {
            $pdf = $generator->generate($counterparty, $month, $currentNumber, 1);
            $zip->addFromString($pdf['filename'], $pdf['content']);
            $currentNumber++;
        }

        $zip->close();

        $user->check_number = $currentNumber;
        $user->save();

        $downloadName = 'selected_invoices_'.CarbonImmutable::now()->format('d-m-Y_His').'.zip';

        return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
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

