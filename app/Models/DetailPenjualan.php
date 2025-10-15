<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    use HasFactory;

    protected $table = 'detail_penjualan';
    protected $fillable = [
        'penjualan_id',
        'produk_id',
        'bahan_baku_id',
        'nama_produk',
        'jenis_item',
        'jumlah',
        'harga_sat',
        'sub_total'
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_sat' => 'decimal:2',
        'sub_total' => 'decimal:2'
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function getHargaSatFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga_sat, 0, ',', '.');
    }

    public function getSubTotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->sub_total, 0, ',', '.');
    }

    public function getNamaItemAttribute()
    {
        if ($this->jenis_item == 'produk' && $this->produk) {
            return $this->produk->nama;
        } elseif ($this->jenis_item == 'bahan_baku' && $this->bahanBaku) {
            return $this->bahanBaku->nama;
        }
        return $this->nama_produk;
    }
}
