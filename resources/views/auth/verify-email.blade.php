@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Подтверждение email</h1>
                    <p class="text-muted mb-3">
                        Мы отправили письмо со ссылкой для подтверждения на ваш email.
                        Перейдите по ссылке в письме, чтобы завершить регистрацию.
                    </p>

                    <form method="post" action="{{ route('verification.send') }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-primary">Отправить письмо повторно</button>
                    </form>

                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Выйти</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
