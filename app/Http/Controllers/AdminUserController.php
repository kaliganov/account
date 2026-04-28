<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function updateApproval(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_approved' => ['required', 'boolean'],
        ]);

        $isApproved = (bool) $validated['is_approved'];

        if ($request->user()?->id === $user->id && ! $isApproved) {
            return back()->withErrors(['status' => 'Нельзя деактивировать собственный аккаунт.']);
        }

        $user->is_approved = $isApproved;
        $user->save();

        return back()->with('status', 'Статус активности пользователя обновлен.');
    }

    public function updateAdmin(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_admin' => ['required', 'boolean'],
        ]);

        $isAdmin = (bool) $validated['is_admin'];

        if ($request->user()?->id === $user->id && ! $isAdmin) {
            return back()->withErrors(['status' => 'Нельзя снять права администратора у себя.']);
        }

        $user->is_admin = $isAdmin;
        $user->save();

        return back()->with('status', 'Роль пользователя обновлена.');
    }
}
