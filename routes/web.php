<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\BahanBakuController;
use App\Http\Controllers\Admin\PembelianController;
use App\Http\Controllers\Admin\PenjualanController;
use App\Http\Controllers\Owner\ProdukOwnerController;
use App\Http\Controllers\Owner\SupplierOwnerController;
use App\Http\Controllers\Owner\BahanBakuOwnerController;
use App\Http\Controllers\Owner\PembelianOwnerController;
use App\Http\Controllers\Owner\PenjualanOwnerController;
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

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    Route::prefix('bahan-baku')->name('bahan-baku.')->group(function () {
        Route::get('/', [BahanBakuController::class, 'index'])->name('index');
        Route::post('/', [BahanBakuController::class, 'store'])->name('store');
        Route::get('/{id}', [BahanBakuController::class, 'show'])->name('show');
        Route::put('/{id}', [BahanBakuController::class, 'update'])->name('update');
        Route::delete('/{id}', [BahanBakuController::class, 'destroy'])->name('destroy');

        Route::post('/recalculate-all', [BahanBakuController::class, 'recalculateAll'])->name('recalculate-all');
        Route::get('/{id}/calculation-detail', [BahanBakuController::class, 'getCalculationDetail'])->name('calculation-detail');
    });

    Route::prefix('produk')->name('produk.')->group(function () {
        Route::get('/', [ProdukController::class, 'index'])->name('index');
        Route::get('/create', [ProdukController::class, 'create'])->name('create');
        Route::post('/', [ProdukController::class, 'store'])->name('store');
        Route::get('/{id}', [ProdukController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ProdukController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProdukController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProdukController::class, 'destroy'])->name('destroy');
        Route::get('/komposisi', [ProdukController::class, 'komposisi'])->name('komposisi');
    });

    Route::resource('supplier', SupplierController::class);

    Route::resource('pembelian', PembelianController::class);

    Route::post('/pembelian/{id}/approve', [PembelianController::class, 'approve'])->name('pembelian.approve');
    Route::post('/pembelian/{id}/reject', [PembelianController::class, 'reject'])->name('pembelian.reject');
    Route::post('/pembelian-rekomendasi/create', [PembelianController::class, 'createFromRekomendasi'])->name('pembelian.rekomendasi.create');
    Route::get('/api/stok-tidak-aman', [PembelianController::class, 'getStokTidakAman'])->name('pembelian.api.stok-tidak-aman');
    Route::get('/api/rekomendasi', [PembelianController::class, 'getRekomendasiPembelian'])->name('pembelian.api.rekomendasi');
    Route::get('/api/perhitungan/{id}', [PembelianController::class, 'getDetailPerhitungan'])->name('pembelian.api.perhitungan');

    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/', [PenjualanController::class, 'index'])->name('index');
        Route::post('/', [PenjualanController::class, 'store'])->name('store');
        Route::get('/{id}', [PenjualanController::class, 'show'])->name('show');
        Route::delete('/{id}', [PenjualanController::class, 'destroy'])->name('destroy');
        Route::get('/get-item-info', [PenjualanController::class, 'getItemInfo'])->name('get-item-info');
    });
});

Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboard::class, 'index'])->name('dashboard');

    Route::resource('bahan-baku', BahanBakuOwnerController::class);
    Route::get('/bahan-baku/{id}/calculation-detail', [BahanBakuOwnerController::class, 'getCalculationDetail'])->name('bahan-baku.calculation-detail');
    Route::resource('produk', ProdukOwnerController::class);
    Route::resource('supplier', SupplierOwnerController::class);

    Route::prefix('pembelian')->name('pembelian.')->group(function () {
        Route::get('/', [PembelianOwnerController::class, 'index'])->name('index');
        Route::post('/', [PembelianOwnerController::class, 'store'])->name('store');
        Route::get('/create', [PembelianOwnerController::class, 'create'])->name('create');
        Route::get('/{pembelian}', [PembelianOwnerController::class, 'show'])->name('show');
        Route::get('/{pembelian}/edit', [PembelianOwnerController::class, 'edit'])->name('edit');
        Route::put('/{pembelian}', [PembelianOwnerController::class, 'update'])->name('update');
        Route::delete('/{pembelian}', [PembelianOwnerController::class, 'destroy'])->name('destroy');
        Route::post('/{pembelian}/approve', [PembelianOwnerController::class, 'approve'])->name('approve');
        Route::post('/{pembelian}/reject', [PembelianOwnerController::class, 'reject'])->name('reject');
        Route::post('/rekomendasi/create', [PembelianOwnerController::class, 'createFromRekomendasi'])->name('rekomendasi.create');

        Route::get('/laporan/print', [PembelianOwnerController::class, 'laporan'])->name('laporan');
    });

    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/', [PenjualanOwnerController::class, 'index'])->name('index');
        Route::get('/laporan', [PenjualanOwnerController::class, 'laporan'])->name('laporan');
        Route::get('/generate-pdf', [PenjualanOwnerController::class, 'generatePDF'])->name('generate-pdf');
        Route::get('/{penjualan}', [PenjualanOwnerController::class, 'show'])->name('show');
    });
});

require __DIR__ . '/auth.php';
