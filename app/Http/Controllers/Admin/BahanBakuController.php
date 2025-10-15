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
        $bahanBaku = BahanBaku::all();
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
            'safety_stock' => 'required|integer|min:0',
            'rop' => 'required|integer|min:0',
            'min' => 'required|integer|min:0',
            'max' => 'required|integer|min:0|gt:min',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ], [
            'max.gt' => 'Nilai Max harus lebih besar dari Min',
            'nama.unique' => 'Nama bahan baku sudah ada'
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
                'safety_stock',
                'rop',
                'min',
                'max'
            ]);

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $data['foto'] = $file->storeAs('bahan-baku', $filename, 'public');
            }

            BahanBaku::create($data);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Bahan baku berhasil ditambahkan'
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
        $bahanBaku = BahanBaku::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255|unique:bahan_baku,nama,' . $id,
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'safety_stock' => 'required|integer|min:0',
            'rop' => 'required|integer|min:0',
            'min' => 'required|integer|min:0',
            'max' => 'required|integer|min:0|gt:min',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ], [
            'max.gt' => 'Nilai Max harus lebih besar dari Min',
            'nama.unique' => 'Nama bahan baku sudah ada'
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
                'safety_stock',
                'rop',
                'min',
                'max'
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

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Bahan baku berhasil diupdate'
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

            // Hapus foto jika ada
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
}
