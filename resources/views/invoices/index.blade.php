@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 mb-0">История счетов</h1>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('home') }}">К контрагентам</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead>
                <tr>
                    <th>Номер</th>
                    <th>Период</th>
                    <th>Дата в счёте</th>
                    <th>Контрагент</th>
                    <th class="text-end">Сумма</th>
                    <th>Файл</th>
                    <th>Создан</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->number }}</td>
                        <td>{{ substr($invoice->period, 5, 2) }}-{{ substr($invoice->period, 0, 4) }}</td>
                        <td>{{ optional($invoice->issued_on)->format('d.m.Y') }}</td>
                        <td>{{ $invoice->counterparty?->name ?? 'Удалённый контрагент' }}</td>
                        <td class="text-end">{{ $invoice->amount !== null ? number_format((float) $invoice->amount, 2, '.', ' ') : '' }}</td>
                        <td class="text-muted">{{ $invoice->filename }}</td>
                        <td class="text-muted">{{ $invoice->created_at?->timezone(config('app.timezone'))->format('d.m.Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Счета ещё не формировались</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $invoices->links('pagination::bootstrap-5') }}
    </div>
@endsection
