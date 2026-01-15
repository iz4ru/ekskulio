<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

// Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'index'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
// });

Route::middleware(['auth', 'check.role:kesiswaan'])->group(function () {
    Route::get('kesiswaan/dashboard', [DashboardController::class, 'index'])->name('kesiswaan.dashboard');
});

Route::middleware(['auth', 'check.role:pembina'])->group(function () {
    Route::get('pembina/dashboard', [DashboardController::class, 'index'])->name('pembina.dashboard');
});

Route::middleware(['auth', 'check.role:admin'])->group(function () {
    Route::get('admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
});
