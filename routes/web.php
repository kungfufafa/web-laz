<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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
