@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Регистрация</h1>

                    <form method="post" action="{{ route('register.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="name">Имя</label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror"
                                autocomplete="name"
                                required
                            >
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror"
                                autocomplete="email"
                                required
                            >
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Пароль</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="new-password"
                                required
                            >
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password_confirmation">Подтверждение пароля</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <button class="btn btn-primary w-100" type="submit">Создать аккаунт</button>
                    </form>

                    <div class="text-center mt-3">
                        <span class="text-muted">Уже есть аккаунт?</span>
                        <a href="{{ route('login') }}">Войти</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
