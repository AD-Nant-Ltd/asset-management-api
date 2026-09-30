<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');

    Route::resource('staff', StaffController::class)
        ->only([
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
        ]);

    Route::post('/logout', [LoginController::class, 'destroy'])
        ->name('logout');
});