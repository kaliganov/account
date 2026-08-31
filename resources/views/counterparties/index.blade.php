@extends('layouts.app')

@section('content')
    <div class="card shadow-sm mb-3">
        <form method="post" action="{{ route('home.generate') }}" id="invoices-form">
            @csrf
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h1 class="h4 mb-0">Контрагенты и формирование счетов</h1>
                    <a class="btn btn-primary btn-sm" href="{{ route('counterparties.create') }}">Добавить контрагента</a>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-2">
                        <label class="form-label" for="month">Месяц</label>
                        <select id="month" name="month" class="form-select @error('month') is-invalid @enderror" required>
                            @foreach ($months as $m)
                                <option value="{{ $m['value'] }}" {{ $selectedMonth === $m['value'] ? 'selected' : '' }}>
                                    {{ $m['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('month')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="check_number">Стартовый номер счета</label>
                        <input
                            id="check_number"
                            type="number"
                            min="1"
                            step="1"
                            name="check_number"
                            value="{{ $checkNumber }}"
                            class="form-control @error('check_number') is-invalid @enderror"
                            required
                        >
                        @error('check_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2 flex-wrap align-items-end">
                        <button class="btn btn-primary" type="submit" id="generate-invoices-btn">Сформировать счета</button>
                        @if ($archive)
                            <a class="btn btn-success" href="{{ route('home.archive.download', ['month' => $archive['month'], 'token' => $archive['token']]) }}">
                                Скачать архив счетов ({{ $archive['count'] }})
                            </a>
                        @endif
                    </div>
                </div>

                @if ($archive)
                    <div class="mt-3 text-muted">
                        Архив сформирован для {{ substr($archive['month'], 5, 2) }}-{{ substr($archive['month'], 0, 4) }}. Диапазон номеров: {{ $archive['start'] }} → {{ $archive['next'] - 1 }}.
                    </div>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                    <tr>
                        <th style="width: 36px;">
                            <input class="form-check-input" type="checkbox" id="select-all-counterparties" @disabled($counterparties->isEmpty())>
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
                                <a
                                    class="btn btn-outline-primary btn-sm js-invoice-preview"
                                    href="{{ route('counterparties.invoice_pdf', $c) }}"
                                    data-preview-url="{{ route('counterparties.invoice_pdf', $c) }}"
                                    target="_blank"
                                    rel="noopener"
                                >Превью PDF</a>
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

            <div class="card-body border-top">
                <p class="text-muted small mb-0">
                    Без отметок — счета для всех {{ $totalCounterparties }} контрагентов.
                    С отметками — только выбранные на этой странице.
                </p>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const form = document.getElementById('invoices-form');
            const monthSelect = document.getElementById('month');
            const selectAll = document.getElementById('select-all-counterparties');
            const checkboxes = Array.from(document.querySelectorAll('.js-counterparty-checkbox'));
            const totalAll = {{ (int) $totalCounterparties }};

            const refreshSelectAllState = () => {
                if (!selectAll || checkboxes.length === 0) {
                    return;
                }
                const checkedCount = checkboxes.filter((item) => item.checked).length;
                selectAll.checked = checkedCount > 0 && checkedCount === checkboxes.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            };

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    checkboxes.forEach((item) => {
                        item.checked = selectAll.checked;
                    });
                    refreshSelectAllState();
                });
            }

            checkboxes.forEach((item) => {
                item.addEventListener('change', refreshSelectAllState);
            });

            document.querySelectorAll('.js-invoice-preview').forEach((link) => {
                link.addEventListener('click', function () {
                    const base = link.getAttribute('data-preview-url');
                    const month = monthSelect ? monthSelect.value : '';
                    link.href = base + '?month=' + encodeURIComponent(month);
                });
            });

            if (form) {
                form.addEventListener('submit', function (event) {
                    const checked = checkboxes.filter((item) => item.checked).length;
                    const count = checked > 0 ? checked : totalAll;
                    const monthLabel = monthSelect && monthSelect.selectedOptions[0]
                        ? monthSelect.selectedOptions[0].text.trim()
                        : '';

                    if (count < 1) {
                        event.preventDefault();
                        alert('Нет контрагентов для формирования счетов.');
                        return;
                    }

                    const ok = confirm('Сформировать счета для ' + count + ' контрагентов за ' + monthLabel + '? Номера счетов будут зарезервированы.');
                    if (!ok) {
                        event.preventDefault();
                    }
                });
            }

            refreshSelectAllState();
        })();
    </script>

    <div class="mt-3">
        {{ $counterparties->links('pagination::bootstrap-5') }}
    </div>
@endsection
