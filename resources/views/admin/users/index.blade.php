@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 mb-0">Пользователи</h1>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Почта</th>
                    <th>Аккаунт</th>
                    <th>Роль</th>
                    <th class="text-end">Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->hasVerifiedEmail())
                                <span class="badge text-bg-success">Подтверждена</span>
                            @else
                                <span class="badge text-bg-warning">Не подтверждена</span>
                            @endif
                        </td>
                        <td>
                            @if ($user->is_approved)
                                <span class="badge text-bg-success">Активен</span>
                            @else
                                <span class="badge text-bg-secondary">Не активен</span>
                            @endif
                        </td>
                        <td>
                            @if ($user->is_admin)
                                <span class="badge text-bg-primary">Администратор</span>
                            @else
                                <span class="badge text-bg-light">Пользователь</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2 flex-wrap">
                                <form method="post" action="{{ route('admin.users.approval', $user) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="is_approved" value="{{ $user->is_approved ? 0 : 1 }}">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                                        {{ $user->is_approved ? 'Деактивировать' : 'Активировать' }}
                                    </button>
                                </form>

                                <form method="post" action="{{ route('admin.users.admin', $user) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="is_admin" value="{{ $user->is_admin ? 0 : 1 }}">
                                    <button class="btn btn-outline-primary btn-sm" type="submit">
                                        {{ $user->is_admin ? 'Снять админа' : 'Сделать админом' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Пользователи не найдены</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
@endsection
