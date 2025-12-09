<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Customer authentication routes (using customer guard)
Route::middleware('guest:customer')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');

    // Google OAuth Routes
    Route::get('/auth/google', [GoogleController::class, 'redirect'])
        ->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])
        ->name('auth.google.callback');
});

Route::middleware('auth:customer')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
