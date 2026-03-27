@extends('layouts.app')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Формирование счетов</h1>

            <form method="post" action="{{ route('home.generate') }}" class="row g-3">
                @csrf

                <div class="col-12 col-md-2">
                    <label class="form-label" for="month">Месяц</label>
                    <select id="month" name="month" class="form-select @error('month') is-invalid @enderror" required>
                        @foreach ($months as $m)
                            <option value="{{ $m['value'] }}" {{ $selectedMonth === $m['value'] ? 'selected' : '' }}>
                                {{ mb_convert_case($m['label'], MB_CASE_TITLE, 'UTF-8') }}
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

                <div class="col-12 d-flex flex-wrap gap-2 mt-5">
                    <button class="btn btn-primary" type="submit">Сформировать счета</button>
                    <a class="btn btn-outline-secondary" href="{{ route('counterparties.index') }}">Контрагенты</a>

                    @if ($archive)
                        <a class="btn btn-success" href="{{ route('home.archive.download', ['month' => $archive['month'], 'token' => $archive['token']]) }}">
                            Скачать архив счетов ({{ $archive['count'] }})
                        </a>
                    @endif
                </div>
            </form>

            @if ($archive)
                <div class="mt-3 text-muted">
                    Архив сформирован для {{ substr($archive['month'], 5, 2) }}-{{ substr($archive['month'], 0, 4) }}. Диапазон номеров: {{ $archive['start'] }} → {{ $archive['next'] - 1 }}.
                </div>
            @endif
        </div>
    </div>
@endsection

