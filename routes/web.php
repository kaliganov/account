<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CounterpartyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceHistoryController;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\ProfileController;

Route::get('/', [CounterpartyController::class, 'index'])
    ->middleware(['auth', 'approved'])
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
Route::get('/logout', fn () => redirect()->route('home'));

Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:5,1')->name('profile.password');

    Route::post('/generate-invoices', [HomeController::class, 'generate'])->middleware('throttle:10,1')->name('home.generate');
    Route::get('/generate-invoices', fn () => redirect()->route('home'));
    Route::match(['get', 'post'], '/download-invoices-archive', [HomeController::class, 'downloadArchive'])->name('home.archive.download');

    Route::get('/invoices', [InvoiceHistoryController::class, 'index'])->name('invoices.index');

    Route::get('/counterparties/create', [CounterpartyController::class, 'create'])->name('counterparties.create');
    Route::post('/counterparties', [CounterpartyController::class, 'store'])->middleware('throttle:30,1')->name('counterparties.store');
    Route::get('/counterparties/{counterparty}/edit', [CounterpartyController::class, 'edit'])->name('counterparties.edit');
    Route::put('/counterparties/{counterparty}', [CounterpartyController::class, 'update'])->middleware('throttle:30,1')->name('counterparties.update');
    Route::delete('/counterparties/{counterparty}', [CounterpartyController::class, 'destroy'])->middleware('throttle:30,1')->name('counterparties.destroy');

    Route::get('/counterparties/{counterparty}/invoice.pdf', [InvoicePdfController::class, 'download'])
        ->name('counterparties.invoice_pdf');
});

Route::middleware(['auth', 'approved', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/approval', [AdminUserController::class, 'updateApproval'])->name('users.approval');
    Route::put('/users/{user}/admin', [AdminUserController::class, 'updateAdmin'])->name('users.admin');
});
