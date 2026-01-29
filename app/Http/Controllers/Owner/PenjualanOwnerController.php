<?php

namespace App\Http\Controllers\Owner;

use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\KomposisiBahanBaku;
use App\Models\PenggunaanBahanBaku;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class PenjualanOwnerController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? date('Y-m-01');
        $tanggalAkhir = $request->tanggal_akhir ?? date('Y-m-d');
        $searchBahanBaku = $request->search_bahan_baku ?? null;

        $penjualanQuery = Penjualan::with(['detailPenjualan', 'admin'])
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

        if ($searchBahanBaku) {
            $penjualanQuery->whereHas('detailPenjualan', function ($query) use ($searchBahanBaku) {
                $query->where(function ($subQuery) use ($searchBahanBaku) {
                    $subQuery->whereHas('produk.komposisi.bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    })->orWhereHas('bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    });
                });
            });
        }

        $penjualan = $penjualanQuery->orderBy('created_at', 'desc')->get();

        $penjualan->each(function ($item) {
            $item->total_formatted = 'Rp ' . number_format($item->total, 0, ',', '.');
            $item->bayar_formatted = 'Rp ' . number_format($item->bayar, 0, ',', '.');
            $item->kembalian_formatted = 'Rp ' . number_format($item->kembalian, 0, ',', '.');

            // Hitung total bahan baku untuk setiap penjualan
            $item->bahan_baku_digunakan = $this->getBahanBakuUntukPenjualan($item);
        });

        // Get bahan baku list for dropdown
        $bahanBakuList = BahanBaku::orderBy('nama')->get();

        // Hitung total bahan baku keluar per periode
        $totalBahanBakuKeluar = $this->getTotalBahanBakuKeluar($tanggalAwal, $tanggalAkhir, $searchBahanBaku);

        // Hitung total pendapatan dan margin keuntungan
        $totalPendapatan = $penjualan->sum('total');
        $totalPendapatanFormatted = 'Rp ' . number_format($totalPendapatan, 0, ',', '.');

        // Hitung total biaya bahan baku
        $totalBiayaBahanBaku = $totalBahanBakuKeluar->sum('total_harga_beli');

        // Hitung laba kotor dan margin keuntungan
        $labaKotor = $totalPendapatan - $totalBiayaBahanBaku;
        $marginKeuntungan = $totalBiayaBahanBaku > 0 ? (($labaKotor / $totalBiayaBahanBaku) * 100) : 0;
        $marginKeuntunganFormatted = number_format($marginKeuntungan, 2, ',', '.') . '%';

        // Get produk terlaris dengan margin keuntungan
        $produkTerlaris = DetailPenjualan::select(
            'produk_id',
            DB::raw('SUM(jumlah) as total_terjual'),
            DB::raw('SUM(sub_total) as total_pendapatan')
        )
            ->whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir, $searchBahanBaku) {
                $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
                if ($searchBahanBaku) {
                    $query->whereHas('detailPenjualan.produk.komposisi.bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    });
                }
            })
            ->where('jenis_item', 'produk')
            ->groupBy('produk_id')
            ->orderBy('total_terjual', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $produk = Produk::with('komposisi.bahanBaku')->find($item->produk_id);

                // Hitung margin keuntungan untuk setiap produk
                $biayaProduksi = 0;
                $marginProduk = 0;

                if ($produk) {
                    foreach ($produk->komposisi as $komposisi) {
                        $biayaProduksi += ($komposisi->bahanBaku->harga_beli ?? 0) * $komposisi->jumlah * $item->total_terjual;
                    }

                    if ($biayaProduksi > 0) {
                        $labaProduk = $item->total_pendapatan - $biayaProduksi;
                        $marginProduk = ($labaProduk / $biayaProduksi) * 100;
                    }
                }

                return [
                    'id' => $item->produk_id,
                    'nama' => $produk ? $produk->nama : 'Produk Tidak Ditemukan',
                    'total_terjual' => $item->total_terjual,
                    'total_pendapatan' => $item->total_pendapatan,
                    'satuan' => $produk ? $produk->satuan : '-',
                    'total_pendapatan_formatted' => 'Rp ' . number_format($item->total_pendapatan, 0, ',', '.'),
                    'biaya_produksi' => $biayaProduksi,
                    'biaya_produksi_formatted' => 'Rp ' . number_format($biayaProduksi, 0, ',', '.'),
                    'laba_produk' => $item->total_pendapatan - $biayaProduksi,
                    'laba_produk_formatted' => 'Rp ' . number_format($item->total_pendapatan - $biayaProduksi, 0, ',', '.'),
                    'margin_keuntungan' => $marginProduk,
                    'margin_keuntungan_formatted' => number_format($marginProduk, 2, ',', '.') . '%'
                ];
            });

        // Get bahan baku terlaris dengan harga jual untuk perhitungan margin
        $bahanBakuTerlaris = $this->getBahanBakuTerlaris($tanggalAwal, $tanggalAkhir, $searchBahanBaku);

        return view('owner.penjualan.index', compact(
            'penjualan',
            'produkTerlaris',
            'bahanBakuTerlaris',
            'bahanBakuList',
            'tanggalAwal',
            'tanggalAkhir',
            'searchBahanBaku',
            'totalBahanBakuKeluar',
            'totalPendapatan',
            'totalPendapatanFormatted',
            'totalBiayaBahanBaku',
            'labaKotor',
            'marginKeuntunganFormatted'
        ));
    }

    private function getTotalBahanBakuKeluar($tanggalAwal, $tanggalAkhir, $searchBahanBaku = null)
    {
        // Data dari penjualan produk (bahan baku yang digunakan dalam produk)
        $produkTerjual = DetailPenjualan::select(
            'produk_id',
            DB::raw('SUM(jumlah) as total_produk_terjual'),
            DB::raw('SUM(sub_total) as total_pendapatan_produk')
        )
            ->whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir, $searchBahanBaku) {
                $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
                if ($searchBahanBaku) {
                    $query->whereHas('detailPenjualan.produk.komposisi.bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    });
                }
            })
            ->where('jenis_item', 'produk')
            ->groupBy('produk_id')
            ->get();

        $bahanBakuUsage = [];

        // Hitung penggunaan bahan baku dari produk yang terjual
        foreach ($produkTerjual as $produk) {
            $komposisi = KomposisiBahanBaku::where('produk_id', $produk->produk_id)
                ->with('bahanBaku')
                ->when($searchBahanBaku, function ($query) use ($searchBahanBaku) {
                    return $query->whereHas('bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    });
                })
                ->get();

            foreach ($komposisi as $item) {
                if ($item->bahanBaku) {
                    $bahanBakuId = $item->bahan_baku_id;
                    $jumlahPenggunaan = $item->jumlah * $produk->total_produk_terjual;

                    if (!isset($bahanBakuUsage[$bahanBakuId])) {
                        $bahanBakuUsage[$bahanBakuId] = [
                            'bahan_baku' => $item->bahanBaku,
                            'total_penggunaan' => 0,
                            'total_harga_beli' => 0,
                            'total_harga_jual' => 0,
                            'total_pendapatan' => 0
                        ];
                    }

                    $bahanBakuUsage[$bahanBakuId]['total_penggunaan'] += $jumlahPenggunaan;
                    $bahanBakuUsage[$bahanBakuId]['total_harga_beli'] += ($item->bahanBaku->harga_beli ?? 0) * $jumlahPenggunaan;
                    $bahanBakuUsage[$bahanBakuId]['total_harga_jual'] += ($item->bahanBaku->harga_jual ?? 0) * $jumlahPenggunaan;

                    // Hitung pendapatan dari bahan baku ini
                    $hargaJualProduk = $produk->total_pendapatan_produk / max($produk->total_produk_terjual, 1);
                    $proporsiBahan = ($item->jumlah * $produk->total_produk_terjual) / max(array_sum(array_column($komposisi->toArray(), 'jumlah')) * $produk->total_produk_terjual, 1);
                    $bahanBakuUsage[$bahanBakuId]['total_pendapatan'] += $hargaJualProduk * $proporsiBahan * $produk->total_produk_terjual;
                }
            }
        }

        // Data dari penjualan bahan baku langsung
        $bahanBakuLangsung = DetailPenjualan::select(
            'bahan_baku_id',
            DB::raw('SUM(jumlah) as total_terjual_langsung'),
            DB::raw('SUM(sub_total) as total_pendapatan_langsung')
        )
            ->whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir, $searchBahanBaku) {
                $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
            })
            ->where('jenis_item', 'bahan_baku')
            ->when($searchBahanBaku, function ($query) use ($searchBahanBaku) {
                return $query->whereHas('bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                    $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                });
            })
            ->groupBy('bahan_baku_id')
            ->get();

        foreach ($bahanBakuLangsung as $item) {
            $bahanBaku = BahanBaku::find($item->bahan_baku_id);
            if ($bahanBaku) {
                if (!isset($bahanBakuUsage[$item->bahan_baku_id])) {
                    $bahanBakuUsage[$item->bahan_baku_id] = [
                        'bahan_baku' => $bahanBaku,
                        'total_penggunaan' => 0,
                        'total_harga_beli' => 0,
                        'total_harga_jual' => 0,
                        'total_pendapatan' => 0
                    ];
                }
                $bahanBakuUsage[$item->bahan_baku_id]['total_penggunaan'] += $item->total_terjual_langsung;
                $bahanBakuUsage[$item->bahan_baku_id]['total_harga_beli'] += ($bahanBaku->harga_beli ?? 0) * $item->total_terjual_langsung;
                $bahanBakuUsage[$item->bahan_baku_id]['total_harga_jual'] += ($bahanBaku->harga_jual ?? 0) * $item->total_terjual_langsung;
                $bahanBakuUsage[$item->bahan_baku_id]['total_pendapatan'] += $item->total_pendapatan_langsung;
            }
        }

        // Format data untuk view dengan margin keuntungan
        $formattedData = collect($bahanBakuUsage)->map(function ($item) {
            $totalHargaBeli = $item['total_harga_beli'] ?? 0;
            $totalHargaJual = $item['total_pendapatan'] ?? 0;
            $laba = $totalHargaJual - $totalHargaBeli;
            $margin = $totalHargaBeli > 0 ? ($laba / $totalHargaBeli) * 100 : 0;

            return [
                'id' => $item['bahan_baku']->id,
                'nama' => $item['bahan_baku']->nama,
                'total_penggunaan' => $item['total_penggunaan'],
                'satuan' => $item['bahan_baku']->satuan,
                'harga_beli' => $item['bahan_baku']->harga_beli,
                'harga_jual' => $item['bahan_baku']->harga_jual,
                'total_harga_beli' => $item['total_harga_beli'],
                'total_pendapatan' => $item['total_pendapatan'],
                'laba' => $laba,
                'margin_keuntungan' => $margin,
                'harga_beli_formatted' => 'Rp ' . number_format($item['bahan_baku']->harga_beli ?? 0, 0, ',', '.'),
                'harga_jual_formatted' => 'Rp ' . number_format($item['bahan_baku']->harga_jual ?? 0, 0, ',', '.'),
                'total_harga_beli_formatted' => 'Rp ' . number_format($item['total_harga_beli'] ?? 0, 0, ',', '.'),
                'total_pendapatan_formatted' => 'Rp ' . number_format($item['total_pendapatan'] ?? 0, 0, ',', '.'),
                'laba_formatted' => 'Rp ' . number_format($laba, 0, ',', '.'),
                'margin_keuntungan_formatted' => number_format($margin, 2, ',', '.') . '%',
                'total_penggunaan_formatted' => number_format($item['total_penggunaan'], 2, ',', '.')
            ];
        })->sortBy('nama')->values();

        return $formattedData;
    }

    /**
     * Mendapatkan daftar bahan baku yang digunakan dalam penjualan
     */
    private function getBahanBakuUntukPenjualan($penjualan)
    {
        $bahanBakuList = [];

        foreach ($penjualan->detailPenjualan as $detail) {
            if ($detail->jenis_item == 'produk' && $detail->produk) {
                foreach ($detail->produk->komposisi as $komposisi) {
                    $bahanBakuList[] = [
                        'nama' => $komposisi->bahanBaku->nama,
                        'jumlah' => $komposisi->jumlah * $detail->jumlah,
                        'satuan' => $komposisi->bahanBaku->satuan
                    ];
                }
            } elseif ($detail->jenis_item == 'bahan_baku' && $detail->bahanBaku) {
                $bahanBakuList[] = [
                    'nama' => $detail->bahanBaku->nama,
                    'jumlah' => $detail->jumlah,
                    'satuan' => $detail->bahanBaku->satuan
                ];
            }
        }

        // Group by nama bahan baku
        $grouped = [];
        foreach ($bahanBakuList as $item) {
            if (!isset($grouped[$item['nama']])) {
                $grouped[$item['nama']] = [
                    'nama' => $item['nama'],
                    'jumlah' => 0,
                    'satuan' => $item['satuan']
                ];
            }
            $grouped[$item['nama']]['jumlah'] += $item['jumlah'];
        }

        return array_values($grouped);
    }

    private function getBahanBakuTerlaris($tanggalAwal, $tanggalAkhir, $searchBahanBaku = null)
    {
        $produkTerjual = DetailPenjualan::select(
            'produk_id',
            DB::raw('SUM(jumlah) as total_produk_terjual')
        )
            ->whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir, $searchBahanBaku) {
                $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
                if ($searchBahanBaku) {
                    $query->whereHas('detailPenjualan.produk.komposisi.bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    });
                }
            })
            ->where('jenis_item', 'produk')
            ->groupBy('produk_id')
            ->get();

        $bahanBakuUsage = [];

        foreach ($produkTerjual as $produk) {
            $komposisi = KomposisiBahanBaku::where('produk_id', $produk->produk_id)
                ->with('bahanBaku')
                ->when($searchBahanBaku, function ($query) use ($searchBahanBaku) {
                    return $query->whereHas('bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    });
                })
                ->get();

            foreach ($komposisi as $item) {
                if ($item->bahanBaku) {
                    $bahanBakuId = $item->bahan_baku_id;
                    $jumlahPenggunaan = $item->jumlah * $produk->total_produk_terjual;

                    if (!isset($bahanBakuUsage[$bahanBakuId])) {
                        $bahanBakuUsage[$bahanBakuId] = [
                            'bahan_baku' => $item->bahanBaku,
                            'total_penggunaan' => 0
                        ];
                    }

                    $bahanBakuUsage[$bahanBakuId]['total_penggunaan'] += $jumlahPenggunaan;
                }
            }
        }

        $bahanBakuLangsung = DetailPenjualan::select(
            'bahan_baku_id',
            DB::raw('SUM(jumlah) as total_terjual_langsung')
        )
            ->whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir, $searchBahanBaku) {
                $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
            })
            ->where('jenis_item', 'bahan_baku')
            ->when($searchBahanBaku, function ($query) use ($searchBahanBaku) {
                return $query->whereHas('bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                    $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                });
            })
            ->groupBy('bahan_baku_id')
            ->get();

        foreach ($bahanBakuLangsung as $item) {
            $bahanBaku = BahanBaku::find($item->bahan_baku_id);
            if ($bahanBaku) {
                if (!isset($bahanBakuUsage[$item->bahan_baku_id])) {
                    $bahanBakuUsage[$item->bahan_baku_id] = [
                        'bahan_baku' => $bahanBaku,
                        'total_penggunaan' => 0
                    ];
                }
                $bahanBakuUsage[$item->bahan_baku_id]['total_penggunaan'] += $item->total_terjual_langsung;
            }
        }

        usort($bahanBakuUsage, function ($a, $b) {
            return $b['total_penggunaan'] <=> $a['total_penggunaan'];
        });

        $top10 = array_slice($bahanBakuUsage, 0, 10);

        return collect($top10)->map(function ($item) {
            return [
                'id' => $item['bahan_baku']->id,
                'nama' => $item['bahan_baku']->nama,
                'total_penggunaan' => $item['total_penggunaan'],
                'satuan' => $item['bahan_baku']->satuan,
                'harga_beli' => $item['bahan_baku']->harga_beli,
                'harga_jual' => $item['bahan_baku']->harga_jual,
                'margin' => $item['bahan_baku']->harga_beli > 0 ? (($item['bahan_baku']->harga_jual - $item['bahan_baku']->harga_beli) / $item['bahan_baku']->harga_beli) * 100 : 0,
                'total_penggunaan_formatted' => number_format($item['total_penggunaan'], 2, ',', '.'),
                'margin_formatted' => number_format(($item['bahan_baku']->harga_beli > 0 ? (($item['bahan_baku']->harga_jual - $item['bahan_baku']->harga_beli) / $item['bahan_baku']->harga_beli) * 100 : 0), 2, ',', '.') . '%'
            ];
        });
    }

    public function laporan(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? date('Y-m-01');
        $tanggalAkhir = $request->tanggal_akhir ?? date('Y-m-d');
        $searchBahanBaku = $request->search_bahan_baku ?? null;

        $penjualanQuery = Penjualan::with(['detailPenjualan', 'admin'])
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

        if ($searchBahanBaku) {
            $penjualanQuery->whereHas('detailPenjualan', function ($query) use ($searchBahanBaku) {
                $query->where(function ($subQuery) use ($searchBahanBaku) {
                    $subQuery->whereHas('produk.komposisi.bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    })->orWhereHas('bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    });
                });
            });
        }

        $penjualan = $penjualanQuery->orderBy('tanggal', 'desc')->get();

        $laporanDetail = $this->formatLaporanDetail($penjualan, $searchBahanBaku);

        $totalPenjualan = $penjualan->sum('total');
        $totalBayar = $penjualan->sum('bayar');
        $totalKembalian = $penjualan->sum('kembalian');

        // Hitung total bahan baku keluar
        $totalBahanBakuKeluar = $this->getTotalBahanBakuKeluar($tanggalAwal, $tanggalAkhir, $searchBahanBaku);

        // Hitung margin keuntungan
        $totalBiayaBahanBaku = $totalBahanBakuKeluar->sum('total_harga_beli');
        $labaKotor = $totalPenjualan - $totalBiayaBahanBaku;
        $marginKeuntungan = $totalBiayaBahanBaku > 0 ? (($labaKotor / $totalBiayaBahanBaku) * 100) : 0;
        $marginKeuntunganFormatted = number_format($marginKeuntungan, 2, ',', '.') . '%';

        $produkTerlaris = DetailPenjualan::select(
            'produk_id',
            DB::raw('SUM(jumlah) as total_terjual'),
            DB::raw('SUM(sub_total) as total_pendapatan')
        )
            ->whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir, $searchBahanBaku) {
                $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
                if ($searchBahanBaku) {
                    $query->whereHas('detailPenjualan.produk.komposisi.bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    });
                }
            })
            ->where('jenis_item', 'produk')
            ->groupBy('produk_id')
            ->orderBy('total_terjual', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $produk = Produk::with('komposisi.bahanBaku')->find($item->produk_id);

                // Hitung margin keuntungan untuk setiap produk
                $biayaProduksi = 0;
                $marginProduk = 0;

                if ($produk) {
                    foreach ($produk->komposisi as $komposisi) {
                        $biayaProduksi += ($komposisi->bahanBaku->harga_beli ?? 0) * $komposisi->jumlah * $item->total_terjual;
                    }

                    if ($biayaProduksi > 0) {
                        $labaProduk = $item->total_pendapatan - $biayaProduksi;
                        $marginProduk = ($labaProduk / $biayaProduksi) * 100;
                    }
                }

                return [
                    'id' => $item->produk_id,
                    'nama' => $produk ? $produk->nama : 'Produk Tidak Ditemukan',
                    'total_terjual' => $item->total_terjual,
                    'total_pendapatan' => $item->total_pendapatan,
                    'satuan' => $produk ? $produk->satuan : '-',
                    'harga' => $produk ? $produk->harga : 0,
                    'total_pendapatan_formatted' => 'Rp ' . number_format($item->total_pendapatan, 0, ',', '.'),
                    'biaya_produksi' => $biayaProduksi,
                    'biaya_produksi_formatted' => 'Rp ' . number_format($biayaProduksi, 0, ',', '.'),
                    'laba_produk' => $item->total_pendapatan - $biayaProduksi,
                    'laba_produk_formatted' => 'Rp ' . number_format($item->total_pendapatan - $biayaProduksi, 0, ',', '.'),
                    'margin_keuntungan' => $marginProduk,
                    'margin_keuntungan_formatted' => number_format($marginProduk, 2, ',', '.') . '%'
                ];
            });

        $bahanBakuTerlaris = $this->getBahanBakuTerlaris($tanggalAwal, $tanggalAkhir, $searchBahanBaku);
        $bahanBakuList = BahanBaku::orderBy('nama')->get();

        if ($request->has('print')) {
            return view('owner.penjualan.laporan-print', compact(
                'penjualan',
                'laporanDetail',
                'totalPenjualan',
                'totalBayar',
                'totalKembalian',
                'produkTerlaris',
                'bahanBakuTerlaris',
                'bahanBakuList',
                'tanggalAwal',
                'tanggalAkhir',
                'searchBahanBaku',
                'totalBahanBakuKeluar',
                'totalBiayaBahanBaku',
                'labaKotor',
                'marginKeuntunganFormatted'
            ));
        }

        return view('owner.penjualan.laporan', compact(
            'penjualan',
            'laporanDetail',
            'totalPenjualan',
            'totalBayar',
            'totalKembalian',
            'produkTerlaris',
            'bahanBakuTerlaris',
            'bahanBakuList',
            'tanggalAwal',
            'tanggalAkhir',
            'searchBahanBaku',
            'totalBahanBakuKeluar',
            'totalBiayaBahanBaku',
            'labaKotor',
            'marginKeuntunganFormatted'
        ));
    }

    /**
     * Format data laporan detail dengan total yang benar per transaksi
     */
    private function formatLaporanDetail($penjualan, $searchBahanBaku = null)
    {
        $formattedData = [];

        foreach ($penjualan as $transaksi) {
            $itemCount = $transaksi->detailPenjualan->count();
            $currentItem = 0;

            foreach ($transaksi->detailPenjualan as $detail) {
                if ($searchBahanBaku) {
                    $containsBahanBaku = false;

                    if ($detail->jenis_item == 'produk' && $detail->produk) {
                        foreach ($detail->produk->komposisi as $komp) {
                            if (stripos($komp->bahanBaku->nama, $searchBahanBaku) !== false) {
                                $containsBahanBaku = true;
                                break;
                            }
                        }
                    } elseif ($detail->jenis_item == 'bahan_baku' && $detail->bahanBaku) {
                        if (stripos($detail->bahanBaku->nama, $searchBahanBaku) !== false) {
                            $containsBahanBaku = true;
                        }
                    }

                    if (!$containsBahanBaku) {
                        continue;
                    }
                }

                $row = [
                    'tanggal' => $transaksi->tanggal,
                    'kode_penjualan' => $transaksi->kode_penjualan,
                    'nama_customer' => $transaksi->nama_customer,
                    'nama_admin' => $transaksi->admin ? $transaksi->admin->name : '-',
                    'produk' => $detail->nama_produk,
                    'jenis_item' => $detail->jenis_item,
                    'jumlah_produk' => $detail->jumlah,
                    'bahan_baku_digunakan' => '',
                    'jumlah_digunakan' => '',
                    'total' => $transaksi->total,
                    'bayar' => $transaksi->bayar,
                    'kembalian' => $transaksi->kembalian,
                    'item_index' => ++$currentItem,
                    'item_count' => $itemCount
                ];

                if ($detail->jenis_item == 'produk' && $detail->produk) {
                    $komposisi = $detail->produk->komposisi;
                    $bahanBakuList = [];
                    $totalJumlah = 0;

                    foreach ($komposisi as $komp) {
                        $bahanBakuList[] = $komp->bahanBaku->nama . ' (' . $komp->jumlah * $detail->jumlah . ' ' . $komp->bahanBaku->satuan . ')';
                        $totalJumlah += $komp->jumlah * $detail->jumlah;
                    }

                    $row['bahan_baku_digunakan'] = implode(', ', $bahanBakuList);
                    $row['jumlah_digunakan'] = $totalJumlah;
                } else {
                    // Jika item adalah bahan baku langsung
                    $row['bahan_baku_digunakan'] = $detail->bahanBaku ? $detail->bahanBaku->nama : '-';
                    $row['jumlah_digunakan'] = $detail->jumlah;
                }

                $formattedData[] = $row;
            }
        }

        return $formattedData;
    }

    public function generatePDF(Request $request)
    {
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date'
        ]);

        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $searchBahanBaku = $request->search_bahan_baku ?? null;

        $penjualanQuery = Penjualan::with(['detailPenjualan', 'admin'])
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

        if ($searchBahanBaku) {
            $penjualanQuery->whereHas('detailPenjualan', function ($query) use ($searchBahanBaku) {
                $query->where(function ($subQuery) use ($searchBahanBaku) {
                    $subQuery->whereHas('produk.komposisi.bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    })->orWhereHas('bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    });
                });
            });
        }

        $penjualan = $penjualanQuery->orderBy('tanggal', 'desc')->get();

        // Format data untuk PDF
        $laporanDetail = $this->formatLaporanDetail($penjualan, $searchBahanBaku);

        $totalPenjualan = $penjualan->sum('total');
        $totalBayar = $penjualan->sum('bayar');
        $totalKembalian = $penjualan->sum('kembalian');

        // Hitung total bahan baku keluar
        $totalBahanBakuKeluar = $this->getTotalBahanBakuKeluar($tanggalAwal, $tanggalAkhir, $searchBahanBaku);

        // Hitung margin keuntungan
        $totalBiayaBahanBaku = $totalBahanBakuKeluar->sum('total_harga_beli');
        $labaKotor = $totalPenjualan - $totalBiayaBahanBaku;
        $marginKeuntungan = $totalBiayaBahanBaku > 0 ? (($labaKotor / $totalBiayaBahanBaku) * 100) : 0;
        $marginKeuntunganFormatted = number_format($marginKeuntungan, 2, ',', '.') . '%';

        $produkTerlaris = DetailPenjualan::select(
            'produk_id',
            DB::raw('SUM(jumlah) as total_terjual'),
            DB::raw('SUM(sub_total) as total_pendapatan')
        )
            ->whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir, $searchBahanBaku) {
                $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
                if ($searchBahanBaku) {
                    $query->whereHas('detailPenjualan.produk.komposisi.bahanBaku', function ($bbQuery) use ($searchBahanBaku) {
                        $bbQuery->where('nama', 'like', '%' . $searchBahanBaku . '%');
                    });
                }
            })
            ->where('jenis_item', 'produk')
            ->groupBy('produk_id')
            ->orderBy('total_terjual', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $produk = Produk::with('komposisi.bahanBaku')->find($item->produk_id);

                // Hitung margin keuntungan untuk setiap produk
                $biayaProduksi = 0;
                $marginProduk = 0;

                if ($produk) {
                    foreach ($produk->komposisi as $komposisi) {
                        $biayaProduksi += ($komposisi->bahanBaku->harga_beli ?? 0) * $komposisi->jumlah * $item->total_terjual;
                    }

                    if ($biayaProduksi > 0) {
                        $labaProduk = $item->total_pendapatan - $biayaProduksi;
                        $marginProduk = ($labaProduk / $biayaProduksi) * 100;
                    }
                }

                return [
                    'id' => $item->produk_id,
                    'nama' => $produk ? $produk->nama : 'Produk Tidak Ditemukan',
                    'total_terjual' => $item->total_terjual,
                    'total_pendapatan' => $item->total_pendapatan,
                    'satuan' => $produk ? $produk->satuan : '-',
                    'harga' => $produk ? $produk->harga : 0,
                    'total_pendapatan_formatted' => 'Rp ' . number_format($item->total_pendapatan, 0, ',', '.'),
                    'biaya_produksi' => $biayaProduksi,
                    'biaya_produksi_formatted' => 'Rp ' . number_format($biayaProduksi, 0, ',', '.'),
                    'laba_produk' => $item->total_pendapatan - $biayaProduksi,
                    'laba_produk_formatted' => 'Rp ' . number_format($item->total_pendapatan - $biayaProduksi, 0, ',', '.'),
                    'margin_keuntungan' => $marginProduk,
                    'margin_keuntungan_formatted' => number_format($marginProduk, 2, ',', '.') . '%'
                ];
            });

        $bahanBakuTerlaris = $this->getBahanBakuTerlaris($tanggalAwal, $tanggalAkhir, $searchBahanBaku);

        $pdf = PDF::loadView('owner.penjualan.laporan-pdf', compact(
            'penjualan',
            'laporanDetail',
            'totalPenjualan',
            'totalBayar',
            'totalKembalian',
            'produkTerlaris',
            'bahanBakuTerlaris',
            'tanggalAwal',
            'tanggalAkhir',
            'searchBahanBaku',
            'totalBahanBakuKeluar',
            'totalBiayaBahanBaku',
            'labaKotor',
            'marginKeuntunganFormatted'
        ));

        $filename = 'laporan-penjualan-' . $tanggalAwal . '-hingga-' . $tanggalAkhir;
        if ($searchBahanBaku) {
            $filename .= '-filter-' . $searchBahanBaku;
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    public function show($id)
    {
        try {
            $penjualan = Penjualan::with(['detailPenjualan.produk.komposisi.bahanBaku', 'detailPenjualan.bahanBaku'])
                ->findOrFail($id);

            $detailWithBahanBaku = $penjualan->detailPenjualan->map(function ($detail) {
                $bahanBakuInfo = [];

                if ($detail->jenis_item == 'produk' && $detail->produk) {
                    foreach ($detail->produk->komposisi as $komposisi) {
                        $bahanBakuInfo[] = [
                            'nama' => $komposisi->bahanBaku->nama,
                            'jumlah' => $komposisi->jumlah * $detail->jumlah,
                            'satuan' => $komposisi->bahanBaku->satuan
                        ];
                    }
                } elseif ($detail->jenis_item == 'bahan_baku' && $detail->bahanBaku) {
                    $bahanBakuInfo[] = [
                        'nama' => $detail->bahanBaku->nama,
                        'jumlah' => $detail->jumlah,
                        'satuan' => $detail->bahanBaku->satuan
                    ];
                }

                return [
                    'id' => $detail->id,
                    'produk_id' => $detail->produk_id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'nama_produk' => $detail->nama_produk,
                    'jenis_item' => $detail->jenis_item,
                    'jumlah' => $detail->jumlah,
                    'harga_sat' => $detail->harga_sat,
                    'harga_sat_formatted' => 'Rp ' . number_format($detail->harga_sat, 0, ',', '.'),
                    'sub_total' => $detail->sub_total,
                    'sub_total_formatted' => 'Rp ' . number_format($detail->sub_total, 0, ',', '.'),
                    'bahan_baku_digunakan' => $bahanBakuInfo,
                    'created_at' => $detail->created_at,
                    'updated_at' => $detail->updated_at
                ];
            });

            $formattedData = [
                'id' => $penjualan->id,
                'kode_penjualan' => $penjualan->kode_penjualan,
                'nama_customer' => $penjualan->nama_customer,
                'total' => $penjualan->total,
                'total_formatted' => 'Rp ' . number_format($penjualan->total, 0, ',', '.'),
                'bayar' => $penjualan->bayar,
                'bayar_formatted' => 'Rp ' . number_format($penjualan->bayar, 0, ',', '.'),
                'kembalian' => $penjualan->kembalian,
                'kembalian_formatted' => 'Rp ' . number_format($penjualan->kembalian, 0, ',', '.'),
                'tanggal' => $penjualan->tanggal,
                'admin' => $penjualan->admin ? $penjualan->admin->name : null,
                'detail_penjualan' => $detailWithBahanBaku,
                'total_bahan_baku' => $this->calculateTotalBahanBaku($penjualan)
            ];

            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Menghitung total bahan baku yang digunakan dalam penjualan
     */
    private function calculateTotalBahanBaku($penjualan)
    {
        $totalBahanBaku = [];

        foreach ($penjualan->detailPenjualan as $detail) {
            if ($detail->jenis_item == 'produk' && $detail->produk) {
                foreach ($detail->produk->komposisi as $komposisi) {
                    $bahanBakuId = $komposisi->bahan_baku_id;
                    $jumlah = $komposisi->jumlah * $detail->jumlah;

                    if (!isset($totalBahanBaku[$bahanBakuId])) {
                        $totalBahanBaku[$bahanBakuId] = [
                            'nama' => $komposisi->bahanBaku->nama,
                            'jumlah' => 0,
                            'satuan' => $komposisi->bahanBaku->satuan
                        ];
                    }
                    $totalBahanBaku[$bahanBakuId]['jumlah'] += $jumlah;
                }
            } elseif ($detail->jenis_item == 'bahan_baku' && $detail->bahanBaku) {
                $bahanBakuId = $detail->bahan_baku_id;

                if (!isset($totalBahanBaku[$bahanBakuId])) {
                    $totalBahanBaku[$bahanBakuId] = [
                        'nama' => $detail->bahanBaku->nama,
                        'jumlah' => 0,
                        'satuan' => $detail->bahanBaku->satuan
                    ];
                }
                $totalBahanBaku[$bahanBakuId]['jumlah'] += $detail->jumlah;
            }
        }

        return array_values($totalBahanBaku);
    }

    public function printNota($id)
    {
        try {
            $penjualan = Penjualan::with(['detailPenjualan.produk.komposisi.bahanBaku', 'detailPenjualan.bahanBaku'])
                ->findOrFail($id);

            $penjualan->total_bahan_baku = $this->calculateTotalBahanBaku($penjualan);

            return view('owner.penjualan.nota', compact('penjualan'));
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }
}
