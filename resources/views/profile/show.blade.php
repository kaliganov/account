@extends('layouts.app')

@section('content')
    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h1 class="h4 mb-3">Профиль пользователя</h1>

                    <dl class="row mb-0">
                        <dt class="col-5">Имя</dt>
                        <dd class="col-7">{{ $user->name }}</dd>

                        <dt class="col-5">Email</dt>
                        <dd class="col-7">{{ $user->email }}</dd>

                        <dt class="col-5">Роль</dt>
                        <dd class="col-7">{{ $user->is_admin ? 'Администратор' : 'Пользователь' }}</dd>

                        <dt class="col-5">Статус</dt>
                        <dd class="col-7">{{ $user->is_approved ? 'Активен' : 'Не активен' }}</dd>

                        <dt class="col-5">Почта</dt>
                        <dd class="col-7">{{ $user->hasVerifiedEmail() ? 'Подтверждена' : 'Не подтверждена' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Смена пароля</h2>

                    <form method="post" action="{{ route('profile.password') }}" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-12">
                            <label class="form-label" for="current_password">Текущий пароль</label>
                            <input
                                id="current_password"
                                type="password"
                                name="current_password"
                                class="form-control @error('current_password') is-invalid @enderror"
                                autocomplete="current-password"
                                required
                            >
                            @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="password">Новый пароль</label>
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

                        <div class="col-12">
                            <label class="form-label" for="password_confirmation">Подтверждение нового пароля</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">Сохранить новый пароль</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
