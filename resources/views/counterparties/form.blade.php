@extends('layouts.app')

@php
    /** @var \App\Models\Counterparty $counterparty */
    $isEdit = ($mode ?? 'create') === 'edit';
@endphp

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 mb-0">{{ $isEdit ? 'Редактирование контрагента' : 'Создание контрагента' }}</h1>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('counterparties.index') }}">К списку</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form
                method="post"
                action="{{ $isEdit ? route('counterparties.update', $counterparty) : route('counterparties.store') }}"
                class="row g-3"
            >
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="col-12">
                    <label class="form-label" for="name">Название компании</label>
                    <input
                        id="name"
                        name="name"
                        value="{{ old('name', $counterparty->name) }}"
                        class="form-control @error('name') is-invalid @enderror"
                        required
                    >
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label" for="inn">ИНН компании</label>
                    <input
                        id="inn"
                        name="inn"
                        inputmode="numeric"
                        pattern="\d*"
                        value="{{ old('inn', $counterparty->inn) }}"
                        class="form-control @error('inn') is-invalid @enderror"
                    >
                    @error('inn')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label" for="contract_number">Номер договора</label>
                    <input
                        id="contract_number"
                        name="contract_number"
                        value="{{ old('contract_number', $counterparty->contract_number) }}"
                        class="form-control @error('contract_number') is-invalid @enderror"
                    >
                    @error('contract_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label" for="contract_date">Дата договора</label>
                    <input
                        id="contract_date"
                        type="date"
                        name="contract_date"
                        value="{{ old('contract_date', optional($counterparty->contract_date)->format('Y-m-d')) }}"
                        class="form-control @error('contract_date') is-invalid @enderror"
                    >
                    @error('contract_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label" for="contract_price">Сумма договора</label>
                    <input
                        id="contract_price"
                        type="text"
                        name="contract_price"
                        placeholder="0.00"
                        value="{{ old('contract_price', $counterparty->contract_price) }}"
                        class="form-control @error('contract_price') is-invalid @enderror"
                    >
                    @error('contract_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Например: 1000.00</div>
                </div>

                <div class="col-12 d-flex justify-content-between align-items-center gap-2 mt-5">
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">{{ $isEdit ? 'Сохранить' : 'Создать' }}</button>
                        <a class="btn btn-outline-secondary" href="{{ route('counterparties.index') }}">Отмена</a>
                    </div>

                    @if ($isEdit)
                        <form
                            method="post"
                            action="{{ route('counterparties.destroy', $counterparty) }}"
                            onsubmit="return confirm('Вы уверены, что хотите удалить этого контрагента?');"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                Удалить контрагента
                            </button>
                        </form>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection

