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

    // Method untuk menghitung statistik penggunaan 30 hari terakhir
    public function hitungStatistikPenggunaan($rangeHari = 30)
    {
        $startDate = now()->subDays($rangeHari)->startOfDay();

        // Gabungkan data penggunaan dari semua sumber
        $penggunaanData = collect();

        // 1. Data dari tabel penggunaan_bahan_baku (baik positif maupun negatif)
        $penggunaanBahanBaku = $this->penggunaan()
            ->where('created_at', '>=', $startDate)
            ->where('jumlah', '>', 0) // Hanya ambil yang positif (penggunaan)
            ->get();

        $penggunaanData = $penggunaanData->merge($penggunaanBahanBaku);

        // 2. Data dari detail penjualan bahan baku langsung
        $penjualanLangsung = $this->detailPenjualan()
            ->whereHas('penjualan', function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            })
            ->where('jumlah', '>', 0)
            ->get();

        $penggunaanData = $penggunaanData->merge($penjualanLangsung);

        // Jika belum ada data penggunaan sama sekali, return default 0
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

        // Kelompokkan data per hari untuk menghitung penjualan harian
        $penggunaanPerHari = $penggunaanData->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($items) {
            return $items->sum('jumlah');
        });

        $totalKeluar = $penggunaanPerHari->sum();
        $countKeluar = $penggunaanData->count();

        // Hitung jumlah hari aktual dengan transaksi
        $hariDenganTransaksi = $penggunaanPerHari->count();
        $hariAktif = max(1, $hariDenganTransaksi); // Minimal 1 hari

        // Rata-rata per hari = total keluar / jumlah hari dengan transaksi
        $rataRata = $totalKeluar / $hariAktif;

        // Maksimum per hari = nilai tertinggi dari penjumlahan per hari
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

        // Jika belum ada data penggunaan atau rata-rata 0, return semua 0
        if ($statistik['total_keluar'] == 0 || $statistik['rata_rata'] == 0) {
            return [
                'safety_stock' => 0,
                'min' => 0,
                'max' => 0,
                'rop' => 0,
                'statistik' => $statistik
            ];
        }

        $T = $statistik['rata_rata']; // Penggunaan rata-rata per hari
        $LT = max(1, $this->lead_time); // Lead time rata-rata dalam hari (minimal 1)
        $LT_max = max($LT, $this->lead_time_max); // Lead time maksimum dalam hari
        $Maks = $statistik['maks_keluar']; // Penggunaan maksimum per hari

        // Safety Stock = (Penjualan Maksimal Harian × Lead Time Maksimum) - (Penjualan Harian Rata-rata × Lead Time Rata-rata)
        $SS = max(0, ($Maks * $LT_max) - ($T * $LT));

        // Minimal Stock = (Rata-rata × Lead Time) + Safety Stock
        $Min = ($T * $LT) + $SS;

        // Maksimal Stock = 2 * (Rata-rata × Lead Time) + Safety Stock
        $Max = 2 * ($T * $LT) + $SS;

        // Reorder Point = Minimal Stock
        $ROP = $Min;

        // Bulatkan semua nilai ke integer
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
                'formula_rop' => "$Min = $ROP"
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
        // Cek di tabel penggunaan_bahan_baku
        $adaPenggunaan = $this->penggunaan()->where('jumlah', '>', 0)->exists();

        // Jika tidak ada, cek di detail penjualan
        if (!$adaPenggunaan) {
            $adaPenggunaan = $this->detailPenjualan()->where('jumlah', '>', 0)->exists();
        }

        return $adaPenggunaan;
    }

    public function isPerluPembelian()
    {
        return $this->min > 0 && $this->stok <= $this->min;
    }

    public function isStokTidakAman()
    {
        return $this->safety_stock > 0 && $this->stok <= $this->safety_stock;
    }

    public function getStatusStokAttribute()
    {
        if ($this->min > 0 && $this->stok <= $this->min) {
            return '<span class="badge badge-danger">Perlu Pembelian</span>';
        } elseif ($this->safety_stock > 0 && $this->stok <= $this->safety_stock) {
            return '<span class="badge badge-warning">Stok Menipis</span>';
        } else {
            return '<span class="badge badge-success">Aman</span>';
        }
    }

    public function jumlahPemesananRekomendasi()
    {
        if ($this->isPerluPembelian() && $this->max > 0) {
            $quantity = $this->max - $this->stok;
            return max(0, $quantity);
        }
        return 0;
    }

    public function totalNilaiPemesananRekomendasi()
    {
        $quantity = $this->jumlahPemesananRekomendasi();
        return $quantity * $this->harga_beli;
    }

    public function scopePerluPembelian($query)
    {
        return $query->where(function ($q) {
            $q->whereColumn('stok', '<=', 'min')
                ->where('min', '>', 0);
        });
    }

    // Perbaikan: Scope yang benar untuk stok tidak aman
    public function scopeStokTidakAman($query)
    {
        return $query->where(function ($q) {
            $q->whereColumn('stok', '<=', 'safety_stock')
                ->where('safety_stock', '>', 0);
        });
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

    public function getRekomendasiPembelianAttribute()
    {
        if ($this->isPerluPembelian()) {
            return [
                'bahan_baku_id' => $this->id,
                'nama' => $this->nama,
                'stok_sekarang' => $this->stok,
                'min' => $this->min,
                'max' => $this->max,
                'jumlah_rekomendasi' => $this->jumlahPemesananRekomendasi(),
                'harga_beli' => $this->harga_beli,
                'total_nilai' => $this->totalNilaiPemesananRekomendasi(),
                'satuan' => $this->satuan
            ];
        }
        return null;
    }

    // Method untuk menambah stok dari pembelian
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
}
