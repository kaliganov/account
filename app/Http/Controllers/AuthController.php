<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Неверный логин или пароль.']);
        }

        $user = $request->user();
        if (! $user?->is_approved) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Аккаунт ожидает подтверждения администратором.']);
        }

        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function createRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $isFirst = User::query()->doesntExist();

        $user = User::query()->create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password' => Hash::make($attributes['password']),
        ]);
        $user->is_admin = $isFirst;
        $user->is_approved = $isFirst;
        $user->save();

        $message = $isFirst
            ? 'Регистрация завершена. Вы первый пользователь — аккаунт уже активен, можно войти.'
            : 'Регистрация завершена. Аккаунт ожидает подтверждения администратором.';

        return redirect()->route('login')->with('status', $message);
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

