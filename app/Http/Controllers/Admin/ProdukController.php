<?php

namespace App\Http\Controllers\Admin;

use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\KomposisiBahanBaku;
use App\Models\PenggunaanBahanBaku;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Produk::with('komposisi.bahanBaku')->get();
        $bahanBaku = BahanBaku::all();
        return view('admin.produk.index', compact('produk', 'bahanBaku'));
    }

    public function create()
    {
        $bahanBaku = BahanBaku::all();
        return view('admin.produk.create', compact('bahanBaku'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'komposisi' => 'required|array|min:1',
            'komposisi.*.bahan_baku_id' => 'required|exists:bahan_baku,id',
            'komposisi.*.jumlah' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Terjadi kesalahan validasi'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $request->only(['nama', 'satuan', 'harga', 'stok']);

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('produk', $filename, 'public');
                $data['foto'] = $path;
            }

            // Validasi stok bahan baku untuk produksi awal
            foreach ($request->komposisi as $komp) {
                $bahanBaku = BahanBaku::find($komp['bahan_baku_id']);
                $kebutuhan = $komp['jumlah'] * $request->stok;

                if ($bahanBaku->stok < $kebutuhan) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Stok bahan baku ' . $bahanBaku->nama . ' tidak mencukupi. Stok tersedia: ' . $bahanBaku->stok . ', Kebutuhan: ' . $kebutuhan
                    ], 422);
                }
            }

            $produk = Produk::create($data);

            // Simpan komposisi dan kurangi stok bahan baku
            foreach ($request->komposisi as $komp) {
                KomposisiBahanBaku::create([
                    'produk_id' => $produk->id,
                    'bahan_baku_id' => $komp['bahan_baku_id'],
                    'jumlah' => $komp['jumlah']
                ]);

                $bahanBaku = BahanBaku::find($komp['bahan_baku_id']);
                $jumlahDigunakan = $komp['jumlah'] * $request->stok;

                $bahanBaku->stok -= $jumlahDigunakan;
                $bahanBaku->save();

                // Catat penggunaan bahan baku untuk produksi awal
                PenggunaanBahanBaku::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'jumlah' => $jumlahDigunakan,
                    'tanggal' => now(),
                    'keterangan' => 'Produksi awal produk: ' . $produk->nama
                ]);

                $bahanBaku->updateParameterStok();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Produk berhasil ditambahkan dan stok bahan baku telah dikurangi'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $produk = Produk::with('komposisi.bahanBaku')->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $produk
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }

    public function edit($id)
    {
        $produk = Produk::with('komposisi.bahanBaku')->findOrFail($id);
        $bahanBaku = BahanBaku::all();
        return view('admin.produk.edit', compact('produk', 'bahanBaku'));
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'komposisi' => 'required|array|min:1',
            'komposisi.*.bahan_baku_id' => 'required|exists:bahan_baku,id',
            'komposisi.*.jumlah' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Terjadi kesalahan validasi'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $request->only(['nama', 'satuan', 'harga', 'stok']);

            if ($request->hasFile('foto')) {
                if ($produk->foto) {
                    Storage::disk('public')->delete($produk->foto);
                }

                $file = $request->file('foto');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('produk', $filename, 'public');
                $data['foto'] = $path;
            }

            $stokDifference = $request->stok - $produk->stok;

            // Jika ada penambahan stok, validasi bahan baku
            if ($stokDifference > 0) {
                foreach ($request->komposisi as $komp) {
                    $bahanBaku = BahanBaku::find($komp['bahan_baku_id']);
                    $kebutuhan = $komp['jumlah'] * $stokDifference;

                    if ($bahanBaku->stok < $kebutuhan) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Stok bahan baku ' . $bahanBaku->nama . ' tidak mencukupi untuk penambahan stok. Stok tersedia: ' . $bahanBaku->stok . ', Kebutuhan: ' . $kebutuhan
                        ], 422);
                    }
                }
            }

            $produk->update($data);

            // Hapus komposisi lama dan buat yang baru
            $produk->komposisi()->delete();

            foreach ($request->komposisi as $komp) {
                KomposisiBahanBaku::create([
                    'produk_id' => $produk->id,
                    'bahan_baku_id' => $komp['bahan_baku_id'],
                    'jumlah' => $komp['jumlah']
                ]);

                // Jika ada perubahan stok, sesuaikan stok bahan baku
                if ($stokDifference != 0) {
                    $bahanBaku = BahanBaku::find($komp['bahan_baku_id']);
                    $jumlahDigunakan = $komp['jumlah'] * $stokDifference;

                    $bahanBaku->stok -= $jumlahDigunakan;
                    $bahanBaku->save();

                    // Catat penggunaan bahan baku
                    if ($jumlahDigunakan > 0) {
                        PenggunaanBahanBaku::create([
                            'bahan_baku_id' => $bahanBaku->id,
                            'jumlah' => $jumlahDigunakan,
                            'tanggal' => now(),
                            'keterangan' => 'Restok produk: ' . $produk->nama
                        ]);
                    } else {
                        // Jika pengurangan stok, catat sebagai pengembalian
                        PenggunaanBahanBaku::create([
                            'bahan_baku_id' => $bahanBaku->id,
                            'jumlah' => $jumlahDigunakan,
                            'tanggal' => now(),
                            'keterangan' => 'Pengurangan stok produk: ' . $produk->nama
                        ]);
                    }

                    // Update parameter stok bahan baku
                    $bahanBaku->updateParameterStok();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Produk berhasil diupdate dan stok bahan baku telah disesuaikan'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $produk = Produk::findOrFail($id);

            // Kembalikan stok bahan baku
            foreach ($produk->komposisi as $komposisi) {
                $bahanBaku = $komposisi->bahanBaku;
                $kebutuhan = $komposisi->jumlah * $produk->stok;

                $bahanBaku->stok += $kebutuhan;
                $bahanBaku->save();

                // Catat pengembalian bahan baku
                PenggunaanBahanBaku::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'jumlah' => -$kebutuhan,
                    'tanggal' => now(),
                    'keterangan' => 'Penghapusan produk: ' . $produk->nama
                ]);

                // Update parameter stok bahan baku
                $bahanBaku->updateParameterStok();
            }

            if ($produk->foto) {
                Storage::disk('public')->delete($produk->foto);
            }

            $produk->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Produk berhasil dihapus dan stok bahan baku telah dikembalikan'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function komposisi()
    {
        $produk = Produk::with('komposisi.bahanBaku')->get();
        return view('admin.komposisi-produk.index', compact('produk'));
    }
}
