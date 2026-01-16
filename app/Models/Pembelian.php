<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian';
    protected $fillable = [
        'kode_pembelian',
        'supplier_id',
        'total',
        'tanggal',
        'waktu_penerimaan',
        'status'
    ];

    const STATUS_MENUNGGU = 'menunggu_persetujuan';
    const STATUS_DISETUJUI = 'completed';
    const STATUS_DITOLAK = 'ditolak';
    const STATUS_DITERIMA = 'diterima';

    protected $dates = ['tanggal', 'waktu_penerimaan', 'created_at'];
    protected $casts = [
        'tanggal' => 'date',
        'waktu_penerimaan' => 'datetime',
        'created_at' => 'datetime'
    ];

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

    public function isDiterima()
    {
        return $this->status === self::STATUS_DITERIMA;
    }

    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            self::STATUS_MENUNGGU => '<span class="badge badge-warning">Menunggu Persetujuan</span>',
            self::STATUS_DISETUJUI => '<span class="badge badge-success">Disetujui</span>',
            self::STATUS_DITOLAK => '<span class="badge badge-danger">Ditolak</span>',
            self::STATUS_DITERIMA => '<span class="badge badge-info">Diterima</span>',
        ];

        return $statusLabels[$this->status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    public function getTanggalFormattedAttribute()
    {
        return \Carbon\Carbon::parse($this->tanggal)->format('d/m/Y');
    }

    public function getWaktuPenerimaanFormattedAttribute()
    {
        return $this->waktu_penerimaan
            ? \Carbon\Carbon::parse($this->waktu_penerimaan)->format('d/m/Y H:i:s')
            : '-';
    }

    public function getTotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    public function canBeReceived()
    {
        return $this->status === self::STATUS_DISETUJUI && !$this->waktu_penerimaan;
    }

    /**
     * Hitung lead time dari created_at ke waktu_penerimaan dalam HARI
     */
    public function calculateLeadTime()
    {
        if (!$this->waktu_penerimaan || !$this->created_at) {
            return null;
        }

        $createdAt = Carbon::parse($this->created_at);
        $waktuPenerimaan = Carbon::parse($this->waktu_penerimaan);

        // Hitung selisih dalam jam terlebih dahulu
        $leadTimeHours = $waktuPenerimaan->floatDiffInHours($createdAt);

        // Konversi ke hari (1 hari = 24 jam)
        $leadTimeDays = $leadTimeHours / 24;

        // Bulatkan ke 2 desimal
        return round($leadTimeDays, 2);
    }

    public function getLeadTimeFormattedAttribute()
    {
        $leadTime = $this->calculateLeadTime();

        if (!$leadTime) {
            return '-';
        }

        if ($leadTime < 1) {
            return "1 hari";
        }

        $days = ceil($leadTime);
        return $days . " hari";
    }

    public function getLeadTimeDetailAttribute()
    {
        $leadTime = $this->calculateLeadTime();

        if (!$leadTime) {
            return [
                'days' => 0,
                'hours' => 0,
                'formatted' => '-',
                'raw' => 0
            ];
        }

        $days = floor($leadTime);
        $hours = round(($leadTime - $days) * 24);

        if ($leadTime < 1) {
            return [
                'days' => 1,
                'hours' => 0,
                'formatted' => '1 hari',
                'raw' => $leadTime
            ];
        }

        if ($hours > 0) {
            $days++; // Tambah 1 hari jika ada jam
        }

        return [
            'days' => $days,
            'hours' => $hours,
            'formatted' => $days . ' hari',
            'raw' => $leadTime
        ];
    }

    /**
     * Method untuk mendapatkan detail lead time per bahan baku
     */
    public function getLeadTimeDetails()
    {
        $details = [];
        foreach ($this->detailPembelian as $detail) {
            $bahan = $detail->bahanBaku;
            if ($bahan) {
                $leadTimeDetail = $this->lead_time_detail;
                $details[] = [
                    'bahan_baku' => $bahan->nama,
                    'bahan_baku_id' => $bahan->id,
                    'jumlah' => $detail->jumlah,
                    'satuan' => $bahan->satuan,
                    'lead_time_bahan' => $bahan->lead_time_formatted,
                    'lead_time_max_bahan' => $bahan->lead_time_max_formatted,
                    'lead_time_actual' => $leadTimeDetail['formatted'],
                    'lead_time_days' => $leadTimeDetail['days'],
                    'lead_time_raw' => $leadTimeDetail['raw']
                ];
            }
        }
        return $details;
    }

    /**
     * Method untuk mendapatkan created_at dalam format yang mudah dibaca
     */
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i:s') : '-';
    }

    /**
     * Method untuk mendapatkan waktu_penerimaan dalam format yang mudah dibaca
     */
    public function getWaktuPenerimaanFormattedDetailedAttribute()
    {
        return $this->waktu_penerimaan ? $this->waktu_penerimaan->format('d/m/Y H:i:s') : '-';
    }

    public function getLeadTimeStats()
    {
        $leadTime = $this->calculateLeadTime();

        if (!$leadTime) {
            return null;
        }

        $leadTimeDetail = $this->lead_time_detail;

        return [
            'raw' => $leadTime,
            'days' => $leadTimeDetail['days'],
            'hours' => $leadTimeDetail['hours'],
            'formatted' => $leadTimeDetail['formatted'],
            'is_less_than_day' => $leadTime < 1,
            'bahan_baku_count' => $this->detailPembelian->count(),
            'bahan_baku_list' => $this->detailPembelian->map(function ($detail) {
                return [
                    'id' => $detail->bahanBaku->id,
                    'nama' => $detail->bahanBaku->nama,
                    'lead_time' => $detail->bahanBaku->lead_time,
                    'lead_time_max' => $detail->bahanBaku->lead_time_max
                ];
            })->toArray()
        ];
    }
}
