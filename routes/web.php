<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\BahanBakuController;
use App\Http\Controllers\Admin\PembelianController;
use App\Http\Controllers\Admin\PenjualanController;
use App\Http\Controllers\Owner\UserOwnerController;
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

    Route::get('/penjualan/get-item-info', [PenjualanController::class, 'getItemInfo'])->name('penjualan.getItemInfo');

    Route::resource('supplier', SupplierController::class);

    Route::prefix('pembelian')->name('pembelian.')->group(function () {
        Route::get('/', [PembelianController::class, 'index'])->name('index');
        Route::get('/create', [PembelianController::class, 'create'])->name('create');
        Route::post('/', [PembelianController::class, 'store'])->name('store');
        Route::get('/{pembelian}', [PembelianController::class, 'show'])->name('show');
        Route::get('/{pembelian}/edit', [PembelianController::class, 'edit'])->name('edit');
        Route::put('/{pembelian}', [PembelianController::class, 'update'])->name('update');
        Route::delete('/{pembelian}', [PembelianController::class, 'destroy'])->name('destroy');
        Route::post('/{pembelian}/approve', [PembelianController::class, 'approve'])->name('approve');
        Route::post('/{pembelian}/reject', [PembelianController::class, 'reject'])->name('reject');

        Route::post('/store-from-rekomendasi', [PembelianController::class, 'storeFromRekomendasi'])->name('store.from.rekomendasi');

        Route::get('/api/stok-tidak-aman', [PembelianController::class, 'getStokTidakAman'])->name('api.stok-tidak-aman');
        Route::get('/api/rekomendasi', [PembelianController::class, 'getRekomendasiPembelian'])->name('api.rekomendasi');
        Route::get('/api/rekomendasi-for-form', [PembelianController::class, 'getRekomendasiForForm'])->name('api.rekomendasi.form');
        Route::get('/api/perhitungan/{id}', [PembelianController::class, 'getDetailPerhitungan'])->name('api.perhitungan');
        Route::post('/reset-modal', [PembelianController::class, 'resetModalSession'])->name('reset-modal');
        Route::get('/rekomendasi/form', [PembelianController::class, 'getRekomendasiForForm'])->name('rekomendasi.form');
    });

    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/', [PenjualanController::class, 'index'])->name('index');
        Route::post('/', [PenjualanController::class, 'store'])->name('store');
        Route::get('/{id}', [PenjualanController::class, 'show'])->name('show');
        Route::delete('/{id}', [PenjualanController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/print', [PenjualanController::class, 'printNota'])->name('print-nota');
        Route::get('/get-item-info', [PenjualanController::class, 'getItemInfo'])->name('get-item-info');
    });
});

Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboard::class, 'index'])->name('dashboard');

    Route::resource('bahan-baku', BahanBakuOwnerController::class);
    Route::get('/bahan-baku/{id}/calculation-detail', [BahanBakuOwnerController::class, 'getCalculationDetail'])->name('bahan-baku.calculation-detail');
    Route::resource('produk', ProdukOwnerController::class);
    Route::resource('supplier', SupplierOwnerController::class);

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserOwnerController::class, 'index'])->name('index');
        Route::post('/', [UserOwnerController::class, 'store'])->name('store');
        Route::get('/{user}', [UserOwnerController::class, 'show'])->name('show');
        Route::put('/{user}', [UserOwnerController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserOwnerController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/', [PenjualanOwnerController::class, 'index'])->name('index');
        Route::get('/laporan', [PenjualanOwnerController::class, 'laporan'])->name('laporan');
        Route::get('/generate-pdf', [PenjualanOwnerController::class, 'generatePDF'])->name('generate-pdf');
        Route::get('/{penjualan}', [PenjualanOwnerController::class, 'show'])->name('show');
        Route::get('/{penjualan}/print', [PenjualanOwnerController::class, 'printNota'])->name('print-nota');
    });

    Route::prefix('pembelian')->name('pembelian.')->group(function () {
        // CRUD Routes
        Route::get('/', [PembelianOwnerController::class, 'index'])->name('index');
        Route::get('/create', [PembelianOwnerController::class, 'create'])->name('create');
        Route::post('/', [PembelianOwnerController::class, 'store'])->name('store');
        Route::get('/{id}', [PembelianOwnerController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PembelianOwnerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PembelianOwnerController::class, 'update'])->name('update');
        Route::delete('/{id}', [PembelianOwnerController::class, 'destroy'])->name('destroy');

        // Aksi khusus
        Route::post('/{id}/approve', [PembelianOwnerController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [PembelianOwnerController::class, 'reject'])->name('reject');
        Route::post('/{id}/receive', [PembelianOwnerController::class, 'receive'])->name('receive');

        Route::get('/data/rekomendasi', [PembelianOwnerController::class, 'getRekomendasiData'])->name('rekomendasi.data');
        Route::get('/data/pembelian-cepat', [PembelianOwnerController::class, 'getPembelianCepatData'])->name('pembelian-cepat.data');
        Route::post('/store/pembelian-cepat', [PembelianOwnerController::class, 'storePembelianCepat'])->name('pembelian-cepat.store');
        Route::post('/store/rekomendasi', [PembelianOwnerController::class, 'storeRekomendasi'])->name('rekomendasi.store');

        Route::get('/pembelian-cepat/form', [PembelianOwnerController::class, 'showPembelianCepat'])->name('pembelian-cepat.form');

        Route::get('/laporan', [PembelianOwnerController::class, 'laporan'])->name('laporan');
        Route::post('/print-laporan', [PembelianOwnerController::class, 'printLaporan'])->name('print-laporan');
        Route::get('/export-pdf', [PembelianOwnerController::class, 'exportPDF'])->name('export-pdf');
    });
});

require __DIR__ . '/auth.php';
