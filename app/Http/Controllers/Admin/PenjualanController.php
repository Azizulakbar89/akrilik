<?php

namespace App\Http\Controllers\Admin;

use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\DetailPenjualan;
use App\Models\KomposisiBahanBaku;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = Penjualan::with('detailPenjualan')->orderBy('created_at', 'desc')->get();
        $produk = Produk::all();
        $bahanBaku = BahanBaku::all();

        return view('admin.penjualan.index', compact('penjualan', 'produk', 'bahanBaku'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_customer' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.jenis_item' => 'required|in:produk,bahan_baku',
            'items.*.item_id' => 'required',
            'items.*.jumlah' => 'required|integer|min:1',
            'bayar' => 'required|numeric|min:0'
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

            $total = 0;
            $items = [];

            foreach ($request->items as $item) {
                if ($item['jenis_item'] == 'produk') {
                    $produk = Produk::findOrFail($item['item_id']);

                    if ($produk->stok < $item['jumlah']) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Stok produk ' . $produk->nama . ' tidak mencukupi. Stok tersedia: ' . $produk->stok
                        ], 422);
                    }

                    if (!$produk->bisaDiproduksi($item['jumlah'])) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Stok bahan baku tidak mencukupi untuk memproduksi produk ' . $produk->nama
                        ], 422);
                    }

                    $subtotal = $produk->harga * $item['jumlah'];
                    $total += $subtotal;

                    $items[] = [
                        'produk_id' => $produk->id,
                        'bahan_baku_id' => null,
                        'nama_produk' => $produk->nama,
                        'jenis_item' => 'produk',
                        'jumlah' => $item['jumlah'],
                        'harga_sat' => $produk->harga,
                        'sub_total' => $subtotal
                    ];
                } else {
                    $bahanBaku = BahanBaku::findOrFail($item['item_id']);

                    if ($bahanBaku->stok < $item['jumlah']) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Stok bahan baku ' . $bahanBaku->nama . ' tidak mencukupi. Stok tersedia: ' . $bahanBaku->stok
                        ], 422);
                    }

                    $subtotal = $bahanBaku->harga_jual * $item['jumlah'];
                    $total += $subtotal;

                    $items[] = [
                        'produk_id' => null,
                        'bahan_baku_id' => $bahanBaku->id,
                        'nama_produk' => $bahanBaku->nama,
                        'jenis_item' => 'bahan_baku',
                        'jumlah' => $item['jumlah'],
                        'harga_sat' => $bahanBaku->harga_jual,
                        'sub_total' => $subtotal
                    ];
                }
            }

            if ($request->bayar < $total) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jumlah pembayaran kurang dari total'
                ], 422);
            }

            $kembalian = $request->bayar - $total;

            $penjualan = Penjualan::create([
                'nama_customer' => $request->nama_customer,
                'total' => $total,
                'bayar' => $request->bayar,
                'kembalian' => $kembalian,
                'tanggal' => now()
            ]);

            foreach ($items as $item) {
                DetailPenjualan::create(array_merge($item, ['penjualan_id' => $penjualan->id]));

                if ($item['jenis_item'] == 'produk') {
                    $produk = Produk::find($item['produk_id']);

                    $produk->stok -= $item['jumlah'];
                    $produk->save();

                    $produk->kurangiStokBahanBaku($item['jumlah']);
                } else {
                    $bahanBaku = BahanBaku::find($item['bahan_baku_id']);
                    $bahanBaku->stok -= $item['jumlah'];
                    $bahanBaku->save();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penjualan berhasil disimpan',
                'data' => [
                    'kode_penjualan' => $penjualan->kode_penjualan,
                    'total' => $total,
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

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $penjualan = Penjualan::with('detailPenjualan')->findOrFail($id);

            foreach ($penjualan->detailPenjualan as $detail) {
                if ($detail->jenis_item == 'produk') {
                    $produk = Produk::find($detail->produk_id);
                    if ($produk) {
                        $produk->stok += $detail->jumlah;
                        $produk->save();

                        $produk->kembalikanStokBahanBaku($detail->jumlah);
                    }
                } else {
                    $bahanBaku = BahanBaku::find($detail->bahan_baku_id);
                    if ($bahanBaku) {
                        $bahanBaku->kembalikanStok($detail->jumlah);
                    }
                }
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

    public function getItemInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_item' => 'required|in:produk,bahan_baku',
            'item_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal'
            ], 422);
        }

        try {
            if ($request->jenis_item == 'produk') {
                $item = Produk::findOrFail($request->item_id);
                $stok = $item->stok;
                $harga = $item->harga;
                $nama = $item->nama;
                $satuan = $item->satuan;
            } else {
                $item = BahanBaku::findOrFail($request->item_id);
                $stok = $item->stok;
                $harga = $item->harga_jual;
                $nama = $item->nama;
                $satuan = $item->satuan;
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'nama' => $nama,
                    'stok' => $stok,
                    'harga' => $harga,
                    'satuan' => $satuan
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }
}
