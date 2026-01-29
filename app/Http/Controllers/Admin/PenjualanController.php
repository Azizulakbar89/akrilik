<?php

namespace App\Http\Controllers\Admin;

use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\PenggunaanBahanBaku;
use App\Models\KomposisiBahanBaku;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $searchBahanBaku = $request->search_bahan_baku ?? null;
        $tanggalAwal = $request->tanggal_awal ?? date('Y-m-01');
        $tanggalAkhir = $request->tanggal_akhir ?? date('Y-m-d');

        // Query untuk data penjualan dengan filter tanggal
        $penjualanQuery = Penjualan::with(['detailPenjualan', 'admin'])
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
            ->latest();

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

        $penjualan = $penjualanQuery->get();

        // Tambahkan format untuk total, bayar, dan kembalian
        $penjualan->each(function ($item) {
            $item->total_formatted = 'Rp ' . number_format($item->total, 0, ',', '.');
            $item->bayar_formatted = 'Rp ' . number_format($item->bayar, 0, ',', '.');
            $item->kembalian_formatted = 'Rp ' . number_format($item->kembalian, 0, ',', '.');
            $item->bahan_baku_digunakan = $this->getBahanBakuUntukPenjualan($item);
        });

        // Hitung total bahan baku keluar per periode
        $totalBahanBakuKeluar = $this->getTotalBahanBakuKeluar($tanggalAwal, $tanggalAkhir, $searchBahanBaku);

        // Hitung total pendapatan
        $totalPendapatan = $penjualan->sum('total');
        $totalPendapatanFormatted = 'Rp ' . number_format($totalPendapatan, 0, ',', '.');

        // Get bahan baku list for dropdown
        $bahanBakuList = BahanBaku::orderBy('nama')->get();

        // Get produk dan bahan baku untuk form penjualan
        $produk = Produk::with(['komposisi.bahanBaku'])->get()->map(function ($produk) {
            $produk->info_penjualan = $produk->getInfoForPenjualan();
            return $produk;
        });

        $bahanBaku = BahanBaku::all()->map(function ($bahan) {
            $bahan->info_penjualan = $bahan->getInfoForPenjualan();
            return $bahan;
        });

        return view('admin.penjualan.index', compact(
            'penjualan',
            'produk',
            'bahanBaku',
            'bahanBakuList',
            'searchBahanBaku',
            'tanggalAwal',
            'tanggalAkhir',
            'totalBahanBakuKeluar',
            'totalPendapatan',
            'totalPendapatanFormatted'
        ));
    }

    /**
     * Mendapatkan total bahan baku keluar per periode
     */
    private function getTotalBahanBakuKeluar($tanggalAwal, $tanggalAkhir, $searchBahanBaku = null)
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
                            'total_penggunaan' => 0,
                            'total_harga_beli' => 0
                        ];
                    }

                    $bahanBakuUsage[$bahanBakuId]['total_penggunaan'] += $jumlahPenggunaan;
                    $bahanBakuUsage[$bahanBakuId]['total_harga_beli'] += ($item->bahanBaku->harga_beli ?? 0) * $jumlahPenggunaan;
                }
            }
        }

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
                        'total_harga_beli' => 0
                    ];
                }
                $bahanBakuUsage[$item->bahan_baku_id]['total_penggunaan'] += $item->total_terjual_langsung;
                $bahanBakuUsage[$item->bahan_baku_id]['total_harga_beli'] += ($bahanBaku->harga_beli ?? 0) * $item->total_terjual_langsung;
            }
        }

        // Format data untuk view
        $formattedData = collect($bahanBakuUsage)->map(function ($item) {
            return [
                'id' => $item['bahan_baku']->id,
                'nama' => $item['bahan_baku']->nama,
                'total_penggunaan' => $item['total_penggunaan'],
                'satuan' => $item['bahan_baku']->satuan,
                'harga_beli' => $item['bahan_baku']->harga_beli,
                'total_harga_beli' => $item['total_harga_beli'],
                'harga_beli_formatted' => 'Rp ' . number_format($item['bahan_baku']->harga_beli ?? 0, 0, ',', '.'),
                'total_harga_beli_formatted' => 'Rp ' . number_format($item['total_harga_beli'] ?? 0, 0, ',', '.'),
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
                    'admin_name' => Auth::user()->name,
                    'bahan_baku_digunakan' => $this->calculateTotalBahanBaku($penjualan)
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

    public function show($id)
    {
        try {
            $penjualan = Penjualan::with(['detailPenjualan.produk.komposisi.bahanBaku', 'detailPenjualan.bahanBaku', 'admin'])
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
                'tanggal_formatted' => $penjualan->tanggal_formatted,
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
        $penjualan = Penjualan::with(['detailPenjualan.produk.komposisi.bahanBaku', 'detailPenjualan.bahanBaku', 'admin'])
            ->findOrFail($id);

        $penjualan->total_bahan_baku = $this->calculateTotalBahanBaku($penjualan);

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

                $bahan_baku_digunakan = [];
                foreach ($item->komposisi as $komposisi) {
                    $bahan_baku_digunakan[] = [
                        'nama' => $komposisi->bahanBaku->nama,
                        'jumlah_per_unit' => $komposisi->jumlah,
                        'satuan' => $komposisi->bahanBaku->satuan,
                        'stok_tersedia' => $komposisi->bahanBaku->stok
                    ];
                }
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
                $bahan_baku_digunakan = [];
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
                    'bahan_baku_digunakan' => $bahan_baku_digunakan,
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
