<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_baku';
    protected $fillable = [
        'nama',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok',
        'safety_stock',
        'rop',
        'min',
        'max',
        'foto'
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok' => 'integer',
        'safety_stock' => 'integer',
        'rop' => 'integer',
        'min' => 'integer',
        'max' => 'integer'
    ];

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'bahan_baku_id');
    }

    public function komposisi()
    {
        return $this->hasMany(KomposisiBahanBaku::class, 'bahan_baku_id');
    }

    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelian::class, 'bahan_baku_id');
    }

    public function isPerluPembelian()
    {
        return $this->stok <= $this->min;
    }

    public function isStokTidakAman()
    {
        return $this->stok <= $this->min;
    }

    public function getStatusStokAttribute()
    {
        if ($this->stok <= $this->min) {
            return '<span class="badge badge-danger">Stok Tidak Aman</span>';
        } elseif ($this->stok <= $this->safety_stock) {
            return '<span class="badge badge-warning">Stok Menipis</span>';
        } else {
            return '<span class="badge badge-success">Stok Aman</span>';
        }
    }

    public function jumlahPemesananRekomendasi()
    {
        if ($this->isPerluPembelian()) {
            $quantity = $this->max - $this->stok;

            return max(0, $quantity);
        }
        return 0;
    }

    public function totalNilaiPemesananRekomendasi()
    {
        $quantity = $this->jumlahPemesananRekomendasi();
        return $quantity * $this->harga_beli;
    }

    public function scopePerluPembelian($query)
    {
        return $query->whereColumn('stok', '<=', 'min');
    }

    public function scopeStokTidakAman($query)
    {
        return $query->whereColumn('stok', '<=', 'min');
    }

    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return Storage::disk('public')->url($this->foto);
        }
        return asset('vendors/images/default-bahan-baku.jpg');
    }

    public function getHargaBeliFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga_beli, 0, ',', '.');
    }

    public function getHargaJualFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga_jual, 0, ',', '.');
    }

    public function getRekomendasiPembelianAttribute()
    {
        if ($this->isPerluPembelian()) {
            return [
                'bahan_baku_id' => $this->id,
                'nama' => $this->nama,
                'stok_sekarang' => $this->stok,
                'min' => $this->min,
                'max' => $this->max,
                'jumlah_rekomendasi' => $this->jumlahPemesananRekomendasi(),
                'harga_beli' => $this->harga_beli,
                'total_nilai' => $this->totalNilaiPemesananRekomendasi(),
                'satuan' => $this->satuan
            ];
        }
        return null;
    }

    public function jualSebagaiProduk($jumlah)
    {
        if ($this->stok < $jumlah) {
            throw new \Exception("Stok bahan baku {$this->nama} tidak mencukupi");
        }

        $this->stok -= $jumlah;
        $this->save();

        return $this->harga_jual * $jumlah;
    }

    public function kembalikanStok($jumlah)
    {
        $this->stok += $jumlah;
        $this->save();
    }
}
