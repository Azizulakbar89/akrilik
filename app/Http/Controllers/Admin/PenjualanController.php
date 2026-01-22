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
use Illuminate\Support\Facades\Auth;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = Penjualan::with('detailPenjualan', 'admin')->latest()->get();

        $produk = Produk::with(['komposisi.bahanBaku'])->get()->map(function ($produk) {
            $produk->info_penjualan = $produk->getInfoForPenjualan();
            return $produk;
        });

        $bahanBaku = BahanBaku::all()->map(function ($bahan) {
            $bahan->info_penjualan = $bahan->getInfoForPenjualan();
            return $bahan;
        });

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
                    $produk = Produk::with('komposisi.bahanBaku')->findOrFail($item['item_id']);

                    if (!$produk->bisaDiproduksi($item['jumlah'])) {
                        $bahanTidakCukup = $produk->bahanBakuYangTidakCukup($item['jumlah']);
                        $errorMessage = "Bahan baku tidak mencukupi untuk memproduksi produk: {$produk->nama}\n";

                        foreach ($bahanTidakCukup as $bahan) {
                            $errorMessage .= "- {$bahan['nama']}: Stok {$bahan['stok_tersedia']} {$bahan['satuan']}, Dibutuhkan {$bahan['dibutuhkan']} {$bahan['satuan']}, Kurang {$bahan['kekurangan']} {$bahan['satuan']}\n";
                        }

                        throw new \Exception($errorMessage);
                    }
                } elseif ($item['jenis_item'] == 'bahan_baku') {
                    $bahanBaku = BahanBaku::findOrFail($item['item_id']);
                    if ($bahanBaku->stok < $item['jumlah']) {
                        throw new \Exception("Stok bahan baku {$bahanBaku->nama} tidak mencukupi. Stok tersedia: {$bahanBaku->stok}, Dibutuhkan: {$item['jumlah']}");
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
                'tanggal' => now(),
                'admin_id' => Auth::id()
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
                    'kembalian' => $kembalian,
                    'admin_name' => Auth::user()->name
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
            $penjualan = Penjualan::with(['detailPenjualan.produk', 'detailPenjualan.bahanBaku', 'admin'])->findOrFail($id);
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
        $penjualan = Penjualan::with(['detailPenjualan.produk', 'detailPenjualan.bahanBaku', 'admin'])->findOrFail($id);
        return view('admin.penjualan.nota', compact('penjualan'));
    }

    public function getItemInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_item' => 'required|in:produk,bahan_baku',
            'item_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid'
            ], 400);
        }

        $jenis = $request->jenis_item;
        $itemId = $request->item_id;

        try {
            if ($jenis == 'produk') {
                $item = Produk::with('komposisi.bahanBaku')->findOrFail($itemId);
                $info = $item->getInfoForPenjualan();
                $stok = 'N/A';
                $harga = $item->harga;
                $satuan = $item->satuan;
                $nama = $item->nama;
                $status_ss = $info['status_bahan_baku_badge'];
                $perlu_pembelian = $info['perlu_pembelian_bahan'];
                $bisa_diproduksi = $info['bisa_diproduksi_satu_unit'];
                $bahan_tidak_cukup = $info['bahan_tidak_cukup'];
            } else {
                $item = BahanBaku::findOrFail($itemId);
                $info = $item->getInfoForPenjualan();
                $stok = $item->stok;
                $harga = $item->harga_jual;
                $satuan = $item->satuan;
                $nama = $item->nama;
                $status_ss = $info['status_ss_badge'];
                $perlu_pembelian = !$item->isStokAmanSS();
                $bisa_diproduksi = null;
                $bahan_tidak_cukup = [];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'nama' => $nama,
                    'stok' => $stok,
                    'harga' => $harga,
                    'satuan' => $satuan,
                    'status_ss' => $status_ss,
                    'perlu_pembelian' => $perlu_pembelian,
                    'bisa_diproduksi' => $bisa_diproduksi,
                    'bahan_tidak_cukup' => $bahan_tidak_cukup,
                    'info_lengkap' => $info
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
