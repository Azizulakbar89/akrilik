<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';
    protected $fillable = [
        'nama',
        'alamat',
        'notel',
        'lead_time'
    ];

    protected $casts = [
        'lead_time' => 'integer'
    ];

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'supplier_id');
    }

    public function getNamaSupplierAttribute()
    {
        return $this->nama;
    }

    public function getLeadTimeFormattedAttribute()
    {
        return $this->lead_time . ' hari';
    }
}
