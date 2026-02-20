<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\MemberPrayerController;
use Illuminate\Support\Facades\Route;

// API prefix is configured in bootstrap/app.php (apiPrefix: 'api')

// Auth endpoints with rate limiting (5 requests per minute)
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/account-deletion', [AuthController::class, 'deleteAccount']);
});
Route::get('/media/{path}', [MediaController::class, 'show'])->where('path', '.*');

Route::get('/payment-methods', [DonationController::class, 'paymentMethods']);
Route::get('/donation-config', [DonationController::class, 'donationConfig']);
Route::post('/zakat/calculate', [DonationController::class, 'calculateZakat']);
Route::post('/donations', [DonationController::class, 'store']);
Route::get('/donations/history', [DonationController::class, 'history']);

Route::get('/articles', [ContentController::class, 'articles']);
Route::get('/articles/{article:slug}', [ContentController::class, 'article']);
Route::get('/videos', [ContentController::class, 'videos']);
Route::get('/prayers', [MemberPrayerController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/prayers', [MemberPrayerController::class, 'store']);
    Route::post('/prayers/{prayer}/amen', [MemberPrayerController::class, 'amen']);
});
