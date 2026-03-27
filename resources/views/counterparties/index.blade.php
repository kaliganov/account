@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 mb-0">Контрагенты</h1>
        <a class="btn btn-primary btn-sm" href="{{ route('counterparties.create') }}">Добавить</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th style="width: 60px;">№</th>
                    <th>Название</th>
                    <th>ИНН</th>
                    <th>Номер договора</th>
                    <th>Дата договора</th>
                    <th class="text-end">Сумма</th>
                    <th class="text-end">Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($counterparties as $c)
                    <tr>
                        <td class="text-muted">
                            {{ ($counterparties->firstItem() ?? 0) + $loop->index }}
                        </td>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->inn }}</td>
                        <td>{{ $c->contract_number }}</td>
                        <td>{{ optional($c->contract_date)->format('d.m.Y') }}</td>
                        <td class="text-end">{{ $c->contract_price !== null ? number_format((float) $c->contract_price, 2, '.', ' ') : '' }}</td>
                        <td class="text-end">
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('counterparties.edit', $c) }}">Редактировать</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Пока нет контрагентов</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $counterparties->links('pagination::bootstrap-5') }}
    </div>
@endsection

