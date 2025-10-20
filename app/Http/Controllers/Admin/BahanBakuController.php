<?php

namespace App\Http\Controllers\Admin;

use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BahanBakuController extends Controller
{
    public function index()
    {
        $bahanBaku = BahanBaku::all()->map(function ($bahan) {
            // Update parameter stok otomatis jika sudah ada penggunaan
            if ($bahan->sudahAdaPenggunaan()) {
                $parameters = $bahan->updateParameterStok();
                $bahan->statistik = $parameters['statistik'];
            } else {
                $bahan->statistik = [
                    'total_keluar' => 0,
                    'count_keluar' => 0,
                    'rata_rata' => 0,
                    'maks_keluar' => 0,
                    'range_hari' => 30,
                    'hari_aktif' => 0
                ];
            }
            return $bahan;
        });

        return view('admin.bahan-baku.index', compact('bahanBaku'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255|unique:bahan_baku,nama',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'lead_time' => 'required|integer|min:1',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ], [
            'nama.unique' => 'Nama bahan baku sudah ada',
            'lead_time.min' => 'Lead time minimal 1 hari'
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

            $data = $request->only([
                'nama',
                'satuan',
                'harga_beli',
                'harga_jual',
                'stok',
                'lead_time'
            ]);

            // Default values untuk parameter stok (0)
            $data['safety_stock'] = 0;
            $data['rop'] = 0;
            $data['min'] = 0;
            $data['max'] = 0;

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $data['foto'] = $file->storeAs('bahan-baku', $filename, 'public');
            }

            $bahanBaku = BahanBaku::create($data);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Bahan baku berhasil ditambahkan. Parameter stok akan terhitung otomatis setelah ada transaksi.'
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
            $bahanBaku = BahanBaku::findOrFail($id);

            if ($bahanBaku->sudahAdaPenggunaan()) {
                $parameters = $bahanBaku->hitungParameterStok();
            } else {
                $parameters = [
                    'safety_stock' => 0,
                    'min' => 0,
                    'max' => 0,
                    'rop' => 0,
                    'statistik' => [
                        'total_keluar' => 0,
                        'count_keluar' => 0,
                        'rata_rata' => 0,
                        'maks_keluar' => 0,
                        'range_hari' => 30,
                        'hari_aktif' => 0
                    ]
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => $bahanBaku,
                'parameters' => $parameters
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255|unique:bahan_baku,nama,' . $id,
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'lead_time' => 'required|integer|min:1',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ], [
            'nama.unique' => 'Nama bahan baku sudah ada',
            'lead_time.min' => 'Lead time minimal 1 hari'
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

            $data = $request->only([
                'nama',
                'satuan',
                'harga_beli',
                'harga_jual',
                'stok',
                'lead_time'
            ]);

            if ($request->hasFile('foto')) {
                if ($bahanBaku->foto) {
                    Storage::disk('public')->delete($bahanBaku->foto);
                }

                $file = $request->file('foto');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $data['foto'] = $file->storeAs('bahan-baku', $filename, 'public');
            }

            $bahanBaku->update($data);

            // Update parameter stok otomatis setelah update jika sudah ada penggunaan
            if ($bahanBaku->sudahAdaPenggunaan()) {
                $bahanBaku->updateParameterStok();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Bahan baku berhasil diupdate' . ($bahanBaku->sudahAdaPenggunaan() ? ' dan parameter stok dihitung ulang' : '')
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
            $bahanBaku = BahanBaku::findOrFail($id);

            if ($bahanBaku->foto) {
                Storage::disk('public')->delete($bahanBaku->foto);
            }

            $bahanBaku->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Bahan baku berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method untuk menghitung ulang semua parameter stok
    public function recalculateAll()
    {
        try {
            DB::beginTransaction();

            $bahanBakuList = BahanBaku::all();
            $updatedCount = 0;

            foreach ($bahanBakuList as $bahan) {
                if ($bahan->sudahAdaPenggunaan()) {
                    $bahan->updateParameterStok();
                    $updatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil menghitung ulang parameter stok untuk {$updatedCount} bahan baku yang memiliki data penggunaan"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method untuk melihat detail perhitungan
    public function getCalculationDetail($id)
    {
        try {
            $bahanBaku = BahanBaku::findOrFail($id);

            if ($bahanBaku->sudahAdaPenggunaan()) {
                $parameters = $bahanBaku->hitungParameterStok();
                $statistik = $parameters['statistik'];

                $calculationDetail = [
                    'bahan_baku' => $bahanBaku->nama,
                    'lead_time' => $bahanBaku->lead_time . ' hari',
                    'statistik_penggunaan' => [
                        'total_keluar' => $statistik['total_keluar'],
                        'count_keluar' => $statistik['count_keluar'],
                        'rata_rata_per_hari' => $statistik['rata_rata'],
                        'maks_keluar_per_hari' => $statistik['maks_keluar'],
                        'range_hari' => $statistik['range_hari'],
                        'hari_aktif' => $statistik['hari_aktif']
                    ],
                    'perhitungan' => [
                        'safety_stock' => "({$statistik['maks_keluar']} - {$statistik['rata_rata']}) × {$bahanBaku->lead_time} = {$parameters['safety_stock']}",
                        'min_stock' => "({$statistik['rata_rata']} × {$bahanBaku->lead_time}) + {$parameters['safety_stock']} = {$parameters['min']}",
                        'max_stock' => "2 × ({$statistik['rata_rata']} × {$bahanBaku->lead_time}) + {$parameters['safety_stock']} = {$parameters['max']}",
                        'rop' => "{$parameters['max']} - {$parameters['min']} = {$parameters['rop']}"
                    ],
                    'hasil' => $parameters,
                    'memiliki_data' => true
                ];
            } else {
                $calculationDetail = [
                    'bahan_baku' => $bahanBaku->nama,
                    'lead_time' => $bahanBaku->lead_time . ' hari',
                    'statistik_penggunaan' => [
                        'total_keluar' => 0,
                        'count_keluar' => 0,
                        'rata_rata_per_hari' => 0,
                        'maks_keluar_per_hari' => 0,
                        'range_hari' => 30,
                        'hari_aktif' => 0
                    ],
                    'perhitungan' => [
                        'safety_stock' => "Belum ada data penggunaan",
                        'min_stock' => "Belum ada data penggunaan",
                        'max_stock' => "Belum ada data penggunaan",
                        'rop' => "Belum ada data penggunaan"
                    ],
                    'hasil' => [
                        'safety_stock' => 0,
                        'min' => 0,
                        'max' => 0,
                        'rop' => 0
                    ],
                    'memiliki_data' => false
                ];
            }

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
