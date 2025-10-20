<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenggunaanBahanBaku extends Model
{
    use HasFactory;

    protected $table = 'penggunaan_bahan_baku';
    protected $fillable = [
        'bahan_baku_id',
        'jumlah',
        'tanggal',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'integer'
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    // Scope untuk penggunaan positif (pengurangan stok)
    public function scopePenggunaan($query)
    {
        return $query->where('jumlah', '>', 0);
    }

    // Scope untuk pengembalian (penambahan stok)
    public function scopePengembalian($query)
    {
        return $query->where('jumlah', '<', 0);
    }
}
