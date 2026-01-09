<?php

namespace App\Models;

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

    // Method baru untuk mendapatkan total penggunaan dari semua sumber
    public function getTotalPenggunaanPeriode($startDate = null, $endDate = null)
    {
        $total = 0;

        if (!$startDate) {
            $startDate = now()->subMonths(11)->startOfMonth();
        }

        if (!$endDate) {
            $endDate = now()->endOfMonth();
        }

        // 1. Penjualan langsung
        $penjualanLangsung = $this->detailPenjualan()
            ->whereHas('penjualan', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->sum('jumlah');

        $total += $penjualanLangsung;

        // 2. Penggunaan melalui produk yang dijual
        $penjualanProduk = DetailPenjualan::where('jenis_item', 'produk')
            ->whereHas('penjualan', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->with(['produk' => function ($query) {
                $query->with(['komposisi' => function ($q) {
                    $q->where('bahan_baku_id', $this->id);
                }]);
            }])
            ->get();

        foreach ($penjualanProduk as $detail) {
            if ($detail->produk && $detail->produk->komposisi) {
                foreach ($detail->produk->komposisi as $komposisi) {
                    if ($komposisi->bahan_baku_id == $this->id) {
                        $total += $komposisi->jumlah * $detail->jumlah;
                    }
                }
            }
        }

        return $total;
    }

    // Method untuk mendapatkan total penggunaan per hari
    public function getPenggunaanPerHari($rangeHari = 30)
    {
        $startDate = now()->subDays($rangeHari)->startOfDay();
        $endDate = now()->endOfDay();

        $dataPerHari = [];

        // Loop melalui setiap hari dalam rentang
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $hari = $currentDate->format('Y-m-d');
            $nextDay = (clone $currentDate)->addDay()->startOfDay();

            // Hitung total penggunaan untuk hari ini
            $totalHari = $this->getTotalPenggunaanPeriode($currentDate, $currentDate->copy()->endOfDay());

            $dataPerHari[$hari] = $totalHari;

            $currentDate = $nextDay;
        }

        return $dataPerHari;
    }

    public function hitungStatistikPenggunaan($rangeHari = 30)
    {
        // Ambil data penggunaan per hari
        $penggunaanPerHari = $this->getPenggunaanPerHari($rangeHari);

        // Filter hanya hari yang memiliki penggunaan
        $hariDenganPenggunaan = array_filter($penggunaanPerHari, function ($value) {
            return $value > 0;
        });

        // Hitung statistik
        $totalKeluar = array_sum($penggunaanPerHari);
        $hariAktif = count($hariDenganPenggunaan);
        $maksKeluar = max($penggunaanPerHari);

        // Hitung rata-rata berdasarkan hari aktif (bukan total hari)
        if ($hariAktif > 0) {
            $rataRata = $totalKeluar / $hariAktif;
        } else {
            $rataRata = 0;
        }

        return [
            'total_keluar' => $totalKeluar,
            'hari_aktif' => $hariAktif,
            'rata_rata' => round($rataRata, 2),
            'maks_keluar' => $maksKeluar,
            'range_hari' => $rangeHari,
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

        $T = $statistik['rata_rata']; // Rata-rata per hari
        $LT = max(1, $this->lead_time); // Lead time rata-rata
        $LT_max = max($LT, $this->lead_time_max); // Lead time maksimum
        $Maks = $statistik['maks_keluar']; // Permintaan maksimal per hari

        // Safety Stock: (Penjualan Maksimal Harian × Lead Time Maksimum) - (Penjualan Harian Rata-rata × Lead Time Rata-rata)
        $SS = max(0, ($Maks * $LT_max) - ($T * $LT));

        // Min Stock: (Penjualan Harian Rata-rata × Lead Time Rata-rata) + Safety Stock
        $Min = ($T * $LT) + $SS;

        // Max Stock: 2 * (rata-rata * lead time) + SS
        $Max = 2 * ($T * $LT) + $SS;

        // ROP: max - min
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
        // Cek dari semua sumber dalam 90 hari terakhir
        $totalPenggunaan = $this->getTotalPenggunaanPeriode(
            now()->subDays(90)->startOfDay(),
            now()->endOfDay()
        );

        return $totalPenggunaan > 0;
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
        if ($this->isPerluPembelian() && $this->rop > 0) {
            return $this->rop;
        }
        return 0;
    }

    public function totalNilaiPemesananRekomendasiRop()
    {
        $quantity = $this->jumlahPemesananRekomendasiRop();
        return $quantity * $this->harga_beli;
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
}
