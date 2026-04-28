<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CounterpartyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoicePdfController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1')->name('register.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('home')->with('status', 'Email успешно подтвержден.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Письмо для подтверждения отправлено повторно.');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/generate-invoices', [HomeController::class, 'generate'])->name('home.generate');
    Route::get('/download-invoices-archive', [HomeController::class, 'downloadArchive'])->name('home.archive.download');

    Route::get('/counterparties', [CounterpartyController::class, 'index'])->name('counterparties.index');
    Route::get('/counterparties/create', [CounterpartyController::class, 'create'])->name('counterparties.create');
    Route::post('/counterparties', [CounterpartyController::class, 'store'])->middleware('throttle:30,1')->name('counterparties.store');
    Route::get('/counterparties/{counterparty}/edit', [CounterpartyController::class, 'edit'])->name('counterparties.edit');
    Route::put('/counterparties/{counterparty}', [CounterpartyController::class, 'update'])->middleware('throttle:30,1')->name('counterparties.update');
    Route::delete('/counterparties/{counterparty}', [CounterpartyController::class, 'destroy'])->middleware('throttle:30,1')->name('counterparties.destroy');

    Route::get('/counterparties/{counterparty}/invoice.pdf', [InvoicePdfController::class, 'download'])
        ->name('counterparties.invoice_pdf');
});
