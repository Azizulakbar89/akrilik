<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $fillable = [
        'nama',
        'satuan',
        'harga',
        'stok',
        'foto'
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer'
    ];

    public function komposisi()
    {
        return $this->hasMany(KomposisiBahanBaku::class, 'produk_id');
    }

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'produk_id');
    }

    public function bisaDiproduksi($jumlah)
    {
        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;
            $kebutuhan = $komposisi->jumlah * $jumlah;

            if ($bahanBaku->stok < $kebutuhan) {
                return false;
            }
        }
        return true;
    }

    public function kurangiStokBahanBaku($jumlah)
    {
        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;
            $kebutuhan = $komposisi->jumlah * $jumlah;

            $bahanBaku->stok -= $kebutuhan;
            $bahanBaku->save();
        }
    }

    public function kembalikanStokBahanBaku($jumlah)
    {
        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;
            $kebutuhan = $komposisi->jumlah * $jumlah;

            $bahanBaku->stok += $kebutuhan;
            $bahanBaku->save();
        }
    }

    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return Storage::disk('public')->url($this->foto);
        }
        return asset('vendors/images/default-produk.jpg');
    }

    public function getHargaJualAttribute()
    {
        return $this->harga;
    }

    public function getHppAttribute()
    {
        $hpp = 0;
        foreach ($this->komposisi as $komposisi) {
            $hpp += $komposisi->bahanBaku->harga_beli * $komposisi->jumlah;
        }
        return $hpp;
    }

    public function kurangiStok($jumlah)
    {
        if ($this->stok < $jumlah) {
            throw new \Exception("Stok produk {$this->nama} tidak mencukupi");
        }

        $this->stok -= $jumlah;
        $this->save();
    }

    public function tambahStok($jumlah)
    {
        $this->stok += $jumlah;
        $this->save();
    }
}
