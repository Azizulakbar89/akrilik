<?php

namespace App\Models;

use App\Models\PenggunaanBahanBaku;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_baku';
    protected $fillable = [
        'nama',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok',
        'safety_stock',
        'rop',
        'min',
        'max',
        'lead_time',
        'lead_time_max',
        'foto'
    ];

    protected $attributes = [
        'safety_stock' => 0,
        'rop' => 0,
        'min' => 0,
        'max' => 0,
        'stok' => 0,
        'lead_time_max' => 1
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok' => 'integer',
        'safety_stock' => 'integer',
        'rop' => 'integer',
        'min' => 'integer',
        'max' => 'integer',
        'lead_time' => 'integer',
        'lead_time_max' => 'integer'
    ];

    public function penggunaan()
    {
        return $this->hasMany(PenggunaanBahanBaku::class, 'bahan_baku_id');
    }

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'bahan_baku_id');
    }

    public function komposisi()
    {
        return $this->hasMany(KomposisiBahanBaku::class, 'bahan_baku_id');
    }

    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelian::class, 'bahan_baku_id');
    }

    public function hitungStatistikPenggunaan($rangeHari = 30)
    {
        $startDate = now()->subDays($rangeHari)->startOfDay();

        $penggunaanData = collect();

        $penggunaanBahanBaku = $this->penggunaan()
            ->where('created_at', '>=', $startDate)
            ->where('jumlah', '>', 0)
            ->get();

        $penggunaanData = $penggunaanData->merge($penggunaanBahanBaku);

        $penjualanLangsung = $this->detailPenjualan()
            ->whereHas('penjualan', function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            })
            ->where('jumlah', '>', 0)
            ->get();

        $penggunaanData = $penggunaanData->merge($penjualanLangsung);

        if ($penggunaanData->isEmpty()) {
            return [
                'total_keluar' => 0,
                'count_keluar' => 0,
                'rata_rata' => 0,
                'maks_keluar' => 0,
                'range_hari' => $rangeHari,
                'hari_aktif' => 0,
                'sumber_data' => 'Tidak ada data'
            ];
        }

        $penggunaanPerHari = $penggunaanData->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($items) {
            return $items->sum('jumlah');
        });

        $totalKeluar = $penggunaanPerHari->sum();
        $countKeluar = $penggunaanData->count();
        $hariDenganTransaksi = $penggunaanPerHari->count();
        $hariAktif = max(1, $hariDenganTransaksi);
        $rataRata = $totalKeluar / $hariAktif;
        $maksKeluar = $penggunaanPerHari->max();

        return [
            'total_keluar' => $totalKeluar,
            'count_keluar' => $countKeluar,
            'rata_rata' => max(0, round($rataRata, 2)),
            'maks_keluar' => max(0, $maksKeluar),
            'range_hari' => $rangeHari,
            'hari_aktif' => $hariAktif,
            'sumber_data' => 'Penggunaan & Penjualan',
            'penggunaan_per_hari' => $penggunaanPerHari
        ];
    }

    public function hitungParameterStok()
    {
        $statistik = $this->hitungStatistikPenggunaan(30);

        if ($statistik['total_keluar'] == 0 || $statistik['rata_rata'] == 0) {
            return [
                'safety_stock' => 0,
                'min' => 0,
                'max' => 0,
                'rop' => 0,
                'statistik' => $statistik
            ];
        }

        $T = $statistik['rata_rata'];
        $LT = max(1, $this->lead_time);
        $LT_max = max($LT, $this->lead_time_max);
        $Maks = $statistik['maks_keluar'];

        $SS = max(0, ($Maks * $LT_max) - ($T * $LT));
        $Min = ($T * $LT) + $SS;
        $Max = 2 * ($T * $LT) + $SS;
        $ROP = $Max - $Min;

        return [
            'safety_stock' => (int) round($SS),
            'min' => (int) round($Min),
            'max' => (int) round($Max),
            'rop' => (int) round($ROP),
            'statistik' => $statistik,
            'perhitungan' => [
                'formula_ss' => "($Maks × $LT_max) - ($T × $LT) = $SS",
                'formula_min' => "($T × $LT) + $SS = $Min",
                'formula_max' => "2 × ($T × $LT) + $SS = $Max",
                'formula_rop' => "$Max - $Min = $ROP"
            ]
        ];
    }

    public function updateParameterStok()
    {
        $parameters = $this->hitungParameterStok();

        $this->update([
            'safety_stock' => $parameters['safety_stock'],
            'min' => $parameters['min'],
            'max' => $parameters['max'],
            'rop' => $parameters['rop']
        ]);

        return $parameters;
    }

    public function sudahAdaPenggunaan()
    {
        $adaPenggunaan = $this->penggunaan()->where('jumlah', '>', 0)->exists();

        if (!$adaPenggunaan) {
            $adaPenggunaan = $this->detailPenjualan()->where('jumlah', '>', 0)->exists();
        }

        return $adaPenggunaan;
    }

    public function isPerluPembelian()
    {
        return $this->stok <= $this->min;
    }

    public function isStokTidakAman()
    {
        return $this->stok <= $this->safety_stock;
    }

    public function getStatusStokAttribute()
    {
        if ($this->stok <= $this->min) {
            return '<span class="badge badge-danger">Perlu Pembelian</span>';
        } elseif ($this->stok <= $this->safety_stock) {
            return '<span class="badge badge-warning">Stok Menipis</span>';
        } else {
            return '<span class="badge badge-success">Aman</span>';
        }
    }

    public function jumlahPemesananRekomendasiRop()
    {
        if ($this->isPerluPembelian() && $this->max > 0 && $this->rop > 0) {
            // Jika stok ≤ min, beli sesuai ROP (ROP = Max - Min)
            return $this->rop;
        }
        return 0;
    }

    public function totalNilaiPemesananRekomendasiRop()
    {
        $quantity = $this->jumlahPemesananRekomendasiRop();
        return $quantity * $this->harga_beli;
    }

    // Metode lama untuk kompatibilitas
    public function jumlahPemesananRekomendasi()
    {
        return $this->jumlahPemesananRekomendasiRop();
    }

    public function totalNilaiPemesananRekomendasi()
    {
        return $this->totalNilaiPemesananRekomendasiRop();
    }

    public function scopePerluPembelian($query)
    {
        return $query->whereColumn('stok', '<=', 'min')
            ->where('min', '>', 0);
    }

    public function scopeStokTidakAman($query)
    {
        return $query->whereColumn('stok', '<=', 'safety_stock')
            ->where('safety_stock', '>', 0);
    }

    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return Storage::disk('public')->url($this->foto);
        }
        return asset('vendors/images/default-bahan-baku.jpg');
    }

    public function getHargaBeliFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga_beli, 0, ',', '.');
    }

    public function getHargaJualFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga_jual, 0, ',', '.');
    }

    public function getRekomendasiPembelianRopAttribute()
    {
        if ($this->isPerluPembelian()) {
            return [
                'bahan_baku_id' => $this->id,
                'nama' => $this->nama,
                'stok_sekarang' => $this->stok,
                'min' => $this->min,
                'max' => $this->max,
                'rop' => $this->rop,
                'jumlah_rekomendasi' => $this->jumlahPemesananRekomendasiRop(),
                'harga_beli' => $this->harga_beli,
                'total_nilai' => $this->totalNilaiPemesananRekomendasiRop(),
                'satuan' => $this->satuan,
                'perlu_pembelian' => true
            ];
        }
        return null;
    }

    // Untuk kompatibilitas
    public function getRekomendasiPembelianAttribute()
    {
        return $this->getRekomendasiPembelianRopAttribute();
    }

    public function tambahStok($jumlah)
    {
        $this->stok += $jumlah;
        $this->save();

        if ($this->sudahAdaPenggunaan()) {
            $this->updateParameterStok();
        }

        return $this;
    }

    public function kurangiStok($jumlah)
    {
        if ($this->stok < $jumlah) {
            throw new \Exception("Stok bahan baku {$this->nama} tidak mencukupi");
        }

        $this->stok -= $jumlah;
        $this->save();

        if ($this->sudahAdaPenggunaan()) {
            $this->updateParameterStok();
        }

        return $this;
    }

    public function kembalikanStok($jumlah)
    {
        $this->stok += $jumlah;
        $this->save();

        if ($this->sudahAdaPenggunaan()) {
            $this->updateParameterStok();
        }
    }

    public function scopeWithPenggunaanBulanan($query, $year = null, $month = null)
    {
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');

        return $query->with(['detailPenjualan' => function ($q) use ($year, $month) {
            $q->whereHas('penjualan', function ($q2) use ($year, $month) {
                $q2->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month);
            });
        }]);
    }
}
