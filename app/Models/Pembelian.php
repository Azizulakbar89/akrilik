<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian';
    protected $fillable = [
        'kode_pembelian',
        'supplier_id',
        'total',
        'tanggal',
        'status'
    ];

    const STATUS_MENUNGGU = 'menunggu_persetujuan';
    const STATUS_DISETUJUI = 'completed';
    const STATUS_DITOLAK = 'ditolak';

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelian::class, 'pembelian_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pembelian) {
            $pembelian->kode_pembelian = 'PBL-' . date('Ymd') . '-' . str_pad(Pembelian::count() + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public function isMenungguPersetujuan()
    {
        return $this->status === self::STATUS_MENUNGGU;
    }

    public function isDisetujui()
    {
        return $this->status === self::STATUS_DISETUJUI;
    }

    public function isDitolak()
    {
        return $this->status === self::STATUS_DITOLAK;
    }

    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            self::STATUS_MENUNGGU => '<span class="badge badge-warning">Menunggu Persetujuan</span>',
            self::STATUS_DISETUJUI => '<span class="badge badge-success">Disetujui</span>',
            self::STATUS_DITOLAK => '<span class="badge badge-danger">Ditolak</span>',
        ];

        return $statusLabels[$this->status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    public function getTanggalFormattedAttribute()
    {
        return \Carbon\Carbon::parse($this->tanggal)->format('d/m/Y');
    }

    public function getTotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }
}
