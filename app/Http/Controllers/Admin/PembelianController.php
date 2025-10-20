<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Models\Supplier;
use App\Models\BahanBaku;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use App\Models\DetailPembelian;
use App\Http\Controllers\Controller;

class PembelianController extends Controller
{
    public function index()
    {
        $pembelian = Pembelian::with(['supplier', 'detailPembelian.bahanBaku'])
            ->orderBy('created_at', 'desc')
            ->get();
        $supplier = Supplier::all();
        $bahanBaku = BahanBaku::all();

        // Ambil rekomendasi pembelian berdasarkan sistem Min-Max
        $rekomendasi = BahanBaku::perluPembelian()->get()->map(function ($bahan) {
            return $bahan->rekomendasi_pembelian;
        })->filter();

        $stokTidakAman = BahanBaku::stokTidakAman()->get();

        $totalRekomendasi = $rekomendasi->sum('total_nilai');

        return view('admin.pembelian.index', compact(
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

        $rekomendasi = BahanBaku::perluPembelian()->get()->map(function ($bahan) {
            return $bahan->rekomendasi_pembelian;
        })->filter();

        return view('admin.pembelian.create', compact('supplier', 'bahanBaku', 'rekomendasi'));
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
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function createFromRekomendasi(Request $request)
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
                    $jumlah = $bahanBaku->jumlahPemesananRekomendasi();
                    $harga = $bahanBaku->harga_beli;
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

            if (empty($items)) {
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
            return response()->json(['success' => 'Pembelian dari rekomendasi sistem Min-Max berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollback();
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

            // Hapus detail pembelian lama
            DetailPembelian::where('pembelian_id', $id)->delete();

            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['jumlah'] * $item['harga'];
            }

            // Update pembelian
            $pembelian->update([
                'supplier_id' => $request->supplier_id,
                'total' => $total,
                'tanggal' => $request->tanggal,
            ]);

            // Buat detail pembelian baru
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
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getStokTidakAman()
    {
        $stokTidakAman = BahanBaku::whereColumn('stok', '<=', 'min')
            ->select('id', 'nama', 'stok', 'min', 'max', 'satuan', 'harga_beli')
            ->get()
            ->map(function ($bahan) {
                $bahan->jumlah_rekomendasi = $bahan->jumlahPemesananRekomendasi();
                $bahan->total_nilai = $bahan->totalNilaiPemesananRekomendasi();
                return $bahan;
            });

        return response()->json($stokTidakAman);
    }

    public function getRekomendasiPembelian()
    {
        $rekomendasi = BahanBaku::perluPembelian()
            ->get()
            ->map(function ($bahan) {
                return $bahan->rekomendasi_pembelian;
            })
            ->filter()
            ->values();

        $totalRekomendasi = $rekomendasi->sum('total_nilai');

        return response()->json([
            'rekomendasi' => $rekomendasi,
            'total_rekomendasi' => $totalRekomendasi,
            'jumlah_item' => $rekomendasi->count()
        ]);
    }

    // Method untuk mendapatkan detail perhitungan sistem Min-Max
    public function getDetailPerhitungan($id)
    {
        try {
            $bahanBaku = BahanBaku::findOrFail($id);
            $parameters = $bahanBaku->hitungParameterStok();
            $statistik = $parameters['statistik'];

            $calculationDetail = [
                'bahan_baku' => $bahanBaku->nama,
                'lead_time' => $bahanBaku->lead_time . ' hari',
                'stok_sekarang' => $bahanBaku->stok,
                'statistik_penggunaan' => [
                    'total_keluar' => $statistik['total_keluar'],
                    'count_keluar' => $statistik['count_keluar'],
                    'rata_rata_per_hari' => $statistik['rata_rata'],
                    'maks_keluar_per_hari' => $statistik['maks_keluar']
                ],
                'perhitungan' => [
                    'safety_stock' => "({$statistik['maks_keluar']} - {$statistik['rata_rata']}) × {$bahanBaku->lead_time} = {$parameters['safety_stock']}",
                    'min_stock' => "({$statistik['rata_rata']} × {$bahanBaku->lead_time}) + {$parameters['safety_stock']} = {$parameters['min']}",
                    'max_stock' => "2 × ({$statistik['rata_rata']} × {$bahanBaku->lead_time}) + {$parameters['safety_stock']} = {$parameters['max']}",
                    'rop' => "{$parameters['max']} - {$parameters['min']} = {$parameters['rop']}"
                ],
                'rekomendasi_pembelian' => [
                    'perlu_pembelian' => $bahanBaku->isPerluPembelian(),
                    'jumlah_rekomendasi' => $bahanBaku->jumlahPemesananRekomendasi(),
                    'alasan' => $bahanBaku->isPerluPembelian() ?
                        "Stok ({$bahanBaku->stok}) ≤ Min ({$parameters['min']})" :
                        "Stok ({$bahanBaku->stok}) > Min ({$parameters['min']})"
                ],
                'hasil' => $parameters
            ];

            return response()->json([
                'status' => 'success',
                'data' => $calculationDetail
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
