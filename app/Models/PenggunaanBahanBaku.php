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
        'jumlah' => 'integer',
        'tanggal' => 'date'
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function getJumlahFormattedAttribute()
    {
        return number_format($this->jumlah, 0, ',', '.');
    }

    public function getJenisAttribute()
    {
        return $this->jumlah > 0 ? 'Penggunaan' : 'Pengembalian';
    }

    public function getJenisClassAttribute()
    {
        return $this->jumlah > 0 ? 'text-danger' : 'text-success';
    }
}
