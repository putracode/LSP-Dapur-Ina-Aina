<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Kasir\PosController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('kasir.pos');
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('kategori', KategoriController::class)->except(['show'])->parameters([
        'kategori' => 'kategori:id_kategori',
    ]);

    Route::resource('menu', MenuController::class)->except(['show'])->parameters([
        'menu' => 'menu:id_menu',
    ]);

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');

    Route::resource('user', UserController::class)->except(['show'])->parameters([
        'user' => 'user:id_user',
    ]);
});

Route::middleware(['auth', 'role:Kasir,Admin'])->prefix('kasir')->name('kasir.')->group(function (): void {
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
    Route::get('/receipt/{transaksi:id_transaksi}', [PosController::class, 'receipt'])->name('receipt');
});
