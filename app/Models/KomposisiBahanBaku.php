<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomposisiBahanBaku extends Model
{
    use HasFactory;

    protected $table = 'komposisi_bahan_baku';
    protected $fillable = [
        'bahan_baku_id',
        'produk_id',
        'jumlah'
    ];

    protected $casts = [
        'jumlah' => 'integer'
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
