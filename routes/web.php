<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CounterpartyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoicePdfController;

Route::get('/', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::post('/generate-invoices', [HomeController::class, 'generate'])->name('home.generate');
    Route::get('/download-invoices-archive', [HomeController::class, 'downloadArchive'])->name('home.archive.download');

    Route::get('/counterparties', [CounterpartyController::class, 'index'])->name('counterparties.index');
    Route::get('/counterparties/create', [CounterpartyController::class, 'create'])->name('counterparties.create');
    Route::post('/counterparties', [CounterpartyController::class, 'store'])->name('counterparties.store');
    Route::get('/counterparties/{counterparty}/edit', [CounterpartyController::class, 'edit'])->name('counterparties.edit');
    Route::put('/counterparties/{counterparty}', [CounterpartyController::class, 'update'])->name('counterparties.update');
    Route::delete('/counterparties/{counterparty}', [CounterpartyController::class, 'destroy'])->name('counterparties.destroy');

    Route::get('/counterparties/{counterparty}/invoice.pdf', [InvoicePdfController::class, 'download'])
        ->name('counterparties.invoice_pdf');
});
