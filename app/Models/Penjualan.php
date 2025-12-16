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
        'tanggal',
        'admin_id'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'tanggal' => 'date'
    ];

    protected $appends = [
        'total_formatted',
        'bayar_formatted',
        'kembalian_formatted',
        'tanggal_formatted'
    ];

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'penjualan_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
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

    public function getTanggalFormattedAttribute()
    {
        return \Carbon\Carbon::parse($this->tanggal)->format('d/m/Y H:i');
    }

    public function cekKetersediaanItems()
    {
        foreach ($this->detailPenjualan as $detail) {
            if (!$detail->cekKetersediaan()) {
                return false;
            }
        }
        return true;
    }

    public function scopePeriode($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal', date('m'))
            ->whereYear('tanggal', date('Y'));
    }

    public function scopeTahunIni($query)
    {
        return $query->whereYear('tanggal', date('Y'));
    }
}
