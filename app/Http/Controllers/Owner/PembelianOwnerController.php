<?php

namespace App\Http\Controllers\Owner;

use DB;
use App\Models\Supplier;
use App\Models\BahanBaku;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use App\Models\DetailPembelian;
use App\Http\Controllers\Controller;

class PembelianOwnerController extends Controller
{
    public function index()
    {
        $pembelian = Pembelian::with(['supplier', 'detailPembelian.bahanBaku'])
            ->orderBy('created_at', 'desc')
            ->get();
        $supplier = Supplier::all();
        $bahanBaku = BahanBaku::all();

        // PERBAIKAN: Gunakan rekomendasi berdasarkan ROP
        $rekomendasi = BahanBaku::perluPembelian()
            ->get()
            ->map(function ($bahan) {
                return $bahan->rekomendasi_pembelian_rop;
            })
            ->filter(function ($item) {
                return !is_null($item) && isset($item['jumlah_rekomendasi']) && $item['jumlah_rekomendasi'] > 0;
            })
            ->values();

        $stokTidakAman = BahanBaku::stokTidakAman()->get();
        $totalRekomendasi = $rekomendasi->sum('total_nilai');

        return view('owner.pembelian.index', compact(
            'pembelian',
            'supplier',
            'bahanBaku',
            'rekomendasi',
            'stokTidakAman',
            'totalRekomendasi'
        ));
    }

