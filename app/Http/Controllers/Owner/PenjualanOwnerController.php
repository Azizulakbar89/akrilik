<?php

namespace App\Http\Controllers\Owner;

use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\KomposisiBahanBaku;
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

        $penjualan = Penjualan::with(['detailPenjualan', 'admin'])
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
            ->orderBy('created_at', 'desc')
            ->get();

        $produkTerlaris = DetailPenjualan::select(
            'produk_id',
            DB::raw('SUM(jumlah) as total_terjual'),
            DB::raw('SUM(sub_total) as total_pendapatan')
        )
            ->whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir) {
                $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
            })
            ->where('jenis_item', 'produk')
            ->groupBy('produk_id')
            ->orderBy('total_terjual', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $produk = Produk::find($item->produk_id);
                return [
                    'id' => $item->produk_id,
                    'nama' => $produk ? $produk->nama : 'Produk Tidak Ditemukan',
                    'total_terjual' => $item->total_terjual,
                    'total_pendapatan' => $item->total_pendapatan,
                    'satuan' => $produk ? $produk->satuan : '-'
                ];
            });

        $bahanBakuTerlaris = $this->getBahanBakuTerlaris($tanggalAwal, $tanggalAkhir);

        return view('owner.penjualan.index', compact(
            'penjualan',
            'produkTerlaris',
            'bahanBakuTerlaris',
            'tanggalAwal',
            'tanggalAkhir'
        ));
    }

    private function getBahanBakuTerlaris($tanggalAwal, $tanggalAkhir)
    {
        $produkTerjual = DetailPenjualan::select(
            'produk_id',
            DB::raw('SUM(jumlah) as total_produk_terjual')
        )
            ->whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir) {
                $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
            })
            ->where('jenis_item', 'produk')
            ->groupBy('produk_id')
            ->get();

        $bahanBakuUsage = [];

        foreach ($produkTerjual as $produk) {
            $komposisi = KomposisiBahanBaku::where('produk_id', $produk->produk_id)
                ->with('bahanBaku')
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
            ->whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir) {
                $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
            })
            ->where('jenis_item', 'bahan_baku')
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
                'harga_beli' => $item['bahan_baku']->harga_beli
            ];
        });
    }

    public function laporan(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? date('Y-m-01');
        $tanggalAkhir = $request->tanggal_akhir ?? date('Y-m-d');

        $penjualan = Penjualan::with(['detailPenjualan', 'admin'])
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalPenjualan = $penjualan->sum('total');
        $totalBayar = $penjualan->sum('bayar');
        $totalKembalian = $penjualan->sum('kembalian');

        $produkTerlaris = DetailPenjualan::select(
            'produk_id',
            DB::raw('SUM(jumlah) as total_terjual'),
            DB::raw('SUM(sub_total) as total_pendapatan')
        )
            ->whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir) {
                $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
            })
            ->where('jenis_item', 'produk')
            ->groupBy('produk_id')
            ->orderBy('total_terjual', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $produk = Produk::find($item->produk_id);
                return [
                    'id' => $item->produk_id,
                    'nama' => $produk ? $produk->nama : 'Produk Tidak Ditemukan',
                    'total_terjual' => $item->total_terjual,
                    'total_pendapatan' => $item->total_pendapatan,
                    'satuan' => $produk ? $produk->satuan : '-',
                    'harga' => $produk ? $produk->harga : 0
                ];
            });

        $bahanBakuTerlaris = $this->getBahanBakuTerlaris($tanggalAwal, $tanggalAkhir);

        if ($request->has('print')) {
            return view('owner.penjualan.laporan-print', compact(
                'penjualan',
                'totalPenjualan',
                'totalBayar',
                'totalKembalian',
                'produkTerlaris',
                'bahanBakuTerlaris',
                'tanggalAwal',
                'tanggalAkhir'
            ));
        }

        return view('owner.penjualan.laporan', compact(
            'penjualan',
            'totalPenjualan',
            'totalBayar',
            'totalKembalian',
            'produkTerlaris',
            'bahanBakuTerlaris',
            'tanggalAwal',
            'tanggalAkhir'
        ));
    }

    public function generatePDF(Request $request)
    {
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date'
        ]);

        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;

        $penjualan = Penjualan::with(['detailPenjualan', 'admin'])
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalPenjualan = $penjualan->sum('total');
        $totalBayar = $penjualan->sum('bayar');
        $totalKembalian = $penjualan->sum('kembalian');

        $produkTerlaris = DetailPenjualan::select(
            'produk_id',
            DB::raw('SUM(jumlah) as total_terjual'),
            DB::raw('SUM(sub_total) as total_pendapatan')
        )
            ->whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir) {
                $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
            })
            ->where('jenis_item', 'produk')
            ->groupBy('produk_id')
            ->orderBy('total_terjual', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $produk = Produk::find($item->produk_id);
                return [
                    'id' => $item->produk_id,
                    'nama' => $produk ? $produk->nama : 'Produk Tidak Ditemukan',
                    'total_terjual' => $item->total_terjual,
                    'total_pendapatan' => $item->total_pendapatan,
                    'satuan' => $produk ? $produk->satuan : '-',
                    'harga' => $produk ? $produk->harga : 0
                ];
            });

        $bahanBakuTerlaris = $this->getBahanBakuTerlaris($tanggalAwal, $tanggalAkhir);

        $pdf = PDF::loadView('owner.penjualan.laporan-pdf', compact(
            'penjualan',
            'totalPenjualan',
            'totalBayar',
            'totalKembalian',
            'produkTerlaris',
            'bahanBakuTerlaris',
            'tanggalAwal',
            'tanggalAkhir'
        ));

        return $pdf->download('laporan-penjualan-' . $tanggalAwal . '-hingga-' . $tanggalAkhir . '.pdf');
    }

    public function show($id)
    {
        try {
            $penjualan = Penjualan::with('detailPenjualan')->findOrFail($id);

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
                'detail_penjualan' => $penjualan->detailPenjualan->map(function ($detail) {
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
                        'created_at' => $detail->created_at,
                        'updated_at' => $detail->updated_at
                    ];
                })
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

    public function printNota($id)
    {
        try {
            $penjualan = Penjualan::with('detailPenjualan')->findOrFail($id);

            return view('owner.penjualan.nota', compact('penjualan'));
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }
}
