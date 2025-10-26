<?php

namespace App\Http\Controllers\Admin;

use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\PenggunaanBahanBaku;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = Penjualan::with('detailPenjualan')->latest()->get();
        $produk = Produk::all();
        $bahanBaku = BahanBaku::all();

        return view('admin.penjualan.index', compact('penjualan', 'produk', 'bahanBaku'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_customer' => 'required|string|max:255',
            'bayar' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.jenis_item' => 'required|in:produk,bahan_baku',
            'items.*.item_id' => 'required|integer',
            'items.*.jumlah' => 'required|integer|min:1'
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

            foreach ($request->items as $item) {
                if ($item['jenis_item'] == 'produk') {
                    $produk = Produk::findOrFail($item['item_id']);
                    if (!$produk->bisaDiproduksi($item['jumlah'])) {
                        throw new \Exception("Bahan baku tidak mencukupi untuk memproduksi produk: {$produk->nama}");
                    }
                } elseif ($item['jenis_item'] == 'bahan_baku') {
                    $bahanBaku = BahanBaku::findOrFail($item['item_id']);
                    if ($bahanBaku->stok < $item['jumlah']) {
                        throw new \Exception("Stok bahan baku {$bahanBaku->nama} tidak mencukupi");
                    }
                }
            }

            $total = 0;
            foreach ($request->items as $item) {
                if ($item['jenis_item'] == 'produk') {
                    $produk = Produk::find($item['item_id']);
                    $total += $produk->harga * $item['jumlah'];
                } elseif ($item['jenis_item'] == 'bahan_baku') {
                    $bahanBaku = BahanBaku::find($item['item_id']);
                    $total += $bahanBaku->harga_jual * $item['jumlah'];
                }
            }

            if ($request->bayar < $total) {
                throw new \Exception("Jumlah pembayaran kurang dari total");
            }

            $kembalian = $request->bayar - $total;

            $penjualan = Penjualan::create([
                'nama_customer' => $request->nama_customer,
                'total' => $total,
                'bayar' => $request->bayar,
                'kembalian' => $kembalian,
                'tanggal' => now()
            ]);

            foreach ($request->items as $item) {
                if ($item['jenis_item'] == 'produk') {
                    $produk = Produk::find($item['item_id']);

                    $detail = DetailPenjualan::create([
                        'penjualan_id' => $penjualan->id,
                        'produk_id' => $item['item_id'],
                        'nama_produk' => $produk->nama,
                        'jenis_item' => 'produk',
                        'jumlah' => $item['jumlah'],
                        'harga_sat' => $produk->harga,
                        'sub_total' => $produk->harga * $item['jumlah']
                    ]);

                    $detail->prosesPenjualan();
                } elseif ($item['jenis_item'] == 'bahan_baku') {
                    $bahanBaku = BahanBaku::find($item['item_id']);

                    $detail = DetailPenjualan::create([
                        'penjualan_id' => $penjualan->id,
                        'bahan_baku_id' => $item['item_id'],
                        'nama_produk' => $bahanBaku->nama,
                        'jenis_item' => 'bahan_baku',
                        'jumlah' => $item['jumlah'],
                        'harga_sat' => $bahanBaku->harga_jual,
                        'sub_total' => $bahanBaku->harga_jual * $item['jumlah']
                    ]);

                    $bahanBaku->stok -= $item['jumlah'];
                    $bahanBaku->save();

                    PenggunaanBahanBaku::create([
                        'bahan_baku_id' => $bahanBaku->id,
                        'jumlah' => $item['jumlah'],
                        'tanggal' => now(),
                        'keterangan' => 'Penjualan langsung: ' . $bahanBaku->nama
                    ]);

                    $bahanBaku->updateParameterStok();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penjualan berhasil disimpan',
                'data' => [
                    'id' => $penjualan->id,
                    'kode_penjualan' => $penjualan->kode_penjualan,
                    'total' => $total,
                    'bayar' => $request->bayar,
                    'kembalian' => $kembalian
                ]
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
            $penjualan = Penjualan::with('detailPenjualan.produk', 'detailPenjualan.bahanBaku')->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $penjualan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $penjualan = Penjualan::with('detailPenjualan')->findOrFail($id);

            foreach ($penjualan->detailPenjualan as $detail) {
                $detail->batalkanPenjualan();
            }

            $penjualan->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penjualan berhasil dihapus dan stok telah dikembalikan'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printNota($id)
    {
        $penjualan = Penjualan::with('detailPenjualan.produk', 'detailPenjualan.bahanBaku')->findOrFail($id);
        return view('admin.penjualan.nota', compact('penjualan'));
    }

    public function getItemInfo(Request $request)
    {
        $jenis = $request->jenis_item;
        $itemId = $request->item_id;

        try {
            if ($jenis == 'produk') {
                $item = Produk::findOrFail($itemId);
                $stok = 'N/A';
                $harga = $item->harga;
                $satuan = $item->satuan;
                $nama = $item->nama;
            } else {
                $item = BahanBaku::findOrFail($itemId);
                $stok = $item->stok;
                $harga = $item->harga_jual;
                $satuan = $item->satuan;
                $nama = $item->nama;
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'nama' => $nama,
                    'stok' => $stok,
                    'harga' => $harga,
                    'satuan' => $satuan
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item tidak ditemukan'
            ], 404);
        }
    }
}
