<?php

namespace App\Http\Controllers\Owner;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplierOwnerController extends Controller
{
    public function index()
    {
        $supplier = Supplier::all();
        return view('owner.supplier.index', compact('supplier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'notel' => 'required|string|max:20',
            'lead_time' => 'required|integer|min:1'
        ]);

        Supplier::create($request->all());

        return response()->json(['success' => 'Supplier berhasil ditambahkan']);
    }

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'notel' => 'required|string|max:20',
            'lead_time' => 'required|integer|min:1'
        ]);

        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());

        return response()->json(['success' => 'Supplier berhasil diupdate']);
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        if ($supplier->pembelian()->exists()) {
            return response()->json([
                'error' => 'Tidak dapat menghapus supplier karena memiliki data pembelian terkait'
            ], 422);
        }

        $supplier->delete();

        return response()->json(['success' => 'Supplier berhasil dihapus']);
    }
}
