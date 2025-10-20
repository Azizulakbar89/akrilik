<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBahanBaku = BahanBaku::count();
        $totalProduk = Produk::count();
        $totalSupplier = Supplier::count();

        $totalPembelian = Pembelian::where('status', 'completed')
            ->whereMonth('tanggal', date('m'))
            ->whereYear('tanggal', date('Y'))
            ->sum('total') ?? 0;

        $totalPenjualan = Penjualan::whereMonth('tanggal', date('m'))
            ->whereYear('tanggal', date('Y'))
            ->sum('total') ?? 0;

        $grafikPembelian = $this->getGrafikPembelian();

        $grafikPenjualanBahanBaku = $this->getGrafikPenjualanBahanBaku();

        $grafikPenjualanProduk = $this->getGrafikPenjualanProduk();

        return view('owner.dashboard', compact(
            'totalBahanBaku',
            'totalProduk',
            'totalSupplier',
            'totalPembelian',
            'totalPenjualan',
            'grafikPembelian',
            'grafikPenjualanBahanBaku',
            'grafikPenjualanProduk'
        ));
    }

    private function getGrafikPembelian()
    {
        $data = [];
        $bulan = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $bulan[] = $date->translatedFormat('M Y');

            $total = Pembelian::where('status', 'completed')
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->sum('total');

            $data[] = $total ? (float)$total : 0.0;
        }

        if (array_sum($data) === 0.0) {
            $data = [1000000, 1500000, 1200000, 1800000, 2000000, 1700000];
        }

        return [
            'bulan' => $bulan,
            'data' => $data
        ];
    }

    private function getGrafikPenjualanBahanBaku()
    {
        $data = [];
        $bulan = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $bulan[] = $date->translatedFormat('M Y');

            $total = Penjualan::whereHas('detailPenjualan', function ($query) {
                $query->whereNotNull('bahan_baku_id');
            })
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->sum('total');

            $data[] = $total ? (float)$total : 0.0;
        }

        if (array_sum($data) === 0.0) {
            $data = [500000, 800000, 600000, 900000, 1000000, 700000];
        }

        return [
            'bulan' => $bulan,
            'data' => $data
        ];
    }

    private function getGrafikPenjualanProduk()
    {
        $data = [];
        $bulan = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $bulan[] = $date->translatedFormat('M Y');

            $total = Penjualan::whereHas('detailPenjualan', function ($query) {
                $query->whereNotNull('produk_id');
            })
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->sum('total');

            $data[] = $total ? (float)$total : 0.0;
        }

        if (array_sum($data) === 0.0) {
            $data = [1200000, 1400000, 1100000, 1600000, 1800000, 1500000];
        }

        return [
            'bulan' => $bulan,
            'data' => $data
        ];
    }
}
