<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>

    @if (file_exists(public_path('favicon.png')))
        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    @endif

    <title>{{ $title ?? 'Формирование счетов' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand me-5" href="{{ route('home') }}">СчетOn</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav" aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="topNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('counterparties.index') ? 'active' : '' }}" href="{{ route('counterparties.index') }}">Список контрагентов</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('counterparties.create') ? 'active' : '' }}" href="{{ route('counterparties.create') }}">Создать контрагента</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.show') }}">Профиль</a>
                    </li>
                    @if (auth()->user()->is_admin)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Админка</a>
                        </li>
                    @endif
                @endauth
            </ul>

            <div class="d-flex gap-2">
                @auth
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-light btn-sm" type="submit">Выйти</button>
                    </form>
                @else
                    <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Войти</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Регистрация</a>
                @endauth
                <button class="btn btn-outline-light btn-sm" type="button" id="themeToggle">Темная тема</button>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->has('status'))
        <div class="alert alert-danger">{{ $errors->first('status') }}</div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const button = document.getElementById('themeToggle');
        if (!button) {
            return;
        }

        const getCurrentTheme = () => document.documentElement.getAttribute('data-bs-theme') || 'light';

        const updateButtonText = () => {
            button.textContent = getCurrentTheme() === 'dark' ? 'Светлая тема' : 'Темная тема';
        };

        button.addEventListener('click', function () {
            const nextTheme = getCurrentTheme() === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', nextTheme);
            localStorage.setItem('theme', nextTheme);
            updateButtonText();
        });

        updateButtonText();
    })();
</script>
</body>
</html>

