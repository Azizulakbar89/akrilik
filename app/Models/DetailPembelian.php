<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembelian extends Model
{
    use HasFactory;

    protected $table = 'detail_pembelian';
    protected $fillable = [
        'pembelian_id',
        'bahan_baku_id',
        'jumlah',
        'harga',
        'sub_total'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}
