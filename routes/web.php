<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\PpobController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PpobController::class, 'index'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'createRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});

Route::get('/ppob', function () {
    return redirect()->route('home');
})->name('ppob.index');
Route::get('/ppob/services/{serviceType}/{journey}', [PpobController::class, 'catalog'])->name('ppob.catalog');
Route::post('/ppob/services/{serviceType}/{journey}/inquiries', [PpobController::class, 'catalogInquiry'])->name('ppob.catalog.inquiries.store');
Route::post('/ppob/services/{serviceType}/{journey}/transactions', [PpobController::class, 'catalogStore'])->name('ppob.catalog.transactions.store');
Route::get('/ppob/transactions/{ppobTransaction}', [PpobController::class, 'show'])->name('ppob.transactions.show');
Route::post('/ppob/transactions/{ppobTransaction}/refresh', [PpobController::class, 'refresh'])->name('ppob.transactions.refresh');

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return view('terms-of-service');
})->name('terms-of-service');

Route::get('/support', function () {
    return view('support');
})->name('support');

Route::get('/account-deletion', function () {
    return view('account-deletion');
})->name('account-deletion');
