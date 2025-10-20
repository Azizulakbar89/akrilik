<?php

namespace App\Http\Controllers\Owner;

use App\Models\BahanBaku;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class BahanBakuOwnerController extends Controller
{
    public function index()
    {
        $bahanBaku = BahanBaku::all()->map(function ($bahan) {
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

        return view('owner.bahan-baku.index', compact('bahanBaku'));
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
