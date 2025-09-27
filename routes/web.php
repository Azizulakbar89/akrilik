<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboard;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'owner' => redirect()->route('owner.dashboard'),
        default => redirect('/'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('admin.dashboard');

    // CRUD Routes untuk Admin
    Route::get('/bahan-baku', function () {
        return view('admin.bahan-baku.index');
    })->name('admin.bahan-baku.index');
    Route::get('/produk', function () {
        return view('admin.produk.index');
    })->name('admin.produk.index');
    Route::get('/supplier', function () {
        return view('admin.supplier.index');
    })->name('admin.supplier.index');
    Route::get('/komposisi-produk', function () {
        return view('admin.komposisi-produk.index');
    })->name('admin.komposisi-produk.index');
    Route::get('/pembelian', function () {
        return view('admin.pembelian.index');
    })->name('admin.pembelian.index');
    Route::get('/penjualan', function () {
        return view('admin.penjualan.index');
    })->name('admin.penjualan.index');
});

Route::middleware(['auth', 'role:owner'])->prefix('owner')->group(function () {
    Route::get('/dashboard', [OwnerDashboard::class, 'index'])->name('owner.dashboard');

    // CRUD Routes untuk Owner
    Route::get('/bahan-baku', function () {
        return view('owner.bahan-baku.index');
    })->name('owner.bahan-baku.index');
    Route::get('/produk', function () {
        return view('owner.produk.index');
    })->name('owner.produk.index');
    Route::get('/supplier', function () {
        return view('owner.supplier.index');
    })->name('owner.supplier.index');
    Route::get('/komposisi-produk', function () {
        return view('owner.komposisi-produk.index');
    })->name('owner.komposisi-produk.index');
    Route::get('/pembelian', function () {
        return view('owner.pembelian.index');
    })->name('owner.pembelian.index');
    Route::get('/penjualan', function () {
        return view('owner.penjualan.index');
    })->name('owner.penjualan.index');
});

require __DIR__ . '/auth.php';