    public function create()
    {
        $supplier = Supplier::all();
        $bahanBaku = BahanBaku::all();

        $rekomendasi = BahanBaku::perluPembelian()
            ->get()
            ->map(function ($bahan) {
                return $bahan->rekomendasi_pembelian_rop;
            })
            ->filter(function ($item) {
                return !is_null($item) && isset($item['jumlah_rekomendasi']) && $item['jumlah_rekomendasi'] > 0;
            })
            ->values();

        return view('owner.pembelian.create', compact('supplier', 'bahanBaku', 'rekomendasi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:supplier,id',
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.bahan_baku_id' => 'required|exists:bahan_baku,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['jumlah'] * $item['harga'];
            }

            $pembelian = Pembelian::create([
                'supplier_id' => $request->supplier_id,
                'total' => $total,
                'tanggal' => $request->tanggal,
                'status' => 'menunggu_persetujuan'
            ]);

            foreach ($request->items as $item) {
                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'bahan_baku_id' => $item['bahan_baku_id'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga'],
                    'sub_total' => $item['jumlah'] * $item['harga']
                ]);
            }

            DB::commit();
            return response()->json(['success' => 'Pembelian berhasil disimpan dan menunggu persetujuan']);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error storing pembelian: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function storePembelianCepat(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:supplier,id',
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.bahan_baku_id' => 'required|exists:bahan_baku,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $total = 0;
            $items = [];

            foreach ($request->items as $item) {
                $total += $item['jumlah'] * $item['harga'];
                $items[] = $item;
            }

            $pembelian = Pembelian::create([
                'supplier_id' => $request->supplier_id,
                'total' => $total,
                'tanggal' => $request->tanggal,
                'status' => 'menunggu_persetujuan'
            ]);

            foreach ($items as $item) {
                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'bahan_baku_id' => $item['bahan_baku_id'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga'],
                    'sub_total' => $item['jumlah'] * $item['harga']
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => 'Pembelian cepat berhasil disimpan',
                'redirect' => route('owner.pembelian.index')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error storing pembelian cepat: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // PERBAIKAN: Method storeRekomendasi untuk ROP
    public function storeRekomendasi(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:supplier,id',
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1'
        ]);

        DB::beginTransaction();
        try {
            $total = 0;
            $items = [];

            foreach ($request->items as $bahanBakuId) {
                $bahanBaku = BahanBaku::findOrFail($bahanBakuId);

                if ($bahanBaku->isPerluPembelian()) {
                    // PERBAIKAN: Gunakan jumlah pemesanan berdasarkan ROP
                    $jumlah = $bahanBaku->jumlahPemesananRekomendasiRop();
                    $harga = $bahanBaku->harga_beli;

                    if ($jumlah > 0) {
                        $subTotal = $jumlah * $harga;

                        $items[] = [
                            'bahan_baku_id' => $bahanBakuId,
                            'jumlah' => $jumlah,
                            'harga' => $harga,
                            'sub_total' => $subTotal
                        ];

                        $total += $subTotal;
                    }
                }
            }

            if (empty($items)) {
                DB::rollback();
                return response()->json(['error' => 'Tidak ada bahan baku yang perlu pembelian'], 400);
            }

            $pembelian = Pembelian::create([
                'supplier_id' => $request->supplier_id,
                'total' => $total,
                'tanggal' => $request->tanggal,
                'status' => 'menunggu_persetujuan'
            ]);

            foreach ($items as $item) {
                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'bahan_baku_id' => $item['bahan_baku_id'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga'],
                    'sub_total' => $item['sub_total']
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => 'Pembelian dari rekomendasi sistem ROP berhasil disimpan',
                'redirect' => route('owner.pembelian.index')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error storing rekomendasi pembelian: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $pembelian = Pembelian::with(['supplier', 'detailPembelian.bahanBaku'])->findOrFail($id);
        return response()->json($pembelian);
    }

    public function edit($id)
    {
        $pembelian = Pembelian::with(['supplier', 'detailPembelian.bahanBaku'])->findOrFail($id);

        if ($pembelian->status !== 'menunggu_persetujuan') {
            return response()->json(['error' => 'Pembelian hanya dapat diubah saat status menunggu persetujuan'], 403);
        }

        $supplier = Supplier::all();
        $bahanBaku = BahanBaku::all();

        return response()->json([
            'pembelian' => $pembelian,
            'supplier' => $supplier,
            'bahanBaku' => $bahanBaku
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|exists:supplier,id',
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.bahan_baku_id' => 'required|exists:bahan_baku,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $pembelian = Pembelian::findOrFail($id);

            if ($pembelian->status !== 'menunggu_persetujuan') {
                return response()->json(['error' => 'Pembelian hanya dapat diubah saat status menunggu persetujuan'], 403);
            }

            DetailPembelian::where('pembelian_id', $id)->delete();

            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['jumlah'] * $item['harga'];
            }

            $pembelian->update([
                'supplier_id' => $request->supplier_id,
                'total' => $total,
                'tanggal' => $request->tanggal,
            ]);

            foreach ($request->items as $item) {
                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'bahan_baku_id' => $item['bahan_baku_id'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga'],
                    'sub_total' => $item['jumlah'] * $item['harga']
                ]);
            }

            DB::commit();
            return response()->json(['success' => 'Pembelian berhasil diupdate']);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error updating pembelian: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pembelian = Pembelian::findOrFail($id);

            if ($pembelian->status !== 'menunggu_persetujuan') {
                return response()->json(['error' => 'Pembelian hanya dapat dihapus saat status menunggu persetujuan'], 403);
            }

            $pembelian->delete();

            DB::commit();
            return response()->json(['success' => 'Pembelian berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error deleting pembelian: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $pembelian = Pembelian::with('detailPembelian.bahanBaku')->findOrFail($id);

            if ($pembelian->status !== 'menunggu_persetujuan') {
                return response()->json(['error' => 'Pembelian tidak dapat disetujui'], 403);
            }

            $pembelian->update([
                'status' => 'completed'
            ]);

            foreach ($pembelian->detailPembelian as $detail) {
                $bahanBaku = $detail->bahanBaku;
                if ($bahanBaku) {
                    $bahanBaku->stok += $detail->jumlah;
                    $bahanBaku->save();
                }
            }

            DB::commit();
            return response()->json(['success' => 'Pembelian berhasil disetujui dan stok diperbarui']);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error approving pembelian: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // PERBAIKAN: Method reject dengan transaction
    public function reject($id)
    {
        DB::beginTransaction();
        try {
            $pembelian = Pembelian::findOrFail($id);

            if ($pembelian->status !== 'menunggu_persetujuan') {
                DB::rollback();
                return response()->json(['error' => 'Pembelian hanya dapat ditolak saat status menunggu persetujuan'], 403);
            }

            $pembelian->update(['status' => 'ditolak']);

            DB::commit();
            return response()->json(['success' => 'Pembelian berhasil ditolak']);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error rejecting pembelian: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // PERBAIKAN: Method getRekomendasiData untuk ROP
    public function getRekomendasiData()
    {
        try {
            $rekomendasi = BahanBaku::perluPembelian()
                ->get()
                ->map(function ($bahan) {
                    $recommendation = $bahan->rekomendasi_pembelian_rop;
                    if (!$recommendation || !isset($recommendation['jumlah_rekomendasi']) || $recommendation['jumlah_rekomendasi'] <= 0) {
                        return null;
                    }

                    return [
                        'bahan_baku_id' => $bahan->id,
                        'nama' => $bahan->nama,
                        'stok_sekarang' => $bahan->stok,
                        'min' => $bahan->min,
                        'max' => $bahan->max,
                        'rop' => $bahan->rop,
                        'jumlah_rekomendasi' => $bahan->jumlahPemesananRekomendasiRop(),
                        'harga_beli' => $bahan->harga_beli,
                        'total_nilai' => $bahan->totalNilaiPemesananRekomendasiRop(),
                        'satuan' => $bahan->satuan,
                        'perlu_pembelian' => $bahan->isPerluPembelian()
                    ];
                })
                ->filter()
                ->values();

            $totalRekomendasi = $rekomendasi->sum('total_nilai');

            return response()->json([
                'success' => true,
                'rekomendasi' => $rekomendasi,
                'total_rekomendasi' => $totalRekomendasi,
                'jumlah_item' => $rekomendasi->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting rekomendasi data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function laporan(Request $request)
    {
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date',
            'status' => 'nullable|in:semua,completed,menunggu_persetujuan,ditolak'
        ]);

        $query = Pembelian::with(['supplier', 'detailPembelian.bahanBaku'])
            ->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir])
            ->orderBy('tanggal', 'asc');

        if ($request->status && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        $pembelian = $query->get();
        $totalPembelian = $pembelian->sum('total');

        $supplierTerbanyak = Pembelian::select(
            'supplier_id',
            DB::raw('COUNT(*) as jumlah_transaksi'),
            DB::raw('SUM(total) as total_pembelian')
        )
            ->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir])
            ->where('status', 'completed')
            ->groupBy('supplier_id')
            ->orderByDesc('jumlah_transaksi')
            ->limit(5)
            ->get();

        $bahanBakuTerbanyak = DetailPembelian::select(
            'bahan_baku_id',
            DB::raw('SUM(jumlah) as total_dibeli'),
            DB::raw('SUM(sub_total) as total_pembelian')
        )
            ->whereHas('pembelian', function ($q) use ($request) {
                $q->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir])
                    ->where('status', 'completed');
            })
            ->groupBy('bahan_baku_id')
            ->orderByDesc('total_dibeli')
            ->limit(5)
            ->get();

        $data = [
            'pembelian' => $pembelian,
            'totalPembelian' => $totalPembelian,
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
            'status' => $request->status,
            'supplierTerbanyak' => $supplierTerbanyak,
            'bahanBakuTerbanyak' => $bahanBakuTerbanyak,
            'request' => $request
        ];

        return view('owner.pembelian.laporan_pdf', $data);
    }
}
