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
        $bahanBaku = BahanBaku::all();
        return view('owner.bahan-baku.index', compact('bahanBaku'));
    }

    public function create()
    {
        return view('owner.bahan-baku.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
            'safety_stock' => 'required|numeric|min:0',
            'rop' => 'required|numeric|min:0',
            'min' => 'required|numeric|min:0',
            'max' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $data = $request->all();

            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('bahan-baku', 'public');
                $data['foto'] = $fotoPath;
            }

            BahanBaku::create($data);

            return redirect()->route('owner.bahan-baku.index')
                ->with('success', 'Bahan baku berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan bahan baku: ' . $e->getMessage())
                ->withInput();
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

    public function edit($id)
    {
        try {
            $bahanBaku = BahanBaku::findOrFail($id);
            return view('owner.bahan-baku.edit', compact('bahanBaku'));
        } catch (\Exception $e) {
            return redirect()->route('owner.bahan-baku.index')
                ->with('error', 'Bahan baku tidak ditemukan!');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
            'safety_stock' => 'required|numeric|min:0',
            'rop' => 'required|numeric|min:0',
            'min' => 'required|numeric|min:0',
            'max' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $bahanBaku = BahanBaku::findOrFail($id);
            $data = $request->all();

            if ($request->hasFile('foto')) {
                if ($bahanBaku->foto) {
                    Storage::disk('public')->delete($bahanBaku->foto);
                }

                $fotoPath = $request->file('foto')->store('bahan-baku', 'public');
                $data['foto'] = $fotoPath;
            }

            $bahanBaku->update($data);

            return redirect()->route('owner.bahan-baku.index')
                ->with('success', 'Bahan baku berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui bahan baku: ' . $e->getMessage())
                ->withInput();
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

            return redirect()->route('owner.bahan-baku.index')
                ->with('success', 'Bahan baku berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('owner.bahan-baku.index')
                ->with('error', 'Gagal menghapus bahan baku: ' . $e->getMessage());
        }
    }
}
