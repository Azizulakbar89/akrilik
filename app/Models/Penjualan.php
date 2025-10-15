<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';
    protected $fillable = [
        'kode_penjualan',
        'nama_customer',
        'total',
        'bayar',
        'kembalian',
        'tanggal'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'tanggal' => 'date'
    ];

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'penjualan_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($penjualan) {
            $penjualan->kode_penjualan = 'PJL-' . date('Ymd') . '-' . str_pad(Penjualan::count() + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public function getTotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    public function getBayarFormattedAttribute()
    {
        return 'Rp ' . number_format($this->bayar, 0, ',', '.');
    }

    public function getKembalianFormattedAttribute()
    {
        return 'Rp ' . number_format($this->kembalian, 0, ',', '.');
    }
}
