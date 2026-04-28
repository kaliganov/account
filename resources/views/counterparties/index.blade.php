@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 mb-0">Контрагенты</h1>
        <a class="btn btn-primary btn-sm" href="{{ route('counterparties.create') }}">Добавить</a>
    </div>

    <div class="card shadow-sm">
        <form method="post" action="{{ route('counterparties.archive.download') }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                    <tr>
                        <th style="width: 36px;">
                            <input class="form-check-input" type="checkbox" id="select-all-counterparties">
                        </th>
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
                            <td>
                                <input
                                    class="form-check-input js-counterparty-checkbox"
                                    type="checkbox"
                                    name="counterparty_ids[]"
                                    value="{{ $c->id }}"
                                >
                            </td>
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
                            <td colspan="8" class="text-center text-muted py-4">Пока нет контрагентов</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-body border-top d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <span class="text-muted small">Выберите контрагентов для добавления в архив.</span>
                <button class="btn btn-primary" type="submit">Скачать архив</button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const selectAll = document.getElementById('select-all-counterparties');
            const checkboxes = Array.from(document.querySelectorAll('.js-counterparty-checkbox'));
            if (!selectAll || checkboxes.length === 0) {
                return;
            }

            const refreshSelectAllState = () => {
                const checkedCount = checkboxes.filter((item) => item.checked).length;
                selectAll.checked = checkedCount > 0 && checkedCount === checkboxes.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            };

            selectAll.addEventListener('change', function () {
                checkboxes.forEach((item) => {
                    item.checked = selectAll.checked;
                });
                refreshSelectAllState();
            });

            checkboxes.forEach((item) => {
                item.addEventListener('change', refreshSelectAllState);
            });

            refreshSelectAllState();
        })();
    </script>

    <div class="mt-3">
        {{ $counterparties->links('pagination::bootstrap-5') }}
    </div>
@endsection

