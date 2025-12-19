<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\KomposisiBahanBaku;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $penjualanPerBulan = $this->getPenjualanData();
            $penggunaanBahanBaku = $this->getPenggunaanBahanBakuData();
            $bahanBakuPerluBeli = BahanBaku::perluPembelian()->get();
            $statistik = $this->getRingkasanStatistik();

            return view('owner.dashboard', compact(
                'penjualanPerBulan',
                'penggunaanBahanBaku',
                'bahanBakuPerluBeli',
                'statistik'
            ));
        } catch (\Exception $e) {
            Log::error('Owner Dashboard error: ' . $e->getMessage());
            return view('owner.dashboard', [
                'penjualanPerBulan' => [],
                'penggunaanBahanBaku' => [
                    'bulan_labels' => [],
                    'series_data' => [],
                    'total_bahan_baku' => 0
                ],
                'bahanBakuPerluBeli' => collect(),
                'statistik' => $this->getDefaultStatistik(),
                'error' => 'Gagal memuat data dashboard: ' . $e->getMessage()
            ]);
        }
    }

    private function getPenjualanData()
    {
        try {
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();

            $results = Penjualan::select(
                DB::raw('YEAR(tanggal) as tahun'),
                DB::raw('MONTH(tanggal) as bulan'),
                DB::raw('SUM(total) as total_penjualan'),
                DB::raw('COUNT(*) as jumlah_transaksi')
            )
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->groupBy(DB::raw('YEAR(tanggal)'), DB::raw('MONTH(tanggal)'))
                ->orderBy('tahun')
                ->orderBy('bulan')
                ->get()
                ->keyBy(function ($item) {
                    return $item->tahun . '-' . str_pad($item->bulan, 2, '0', STR_PAD_LEFT);
                });

            $data = [];
            $monthNames = [
                1 => 'Jan',
                2 => 'Feb',
                3 => 'Mar',
                4 => 'Apr',
                5 => 'Mei',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Agu',
                9 => 'Sep',
                10 => 'Okt',
                11 => 'Nov',
                12 => 'Des'
            ];

            for ($i = 0; $i < 12; $i++) {
                $date = Carbon::now()->subMonths(11 - $i);
                $year = $date->year;
                $month = $date->month;
                $key = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                $result = $results[$key] ?? null;

                $data[] = [
                    'bulan' => $monthNames[$month] . ' ' . $date->format('y'),
                    'tahun' => $year,
                    'bulan_angka' => $month,
                    'total_penjualan' => $result ? (float) $result->total_penjualan : 0,
                    'jumlah_transaksi' => $result ? (int) $result->jumlah_transaksi : 0,
                ];
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Error getPenjualanData: ' . $e->getMessage());
            return [];
        }
    }

    private function getPenggunaanBahanBakuData()
    {
        try {
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();

            $topBahanBaku = $this->getTopBahanBakuByUsage($startDate, $endDate);

            if (empty($topBahanBaku)) {
                return [
                    'bulan_labels' => [],
                    'series_data' => [],
                    'total_bahan_baku' => 0
                ];
            }

            $monthNames = [
                1 => 'Jan',
                2 => 'Feb',
                3 => 'Mar',
                4 => 'Apr',
                5 => 'Mei',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Agu',
                9 => 'Sep',
                10 => 'Okt',
                11 => 'Nov',
                12 => 'Des'
            ];

            $bulanLabels = [];
            for ($i = 0; $i < 12; $i++) {
                $date = Carbon::now()->subMonths(11 - $i);
                $bulanLabels[] = $monthNames[$date->month] . ' ' . $date->format('y');
            }

            $seriesData = [];
            $colors = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0'];

            foreach ($topBahanBaku as $index => $bahanBakuId) {
                $bahanBaku = BahanBaku::find($bahanBakuId);
                if (!$bahanBaku) continue;

                $dataPerBulan = [];
                for ($i = 0; $i < 12; $i++) {
                    $date = Carbon::now()->subMonths(11 - $i);
                    $year = $date->year;
                    $month = $date->month;

                    $total = $this->calculateMonthlyUsage($bahanBakuId, $year, $month);

                    $dataPerBulan[] = (float)($total ?: 0);
                }

                $seriesData[] = [
                    'name' => $bahanBaku->nama ?? 'Bahan Baku #' . $bahanBakuId,
                    'data' => $dataPerBulan
                ];
            }

            return [
                'bulan_labels' => $bulanLabels,
                'series_data' => $seriesData,
                'total_bahan_baku' => count($topBahanBaku)
            ];
        } catch (\Exception $e) {
            Log::error('Error getPenggunaanBahanBakuData: ' . $e->getMessage());
            return [
                'bulan_labels' => [],
                'series_data' => [],
                'total_bahan_baku' => 0
            ];
        }
    }

    private function getTopBahanBakuByUsage($startDate, $endDate)
    {
        $usageData = [];

        $penjualanLangsung = DetailPenjualan::where('jenis_item', 'bahan_baku')
            ->whereHas('penjualan', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->select('bahan_baku_id', DB::raw('SUM(jumlah) as total_penggunaan'))
            ->groupBy('bahan_baku_id')
            ->get();

        foreach ($penjualanLangsung as $item) {
            $usageData[$item->bahan_baku_id] = ($usageData[$item->bahan_baku_id] ?? 0) + $item->total_penggunaan;
        }

        $penjualanProduk = DetailPenjualan::where('jenis_item', 'produk')
            ->whereHas('penjualan', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->with(['produk' => function ($query) {
                $query->with('komposisi.bahanBaku');
            }])
            ->get();

        foreach ($penjualanProduk as $detail) {
            if ($detail->produk && $detail->produk->komposisi) {
                foreach ($detail->produk->komposisi as $komposisi) {
                    if ($komposisi->bahanBaku) {
                        $usage = $komposisi->jumlah * $detail->jumlah;
                        $usageData[$komposisi->bahan_baku_id] =
                            ($usageData[$komposisi->bahan_baku_id] ?? 0) + $usage;
                    }
                }
            }
        }

        arsort($usageData);

        return array_slice(array_keys($usageData), 0, 5);
    }

    private function calculateMonthlyUsage($bahanBakuId, $year, $month)
    {
        $total = 0;

        $penjualanLangsung = DetailPenjualan::where('jenis_item', 'bahan_baku')
            ->where('bahan_baku_id', $bahanBakuId)
            ->whereHas('penjualan', function ($query) use ($year, $month) {
                $query->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month);
            })
            ->sum('jumlah');

        $total += $penjualanLangsung;

        $penjualanProduk = DetailPenjualan::where('jenis_item', 'produk')
            ->whereHas('penjualan', function ($query) use ($year, $month) {
                $query->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month);
            })
            ->with(['produk' => function ($query) use ($bahanBakuId) {
                $query->with(['komposisi' => function ($q) use ($bahanBakuId) {
                    $q->where('bahan_baku_id', $bahanBakuId);
                }]);
            }])
            ->get();

        foreach ($penjualanProduk as $detail) {
            if ($detail->produk && $detail->produk->komposisi) {
                foreach ($detail->produk->komposisi as $komposisi) {
                    if ($komposisi->bahan_baku_id == $bahanBakuId) {
                        $total += $komposisi->jumlah * $detail->jumlah;
                    }
                }
            }
        }

        return $total;
    }

    private function getRingkasanStatistik()
    {
        try {
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
            $startOfMonth = Carbon::now()->startOfMonth();
            $startOfYear = Carbon::now()->startOfYear();

            $penjualanHariIni = Penjualan::whereDate('tanggal', $today)->sum('total');
            $penjualanKemarin = Penjualan::whereDate('tanggal', $yesterday)->sum('total');
            $perubahan = $penjualanKemarin > 0 ?
                (($penjualanHariIni - $penjualanKemarin) / $penjualanKemarin * 100) : 0;

            $pembelianBulanIni = Pembelian::where('status', 'completed')
                ->where('tanggal', '>=', $startOfMonth)
                ->sum('total');

            return [
                'penjualan_hari_ini' => (float) $penjualanHariIni,
                'perubahan_penjualan' => round($perubahan, 2),
                'penjualan_bulan_ini' => (float) Penjualan::where('tanggal', '>=', $startOfMonth)->sum('total'),
                'penjualan_tahun_ini' => (float) Penjualan::where('tanggal', '>=', $startOfYear)->sum('total'),
                'pembelian_bulan_ini' => (float) $pembelianBulanIni,
                'total_transaksi_hari_ini' => (int) Penjualan::whereDate('tanggal', $today)->count(),
                'total_bahan_baku_perlu_beli' => (int) BahanBaku::perluPembelian()->count(),
                'total_bahan_baku' => (int) BahanBaku::count(),
                'total_produk' => (int) Produk::count(),
                'total_supplier' => (int) Supplier::count(),
                'total_nilai_perlu_beli' => BahanBaku::perluPembelian()
                    ->get()
                    ->sum(function ($bahan) {
                        try {
                            $rekomendasi = $bahan->getRekomendasiPembelianRopAttribute();
                            return $rekomendasi ? $rekomendasi['total_nilai'] : 0;
                        } catch (\Exception $e) {
                            return 0;
                        }
                    })
            ];
        } catch (\Exception $e) {
            Log::error('Error getRingkasanStatistik: ' . $e->getMessage());
            return $this->getDefaultStatistik();
        }
    }

    private function getDefaultStatistik()
    {
        return [
            'penjualan_hari_ini' => 0,
            'perubahan_penjualan' => 0,
            'penjualan_bulan_ini' => 0,
            'penjualan_tahun_ini' => 0,
            'pembelian_bulan_ini' => 0,
            'total_transaksi_hari_ini' => 0,
            'total_bahan_baku_perlu_beli' => 0,
            'total_bahan_baku' => 0,
            'total_produk' => 0,
            'total_supplier' => 0,
            'total_nilai_perlu_beli' => 0
        ];
    }
}
