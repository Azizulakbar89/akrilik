<?php

namespace App\Http\Controllers\Owner;

use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class PenjualanOwnerController extends Controller
{
    public function index()
    {
        $penjualan = Penjualan::with('detailPenjualan')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('owner.penjualan.index', compact('penjualan'));
    }

    public function laporan(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? date('Y-m-01');
        $tanggalAkhir = $request->tanggal_akhir ?? date('Y-m-d');

        $penjualan = Penjualan::with('detailPenjualan')
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalPenjualan = $penjualan->sum('total');
        $totalBayar = $penjualan->sum('bayar');
        $totalKembalian = $penjualan->sum('kembalian');

        $produkTerlaris = DetailPenjualan::whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir) {
            $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
        })
            ->where('jenis_item', 'produk')
            ->select('produk_id', DB::raw('SUM(jumlah) as total_terjual'))
            ->groupBy('produk_id')
            ->with('produk')
            ->orderBy('total_terjual', 'desc')
            ->take(10)
            ->get();

        $bahanBakuTerlaris = DetailPenjualan::whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir) {
            $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
        })
            ->where('jenis_item', 'bahan_baku')
            ->select('bahan_baku_id', DB::raw('SUM(jumlah) as total_terjual'))
            ->groupBy('bahan_baku_id')
            ->with('bahanBaku')
            ->orderBy('total_terjual', 'desc')
            ->take(10)
            ->get();

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

        $penjualan = Penjualan::with('detailPenjualan')
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalPenjualan = $penjualan->sum('total');
        $totalBayar = $penjualan->sum('bayar');
        $totalKembalian = $penjualan->sum('kembalian');

        $produkTerlaris = DetailPenjualan::whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir) {
            $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
        })
            ->where('jenis_item', 'produk')
            ->select('produk_id', DB::raw('SUM(jumlah) as total_terjual'))
            ->groupBy('produk_id')
            ->with('produk')
            ->orderBy('total_terjual', 'desc')
            ->take(10)
            ->get();

        $bahanBakuTerlaris = DetailPenjualan::whereHas('penjualan', function ($query) use ($tanggalAwal, $tanggalAkhir) {
            $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
        })
            ->where('jenis_item', 'bahan_baku')
            ->select('bahan_baku_id', DB::raw('SUM(jumlah) as total_terjual'))
            ->groupBy('bahan_baku_id')
            ->with('bahanBaku')
            ->orderBy('total_terjual', 'desc')
            ->take(10)
            ->get();

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
                'total_formatted' => $penjualan->total_formatted,
                'bayar' => $penjualan->bayar,
                'bayar_formatted' => $penjualan->bayar_formatted,
                'kembalian' => $penjualan->kembalian,
                'kembalian_formatted' => $penjualan->kembalian_formatted,
                'tanggal' => $penjualan->tanggal,
                'detail_penjualan' => $penjualan->detailPenjualan->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'produk_id' => $detail->produk_id,
                        'bahan_baku_id' => $detail->bahan_baku_id,
                        'nama_produk' => $detail->nama_produk,
                        'jenis_item' => $detail->jenis_item,
                        'jumlah' => $detail->jumlah,
                        'harga_sat' => $detail->harga_sat,
                        'harga_sat_formatted' => $detail->harga_sat_formatted,
                        'sub_total' => $detail->sub_total,
                        'sub_total_formatted' => $detail->sub_total_formatted,
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
