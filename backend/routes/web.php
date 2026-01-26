<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Auth;

Route::get('/login', function () {
    if (Auth::user()) {
        return redirect()->route("index");
    }
    return inertia('Auth/Login');
})->name('login');

Route::get('/register', function () {
    if (Auth::user()) {
        return redirect()->route('index');
    }
    return inertia('Auth/Register');
});

// Use this for your Inertia form submission
Route::post('/login-action', [AuthController::class, 'spaLogin']);
Route::post('/register-action', [AuthController::class, 'spaRegister']);

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return inertia('Home');
    })->name('index');
    Route::post('/logout', [AuthController::class, 'spaLogout'])->name('logout');
});
