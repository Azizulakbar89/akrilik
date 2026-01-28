<?php

namespace App\Http\Controllers\Owner;

use DB;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\BahanBaku;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use App\Models\DetailPembelian;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class PembelianOwnerController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembelian::with(['supplier', 'detailPembelian.bahanBaku'])
            ->orderBy('created_at', 'desc');

        $searchBahanBaku = $request->get('search_bahan_baku');
        $bahanBakuList = BahanBaku::orderBy('nama')->get();

        if ($searchBahanBaku) {
            $query->whereHas('detailPembelian.bahanBaku', function ($q) use ($searchBahanBaku) {
                $q->where('nama', 'like', '%' . $searchBahanBaku . '%');
            });
        }

        $pembelian = $query->get();
        $supplier = Supplier::all();
        $bahanBaku = BahanBaku::all();

        // Rekomendasi ROP biasa (stok <= min)
        $rekomendasi = $this->getRekomendasiDataLocal();

        // Stok tidak aman (stok <= safety stock)
        $stokTidakAman = BahanBaku::whereColumn('stok', '<=', 'safety_stock')
            ->where('safety_stock', '>', 0)
            ->get();

        // Gabungkan semua bahan baku yang perlu dibeli untuk pembelian cepat
        $bahanBakuPerluBeli = BahanBaku::where(function ($query) {
            $query->whereColumn('stok', '<=', 'min')
                ->orWhereColumn('stok', '<=', 'safety_stock');
        })
            ->where('min', '>', 0)
            ->get();

        $totalRekomendasi = $rekomendasi->sum('total_nilai');
        $leadTimeStats = $this->calculateLeadTimeStats($bahanBaku);

        return view('owner.pembelian.index', compact(
            'pembelian',
            'supplier',
            'bahanBaku',
            'rekomendasi',
            'stokTidakAman',
            'bahanBakuPerluBeli',
            'totalRekomendasi',
            'leadTimeStats',
            'searchBahanBaku',
            'bahanBakuList'
        ));
    }

    private function getRekomendasiDataLocal()
    {
        return BahanBaku::whereColumn('stok', '<=', 'min')
            ->where('min', '>', 0)
            ->get()
            ->map(function ($bahan) {
                // Cek apakah bahan baku perlu pembelian
                $perluPembelian = $bahan->stok <= $bahan->min;

                if (!$perluPembelian) {
                    return null;
                }

                // Hitung jumlah rekomendasi berdasarkan ROP
                $jumlahRekomendasi = 0;
                if ($bahan->rop > 0) {
                    $jumlahRekomendasi = $bahan->rop;
                } elseif ($bahan->max > 0 && $bahan->min > 0) {
                    $jumlahRekomendasi = max(1, $bahan->max - $bahan->stok);
                } else {
                    $jumlahRekomendasi = max(1, $bahan->min - $bahan->stok + 10);
                }

                if ($jumlahRekomendasi <= 0) {
                    return null;
                }

                $hargaBeli = $bahan->harga_beli ?: 0;
                $totalNilai = $jumlahRekomendasi * $hargaBeli;

                return [
                    'bahan_baku_id' => $bahan->id,
                    'nama' => $bahan->nama,
                    'stok_sekarang' => $bahan->stok,
                    'min' => $bahan->min,
                    'max' => $bahan->max,
                    'rop' => $bahan->rop ?? 0,
                    'safety_stock' => $bahan->safety_stock ?? 0,
                    'jumlah_rekomendasi' => $jumlahRekomendasi,
                    'harga_beli' => $hargaBeli,
                    'total_nilai' => $totalNilai,
                    'satuan' => $bahan->satuan,
                    'lead_time' => $bahan->lead_time ?? 1,
                    'lead_time_max' => $bahan->lead_time_max ?? 1,
                    'perlu_pembelian' => $perluPembelian,
                    'stok_tidak_aman' => $bahan->stok <= $bahan->safety_stock
                ];
            })
            ->filter(function ($item) {
                return $item !== null &&
                    isset($item['jumlah_rekomendasi']) &&
                    $item['jumlah_rekomendasi'] > 0;
            })
            ->values();
    }

    private function calculateLeadTimeStats($bahanBaku)
    {
        $leadTimes = [];
        $maxLeadTimes = [];

        foreach ($bahanBaku as $bahan) {
            if ($bahan->lead_time < 1) {
                $leadTimes[] = 1;
            } else {
                $days = floor($bahan->lead_time);
                $hours = ($bahan->lead_time - $days) * 24;
                $leadTimes[] = $hours > 0 ? $days + 1 : $days;
            }

            if ($bahan->lead_time_max < 1) {
                $maxLeadTimes[] = 1;
            } else {
                $days = floor($bahan->lead_time_max);
                $hours = ($bahan->lead_time_max - $days) * 24;
                $maxLeadTimes[] = $hours > 0 ? $days + 1 : $days;
            }
        }

        return [
            'average' => !empty($leadTimes) ? round(array_sum($leadTimes) / count($leadTimes), 1) : 1,
            'max' => !empty($maxLeadTimes) ? max($maxLeadTimes) : 1,
            'min' => !empty($leadTimes) ? min($leadTimes) : 1,
            'count' => $bahanBaku->count(),
            'max_average' => !empty($maxLeadTimes) ? round(array_sum($maxLeadTimes) / count($maxLeadTimes), 1) : 1,
            'lead_times' => $leadTimes,
            'max_lead_times' => $maxLeadTimes
        ];
    }

    public function create()
    {
        $supplier = Supplier::all();
        $bahanBaku = BahanBaku::all();

        $rekomendasi = $this->getRekomendasiDataLocal();

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

            // Generate kode pembelian
            $kodePembelian = 'PB-' . date('Ymd') . '-' . str_pad(Pembelian::count() + 1, 4, '0', STR_PAD_LEFT);

            $pembelian = Pembelian::create([
                'kode_pembelian' => $kodePembelian,
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

            if ($request->ajax()) {
                return response()->json(['success' => 'Pembelian berhasil disimpan dan menunggu persetujuan']);
            }

            return redirect()->route('owner.pembelian.index')
                ->with('success', 'Pembelian berhasil disimpan dan menunggu persetujuan');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error storing pembelian: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Store Pembelian Cepat untuk semua bahan baku yang perlu dibeli
     */
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
                $bahanBaku = BahanBaku::find($item['bahan_baku_id']);

                if (!$bahanBaku) {
                    throw new \Exception("Bahan baku dengan ID {$item['bahan_baku_id']} tidak ditemukan");
                }

                // Validasi apakah bahan baku memang perlu dibeli
                $perluBeli = $bahanBaku->stok <= $bahanBaku->min ||
                    $bahanBaku->stok <= $bahanBaku->safety_stock;

                if (!$perluBeli) {
                    continue;
                }

                // Validasi jumlah minimal
                if ($item['jumlah'] < 1) {
                    throw new \Exception("Jumlah untuk {$bahanBaku->nama} harus minimal 1");
                }

                $subTotal = $item['jumlah'] * $item['harga'];

                $items[] = [
                    'bahan_baku_id' => $item['bahan_baku_id'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga'],
                    'sub_total' => $subTotal,
                    'bahan_baku' => $bahanBaku
                ];

                $total += $subTotal;
            }

            if (empty($items)) {
                DB::rollback();

                if ($request->ajax()) {
                    return response()->json(['error' => 'Tidak ada bahan baku yang valid untuk dibeli. Semua bahan baku sudah dalam kondisi aman.'], 400);
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Tidak ada bahan baku yang valid untuk dibeli. Semua bahan baku sudah dalam kondisi aman.']);
            }

            // Generate kode pembelian
            $kodePembelian = 'PB-' . date('Ymd') . '-' . str_pad(Pembelian::count() + 1, 4, '0', STR_PAD_LEFT);

            // Buat pembelian
            $pembelian = Pembelian::create([
                'kode_pembelian' => $kodePembelian,
                'supplier_id' => $request->supplier_id,
                'total' => $total,
                'tanggal' => $request->tanggal,
                'status' => 'menunggu_persetujuan',
                'catatan' => 'PEMBELIAN CEPAT ROP - ' . count($items) . ' bahan baku yang perlu dibeli'
            ]);

            // Simpan detail pembelian
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

            // Log keberhasilan
            $bahanBakuNames = collect($items)->pluck('bahan_baku.nama')->implode(', ');
            Log::info('Pembelian cepat berhasil dibuat', [
                'pembelian_id' => $pembelian->id,
                'supplier_id' => $request->supplier_id,
                'total_items' => count($items),
                'total_pembelian' => $total,
                'bahan_baku' => $bahanBakuNames
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => 'Pembelian cepat berhasil dibuat!',
                    'pembelian_id' => $pembelian->id,
                    'total_items' => count($items),
                    'total_pembelian' => $total,
                    'redirect' => route('owner.pembelian.index'),
                    'message' => count($items) . ' bahan baku berhasil ditambahkan ke pembelian'
                ]);
            }

            return redirect()->route('owner.pembelian.index')
                ->with('success', 'Pembelian cepat berhasil dibuat! ' . count($items) . ' bahan baku berhasil ditambahkan ke pembelian');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error storing pembelian cepat: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Store rekomendasi ROP (untuk tombol "Gunakan Rekomendasi ROP")
     */
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

                // Cek apakah bahan baku perlu pembelian
                $perluPembelian = $bahanBaku->stok <= $bahanBaku->min;

                if ($perluPembelian) {
                    // Hitung jumlah berdasarkan ROP
                    $jumlah = 0;
                    if ($bahanBaku->rop > 0) {
                        $jumlah = $bahanBaku->rop;
                    } elseif ($bahanBaku->max > 0) {
                        $jumlah = max(1, $bahanBaku->max - $bahanBaku->stok);
                    } else {
                        $jumlah = max(1, $bahanBaku->min - $bahanBaku->stok + 10);
                    }

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

            // Generate kode pembelian
            $kodePembelian = 'PB-' . date('Ymd') . '-' . str_pad(Pembelian::count() + 1, 4, '0', STR_PAD_LEFT);

            $pembelian = Pembelian::create([
                'kode_pembelian' => $kodePembelian,
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
            Log::error('Error storing rekomendasi pembelian: ' . $e->getMessage());
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
            Log::error('Error updating pembelian: ' . $e->getMessage());
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
            Log::error('Error deleting pembelian: ' . $e->getMessage());
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

            DB::commit();
            return response()->json(['success' => 'Pembelian berhasil disetujui']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error approving pembelian: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function receive($id)
    {
        DB::beginTransaction();
        try {
            $pembelian = Pembelian::with('detailPembelian.bahanBaku')->findOrFail($id);

            if ($pembelian->status !== 'completed' || $pembelian->waktu_penerimaan) {
                return response()->json(['error' => 'Pembelian tidak dapat diterima. Pastikan status sudah disetujui dan belum diterima.'], 403);
            }

            // Set waktu penerimaan sekarang
            $pembelian->waktu_penerimaan = now();

            // Hitung lead time actual dari tanggal pesan sampai tanggal terima
            $leadTimeDays = $this->calculateActualLeadTime($pembelian);

            // Update status
            $pembelian->status = 'diterima';
            $pembelian->save();

            // Tambah stok bahan baku dan update lead time serta hitung ulang parameter stok
            $updates = [];
            foreach ($pembelian->detailPembelian as $detail) {
                $bahanBaku = $detail->bahanBaku;
                if ($bahanBaku) {
                    // Simpan stok sebelum
                    $stokSebelum = $bahanBaku->stok;

                    // Update lead time berdasarkan lead time actual dari pembelian ini
                    $leadTimeUpdate = $bahanBaku->updateLeadTimeWithActual($leadTimeDays);

                    // Tambah stok
                    $bahanBaku->stok += $detail->jumlah;

                    // Hitung ulang parameter stok (safety stock, min, max, rop)
                    $parameterBaru = $bahanBaku->hitungDanUpdateParameterStok();

                    $bahanBaku->save();

                    $updates[] = [
                        'bahan_baku' => $bahanBaku->nama,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $bahanBaku->stok,
                        'jumlah_ditambahkan' => $detail->jumlah,
                        'lead_time_sebelum' => $leadTimeUpdate['old_average'] . ' hari',
                        'lead_time_sesudah' => $leadTimeUpdate['new_average'] . ' hari',
                        'lead_time_max_sebelum' => $leadTimeUpdate['old_max'] . ' hari',
                        'lead_time_max_sesudah' => $leadTimeUpdate['new_max'] . ' hari',
                        'safety_stock_sebelum' => $parameterBaru['safety_stock_old'],
                        'safety_stock_sesudah' => $parameterBaru['safety_stock'],
                        'min_sebelum' => $parameterBaru['min_old'],
                        'min_sesudah' => $parameterBaru['min'],
                        'max_sebelum' => $parameterBaru['max_old'],
                        'max_sesudah' => $parameterBaru['max'],
                        'rop_sebelum' => $parameterBaru['rop_old'],
                        'rop_sesudah' => $parameterBaru['rop']
                    ];
                }
            }

            DB::commit();

            Log::info('Pembelian diterima dengan perhitungan parameter stok otomatis', [
                'pembelian_id' => $id,
                'lead_time_actual' => $leadTimeDays,
                'updates' => $updates
            ]);

            return response()->json([
                'success' => 'Pembelian berhasil diterima, stok diperbarui, dan parameter stok dihitung ulang',
                'lead_time_actual' => $leadTimeDays,
                'lead_time_formatted' => $leadTimeDays . ' hari',
                'updates' => $updates,
                'message' => 'Parameter stok (safety stock, min, max, rop) telah dihitung ulang berdasarkan lead time terbaru'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error receiving pembelian: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hitung lead time actual dari tanggal pesan sampai tanggal terima
     */
    private function calculateActualLeadTime($pembelian)
    {
        $tanggalPesan = Carbon::parse($pembelian->tanggal);
        $tanggalTerima = Carbon::parse(now());

        // Hitung selisih dalam jam
        $selisihJam = $tanggalPesan->diffInHours($tanggalTerima);

        // Konversi ke hari dengan pembulatan ke atas
        $leadTimeDays = ceil($selisihJam / 24);

        // Minimal 1 hari
        return max(1, $leadTimeDays);
    }

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
            Log::error('Error rejecting pembelian: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get data untuk rekomendasi ROP (untuk tombol "Gunakan Rekomendasi ROP")
     */
    public function getRekomendasiData()
    {
        try {
            $rekomendasi = $this->getRekomendasiDataLocal();

            $totalRekomendasi = $rekomendasi->sum('total_nilai');

            return response()->json([
                'success' => true,
                'rekomendasi' => $rekomendasi,
                'total_rekomendasi' => $totalRekomendasi,
                'jumlah_item' => $rekomendasi->count(),
                'message' => $rekomendasi->count() > 0 ?
                    'Data rekomendasi berhasil dimuat' :
                    'Tidak ada bahan baku yang memerlukan pembelian'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting rekomendasi data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'message' => 'Gagal memuat rekomendasi ROP'
            ], 500);
        }
    }

    /**
     * Get data untuk Pembelian Cepat (semua bahan baku yang perlu dibeli)
     */
    public function getPembelianCepatData()
    {
        try {
            // Ambil semua bahan baku dengan kondisi yang sederhana
            $bahanBaku = BahanBaku::where('min', '>', 0)
                ->whereNotNull('harga_beli')
                ->where('harga_beli', '>', 0)
                ->orderBy('nama')
                ->get();

            $data = [];
            $totalNilai = 0;
            $totalItems = 0;

            foreach ($bahanBaku as $bahan) {
                // Cek apakah perlu pembelian
                $perluPembelian = false;
                $statusStok = 'Aman';

                // Cek 1: Stok <= Min (Kritis)
                if ($bahan->stok <= $bahan->min) {
                    $perluPembelian = true;
                    $statusStok = 'Kritis';
                }
                // Cek 2: Stok <= Safety Stock (jika safety stock > 0)
                elseif ($bahan->safety_stock > 0 && $bahan->stok <= $bahan->safety_stock) {
                    $perluPembelian = true;
                    $statusStok = 'Tidak Aman';
                }

                // Jika tidak perlu pembelian, skip
                if (!$perluPembelian) {
                    continue;
                }

                // Hitung jumlah rekomendasi
                $jumlahRekomendasi = 0;

                // Prioritaskan ROP jika ada
                if ($bahan->rop > 0) {
                    $jumlahRekomendasi = $bahan->rop;
                }
                // Jika ada max, hitung dari max - stok
                elseif ($bahan->max > 0) {
                    $kebutuhan = $bahan->max - $bahan->stok;
                    $jumlahRekomendasi = max(1, $kebutuhan);
                }
                // Default: beli sampai min + buffer
                else {
                    $kebutuhan = $bahan->min - $bahan->stok;
                    $jumlahRekomendasi = max(1, $kebutuhan + 10);
                }

                // Validasi: jangan melebihi max jika ada
                if ($bahan->max > 0) {
                    $jumlahRekomendasi = min($jumlahRekomendasi, $bahan->max);
                }

                // Pastikan minimal 1
                $jumlahRekomendasi = max(1, $jumlahRekomendasi);

                $hargaBeli = $bahan->harga_beli ?: 0;
                $totalNilaiItem = $jumlahRekomendasi * $hargaBeli;

                $data[] = [
                    'bahan_baku_id' => $bahan->id,
                    'nama' => $bahan->nama,
                    'stok' => $bahan->stok,
                    'min' => $bahan->min,
                    'max' => $bahan->max ?? 0,
                    'safety_stock' => $bahan->safety_stock ?? 0,
                    'rop' => $bahan->rop ?? 0,
                    'jumlah_rekomendasi' => $jumlahRekomendasi,
                    'harga_beli' => $hargaBeli,
                    'satuan' => $bahan->satuan ?? 'pcs',
                    'total_nilai' => $totalNilaiItem,
                    'status_stok' => $statusStok,
                    'perlu_pembelian' => true,
                    'lead_time' => $bahan->lead_time ?? 1,
                    'lead_time_max' => $bahan->lead_time_max ?? $bahan->lead_time ?? 1,
                    'stok_kurang' => max(0, $bahan->min - $bahan->stok),
                    'kebutuhan_untuk_stok_aman' => $bahan->safety_stock > 0 ? max(0, $bahan->safety_stock - $bahan->stok) : 0
                ];

                $totalNilai += $totalNilaiItem;
                $totalItems++;
            }

            if ($totalItems === 0) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'total_items' => 0,
                    'total_nilai' => 0,
                    'message' => 'Semua bahan baku dalam kondisi aman. Tidak ada yang perlu dibeli.',
                    'all_safe' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'total_items' => $totalItems,
                'total_nilai' => $totalNilai,
                'message' => 'Ditemukan ' . $totalItems . ' bahan baku yang perlu dibeli',
                'all_safe' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting pembelian cepat data: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan saat memuat data',
                'message' => 'Gagal memuat data pembelian cepat. Error: ' . $e->getMessage(),
                'data' => [],
                'all_safe' => false
            ], 500);
        }
    }

    public function laporan(Request $request)
    {
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date',
            'status' => 'nullable|in:semua,completed,menunggu_persetujuan,ditolak,diterima'
        ]);

        $query = Pembelian::with(['supplier', 'detailPembelian.bahanBaku'])
            ->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir])
            ->orderBy('tanggal', 'asc');

        if ($request->status && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        $pembelian = $query->get();
        $totalPembelian = $pembelian->sum('total');

        // Analisis supplier terbanyak
        $supplierTerbanyak = Pembelian::select(
            'supplier_id',
            DB::raw('COUNT(*) as jumlah_transaksi'),
            DB::raw('SUM(total) as total_pembelian')
        )
            ->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir])
            ->groupBy('supplier_id')
            ->orderByDesc('jumlah_transaksi')
            ->limit(5)
            ->get();

        // Analisis bahan baku terbanyak
        $bahanBakuTerbanyak = DetailPembelian::select(
            'bahan_baku_id',
            DB::raw('SUM(jumlah) as total_dibeli'),
            DB::raw('SUM(sub_total) as total_pembelian')
        )
            ->whereHas('pembelian', function ($q) use ($request) {
                $q->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir]);
            })
            ->groupBy('bahan_baku_id')
            ->orderByDesc('total_dibeli')
            ->limit(10)
            ->get();

        $data = [
            'pembelian' => $pembelian,
            'totalPembelian' => $totalPembelian,
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
            'status' => $request->status,
            'request' => $request,
            'supplierTerbanyak' => $supplierTerbanyak,
            'bahanBakuTerbanyak' => $bahanBakuTerbanyak
        ];

        // Jika request dari modal print, return view PDF
        if ($request->ajax() || $request->wantsJson()) {
            return view('owner.pembelian.laporan_pdf', $data);
        }

        return view('owner.pembelian.laporan', $data);
    }

    /**
     * Print Laporan Pembelian (POST request)
     */
    public function printLaporan(Request $request)
    {
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date',
            'status' => 'nullable|in:semua,completed,menunggu_persetujuan,ditolak,diterima'
        ]);

        $query = Pembelian::with(['supplier', 'detailPembelian.bahanBaku'])
            ->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir])
            ->orderBy('tanggal', 'asc');

        if ($request->status && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        $pembelian = $query->get();
        $totalPembelian = $pembelian->sum('total');

        // Analisis supplier terbanyak
        $supplierTerbanyak = Pembelian::select(
            'supplier_id',
            DB::raw('COUNT(*) as jumlah_transaksi'),
            DB::raw('SUM(total) as total_pembelian')
        )
            ->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir])
            ->groupBy('supplier_id')
            ->orderByDesc('jumlah_transaksi')
            ->limit(5)
            ->get();

        // Analisis bahan baku terbanyak
        $bahanBakuTerbanyak = DetailPembelian::select(
            'bahan_baku_id',
            DB::raw('SUM(jumlah) as total_dibeli'),
            DB::raw('SUM(sub_total) as total_pembelian')
        )
            ->whereHas('pembelian', function ($q) use ($request) {
                $q->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir]);
            })
            ->groupBy('bahan_baku_id')
            ->orderByDesc('total_dibeli')
            ->limit(10)
            ->get();

        $data = [
            'pembelian' => $pembelian,
            'totalPembelian' => $totalPembelian,
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
            'status' => $request->status,
            'supplierTerbanyak' => $supplierTerbanyak,
            'bahanBakuTerbanyak' => $bahanBakuTerbanyak
        ];

        if ($request->ajax()) {
            return view('owner.pembelian.laporan_print', $data);
        }

        return view('owner.pembelian.laporan_print', $data);
    }

    /**
     * Tampilkan form pembelian cepat
     */
    public function showPembelianCepat()
    {
        $supplier = Supplier::all();
        $bahanBaku = BahanBaku::all();

        // Ambil data untuk pembelian cepat
        $pembelianCepatData = $this->getPembelianCepatDataForView();

        return view('owner.pembelian.pembelian_cepat', compact(
            'supplier',
            'bahanBaku',
            'pembelianCepatData'
        ));
    }

    /**
     * Get data pembelian cepat untuk view
     */
    private function getPembelianCepatDataForView()
    {
        try {
            $bahanBakuPerluBeli = BahanBaku::where(function ($query) {
                $query->whereColumn('stok', '<=', 'min')
                    ->orWhere(function ($q) {
                        $q->whereColumn('stok', '<=', 'safety_stock')
                            ->where('safety_stock', '>', 0);
                    });
            })
                ->where('min', '>', 0)
                ->whereNotNull('harga_beli')
                ->get();

            $data = [];
            $totalNilai = 0;

            foreach ($bahanBakuPerluBeli as $bahan) {
                $perluPembelian = ($bahan->stok <= $bahan->min) ||
                    ($bahan->safety_stock > 0 && $bahan->stok <= $bahan->safety_stock);

                if (!$perluPembelian) {
                    continue;
                }

                // Hitung jumlah rekomendasi
                $jumlahRekomendasi = 0;
                if ($bahan->rop > 0) {
                    $jumlahRekomendasi = $bahan->rop;
                } elseif ($bahan->max > 0) {
                    $kebutuhan = $bahan->max - $bahan->stok;
                    $jumlahRekomendasi = max(1, $kebutuhan);
                } elseif ($bahan->min > 0) {
                    $kebutuhan = $bahan->min - $bahan->stok;
                    $jumlahRekomendasi = max(1, $kebutuhan + 10);
                } else {
                    $jumlahRekomendasi = 1;
                }

                $jumlahRekomendasi = max(1, $jumlahRekomendasi);
                if ($bahan->max > 0) {
                    $jumlahRekomendasi = min($jumlahRekomendasi, $bahan->max);
                }

                $hargaBeli = $bahan->harga_beli > 0 ? $bahan->harga_beli : 0;
                $totalNilaiItem = $jumlahRekomendasi * $hargaBeli;

                $statusStok = 'Aman';
                if ($bahan->stok <= $bahan->min) {
                    $statusStok = 'Kritis';
                } elseif ($bahan->safety_stock > 0 && $bahan->stok <= $bahan->safety_stock) {
                    $statusStok = 'Tidak Aman';
                }

                $data[] = [
                    'bahan_baku_id' => $bahan->id,
                    'nama' => $bahan->nama,
                    'stok' => $bahan->stok,
                    'min' => $bahan->min,
                    'max' => $bahan->max ?? 0,
                    'safety_stock' => $bahan->safety_stock ?? 0,
                    'rop' => $bahan->rop ?? 0,
                    'jumlah_rekomendasi' => $jumlahRekomendasi,
                    'harga_beli' => $hargaBeli,
                    'satuan' => $bahan->satuan ?? 'pcs',
                    'total_nilai' => $totalNilaiItem,
                    'status_stok' => $statusStok,
                    'perlu_pembelian' => true,
                    'lead_time' => $bahan->lead_time ?? 1
                ];

                $totalNilai += $totalNilaiItem;
            }

            return [
                'data' => $data,
                'total_nilai' => $totalNilai,
                'total_items' => count($data),
                'has_data' => count($data) > 0
            ];
        } catch (\Exception $e) {
            Log::error('Error in getPembelianCepatDataForView: ' . $e->getMessage());
            return [
                'data' => [],
                'total_nilai' => 0,
                'total_items' => 0,
                'has_data' => false
            ];
        }
    }

    /**
     * Export PDF untuk laporan
     */
    public function exportPDF(Request $request)
    {
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date',
            'status' => 'nullable|in:semua,completed,menunggu_persetujuan,ditolak,diterima'
        ]);

        $query = Pembelian::with(['supplier', 'detailPembelian.bahanBaku'])
            ->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir])
            ->orderBy('tanggal', 'asc');

        if ($request->status && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        $pembelian = $query->get();
        $totalPembelian = $pembelian->sum('total');

        // Analisis supplier terbanyak
        $supplierTerbanyak = Pembelian::select(
            'supplier_id',
            DB::raw('COUNT(*) as jumlah_transaksi'),
            DB::raw('SUM(total) as total_pembelian')
        )
            ->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir])
            ->groupBy('supplier_id')
            ->orderByDesc('jumlah_transaksi')
            ->limit(5)
            ->get();

        // Analisis bahan baku terbanyak
        $bahanBakuTerbanyak = DetailPembelian::select(
            'bahan_baku_id',
            DB::raw('SUM(jumlah) as total_dibeli'),
            DB::raw('SUM(sub_total) as total_pembelian')
        )
            ->whereHas('pembelian', function ($q) use ($request) {
                $q->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir]);
            })
            ->groupBy('bahan_baku_id')
            ->orderByDesc('total_dibeli')
            ->limit(10)
            ->get();

        $data = [
            'pembelian' => $pembelian,
            'totalPembelian' => $totalPembelian,
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
            'status' => $request->status,
            'supplierTerbanyak' => $supplierTerbanyak,
            'bahanBakuTerbanyak' => $bahanBakuTerbanyak
        ];

        $pdf = \PDF::loadView('owner.pembelian.laporan_pdf', $data);

        return $pdf->download('laporan-pembelian-' . date('Y-m-d') . '.pdf');
    }
}
