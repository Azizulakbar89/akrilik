<?php

namespace App\Http\Controllers\Owner;

use App\Models\BahanBaku;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BahanBakuOwnerController extends Controller
{
    public function index()
    {
        $bahanBaku = BahanBaku::all()->map(function ($bahan) {
            $parameters = $bahan->hitungParameterStok();
            $bahan->statistik = $parameters['statistik'];
            $bahan->safety_stock = $parameters['safety_stock'];
            $bahan->min = $parameters['min'];
            $bahan->max = $parameters['max'];
            $bahan->rop = $parameters['rop'];

            return $bahan;
        });

        return view('owner.bahan-baku.index', compact('bahanBaku'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'lead_time' => 'required|integer|min:1',
            'lead_time_max' => 'required|integer|min:1',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->lead_time_max < $request->lead_time) {
                $validator->errors()->add(
                    'lead_time_max',
                    'Lead Time Maksimum harus lebih besar atau sama dengan Lead Time Rata-rata'
                );
            }
        });

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validasi gagal!');
        }

        try {
            $data = $request->except(['_token', '_method', 'foto']);

            $data['safety_stock'] = 0;
            $data['min'] = 0;
            $data['max'] = 0;
            $data['rop'] = 0;

            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('bahan-baku', 'public');
                $data['foto'] = $fotoPath;
            }

            $bahanBaku = BahanBaku::create($data);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Bahan baku berhasil ditambahkan!',
                    'data' => $bahanBaku
                ], 200);
            }

            return redirect()->route('owner.bahan-baku.index')
                ->with('success', 'Bahan baku berhasil ditambahkan!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menambahkan bahan baku: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal menambahkan bahan baku: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $bahanBaku = BahanBaku::findOrFail($id);

            $parameters = $bahanBaku->hitungParameterStok();

            $bahanBaku->calculated_safety_stock = $parameters['safety_stock'];
            $bahanBaku->calculated_min = $parameters['min'];
            $bahanBaku->calculated_max = $parameters['max'];
            $bahanBaku->calculated_rop = $parameters['rop'];
            $bahanBaku->statistik = $parameters['statistik'];

            return response()->json([
                'status' => 'success',
                'data' => $bahanBaku
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
        try {
            $bahanBaku = BahanBaku::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $bahanBaku
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
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'lead_time' => 'required|integer|min:1',
            'lead_time_max' => 'required|integer|min:1',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->lead_time_max < $request->lead_time) {
                $validator->errors()->add(
                    'lead_time_max',
                    'Lead Time Maksimum harus lebih besar atau sama dengan Lead Time Rata-rata'
                );
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $bahanBaku = BahanBaku::findOrFail($id);

            $data = $request->except(['_token', '_method', 'foto']);

            if ($request->hasFile('foto')) {
                if ($bahanBaku->foto) {
                    Storage::disk('public')->delete($bahanBaku->foto);
                }

                $fotoPath = $request->file('foto')->store('bahan-baku', 'public');
                $data['foto'] = $fotoPath;
            }

            $bahanBaku->update($data);

            $parameters = $bahanBaku->hitungParameterStok();

            $bahanBaku->update([
                'safety_stock' => $parameters['safety_stock'],
                'min' => $parameters['min'],
                'max' => $parameters['max'],
                'rop' => $parameters['rop']
            ]);

            $bahanBaku->refresh();

            return response()->json([
                'status' => 'success',
                'message' => 'Bahan baku berhasil diupdate!',
                'data' => $bahanBaku
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate bahan baku: ' . $e->getMessage()
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
                'message' => 'Bahan baku berhasil dihapus!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus bahan baku: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCalculationDetail($id)
    {
        try {
            $bahanBaku = BahanBaku::findOrFail($id);

            $parameters = $bahanBaku->hitungParameterStok();
            $statistik = $parameters['statistik'];

            $hasData = $statistik['count_keluar'] > 0;

            if ($hasData) {
                $calculationDetail = [
                    'bahan_baku' => $bahanBaku->nama,
                    'lead_time' => $bahanBaku->lead_time . ' hari',
                    'lead_time_max' => $bahanBaku->lead_time_max . ' hari',
                    'statistik_penggunaan' => [
                        'total_keluar' => $statistik['total_keluar'],
                        'count_keluar' => $statistik['count_keluar'],
                        'rata_rata_per_hari' => round($statistik['rata_rata'], 2),
                        'maks_keluar_per_hari' => $statistik['maks_keluar'],
                        'range_hari' => $statistik['range_hari'],
                        'hari_aktif' => $statistik['hari_aktif']
                    ],
                    'perhitungan' => [
                        'safety_stock' => "({$statistik['maks_keluar']} × {$bahanBaku->lead_time_max}) - (" . round($statistik['rata_rata'], 2) . " × {$bahanBaku->lead_time}) = {$parameters['safety_stock']}",
                        'min_stock' => "(" . round($statistik['rata_rata'], 2) . " × {$bahanBaku->lead_time}) + {$parameters['safety_stock']} = {$parameters['min']}",
                        'max_stock' => "2 × (" . round($statistik['rata_rata'], 2) . " × {$bahanBaku->lead_time}) + {$parameters['safety_stock']} = {$parameters['max']}",
                        'rop' => "{$parameters['max']} - {$parameters['min']} = {$parameters['rop']}"
                    ],
                    'hasil' => [
                        'safety_stock' => $parameters['safety_stock'],
                        'min' => $parameters['min'],
                        'max' => $parameters['max'],
                        'rop' => $parameters['rop']
                    ],
                    'memiliki_data' => true
                ];
            } else {
                $calculationDetail = [
                    'bahan_baku' => $bahanBaku->nama,
                    'lead_time' => $bahanBaku->lead_time . ' hari',
                    'lead_time_max' => $bahanBaku->lead_time_max . ' hari',
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
