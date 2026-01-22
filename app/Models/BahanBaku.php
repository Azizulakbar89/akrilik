<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        'lead_time' => 1,
        'lead_time_max' => 2
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

    protected $appends = [
        'status_ss_label',
        'status_ss_badge',
        'status_stok_bahan'
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

    public function hitungParameterStok()
    {
        $statistik = $this->hitungStatistikPenggunaan(30);

        if ($statistik['total_keluar'] == 0 || $statistik['rata_rata'] == 0) {
            return [
                'safety_stock' => 0,
                'min' => 0,
                'max' => 0,
                'rop' => 0,
                'statistik' => $statistik,
                'perhitungan' => [
                    'formula_ss' => 'Belum ada data penggunaan',
                    'formula_min' => 'Belum ada data penggunaan',
                    'formula_max' => 'Belum ada data penggunaan',
                    'formula_rop' => 'Belum ada data penggunaan'
                ]
            ];
        }

        $T = $statistik['rata_rata']; // Rata-rata penggunaan per hari
        $LT = max(1, $this->lead_time); // Lead time rata-rata dalam hari
        $LT_max = max($LT, $this->lead_time_max); // Lead time maksimum dalam hari
        $Maks = $statistik['maks_keluar']; // Permintaan maksimal per hari

        // Safety Stock: (Permintaan Maksimal Harian × Lead Time Maksimum) - (Permintaan Harian Rata-rata × Lead Time Rata-rata)
        $SS = max(0, ($Maks * $LT_max) - ($T * $LT));

        // Min Stock: (Permintaan Harian Rata-rata × Lead Time Rata-rata) + Safety Stock
        $Min = ($T * $LT) + $SS;

        // Max Stock: 2 * (rata-rata × lead time) + SS
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
                'rata_rata_harian' => round($T, 2),
                'lead_time_rata' => $LT,
                'lead_time_max' => $LT_max,
                'permintaan_maks' => $Maks,
                'formula_ss' => "({$Maks} × {$LT_max}) - (" . round($T, 2) . " × {$LT}) = " . round($SS, 0),
                'formula_min' => "(" . round($T, 2) . " × {$LT}) + " . round($SS, 0) . " = " . round($Min, 0),
                'formula_max' => "2 × (" . round($T, 2) . " × {$LT}) + " . round($SS, 0) . " = " . round($Max, 0),
                'formula_rop' => round($Max, 0) . " - " . round($Min, 0) . " = " . round($ROP, 0)
            ]
        ];
    }

    /**
     * Hitung dan update parameter stok sekaligus
     */
    public function hitungDanUpdateParameterStok()
    {
        $parameters = $this->hitungParameterStok();

        $oldValues = [
            'safety_stock' => $this->safety_stock,
            'min' => $this->min,
            'max' => $this->max,
            'rop' => $this->rop
        ];

        $this->update([
            'safety_stock' => $parameters['safety_stock'],
            'min' => $parameters['min'],
            'max' => $parameters['max'],
            'rop' => $parameters['rop']
        ]);

        return array_merge($parameters, [
            'safety_stock_old' => $oldValues['safety_stock'],
            'min_old' => $oldValues['min'],
            'max_old' => $oldValues['max'],
            'rop_old' => $oldValues['rop']
        ]);
    }

    /**
     * Method alias untuk updateParameterStok() - untuk kompatibilitas dengan controller
     */
    public function updateParameterStok()
    {
        return $this->hitungDanUpdateParameterStok();
    }

    /**
     * Update lead time dengan data actual dari pembelian
     */
    public function updateLeadTimeWithActual($actualLeadTime)
    {
        $oldAverage = $this->lead_time;
        $oldMax = $this->lead_time_max;

        // Hitung lead time rata-rata baru
        // Rumus: (lead_time_lama + actual_lead_time) / 2
        $newAverage = ($oldAverage + $actualLeadTime) / 2;

        // Update lead time maksimum
        $newMax = max($oldMax, $actualLeadTime);

        // Simpan ke database
        $this->lead_time = round($newAverage, 1);
        $this->lead_time_max = round($newMax, 1);
        $this->save();

        return [
            'old_average' => $oldAverage,
            'new_average' => round($newAverage, 1),
            'old_max' => $oldMax,
            'new_max' => round($newMax, 1),
            'actual_lead_time' => $actualLeadTime
        ];
    }

    /**
     * Method untuk mendapatkan total penggunaan dari semua sumber
     */
    public function getTotalPenggunaanPeriode($startDate = null, $endDate = null)
    {
        $total = 0;

        if (!$startDate) {
            $startDate = now()->subMonths(11)->startOfMonth();
        }

        if (!$endDate) {
            $endDate = now()->endOfMonth();
        }

        $penjualanLangsung = $this->detailPenjualan()
            ->whereHas('penjualan', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->sum('jumlah');

        $total += $penjualanLangsung;

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

    /**
     * Method untuk mendapatkan jumlah transaksi dalam periode
     */
    public function getJumlahTransaksiPeriode($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = now()->subMonths(11)->startOfMonth();
        }

        if (!$endDate) {
            $endDate = now()->endOfMonth();
        }

        // Hitung jumlah transaksi yang menggunakan bahan baku ini
        $jumlahTransaksi = 0;

        // 1. Transaksi penjualan langsung
        $transaksiLangsung = $this->detailPenjualan()
            ->whereHas('penjualan', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->distinct('penjualan_id')
            ->count('penjualan_id');

        $jumlahTransaksi += $transaksiLangsung;

        // 2. Transaksi melalui produk
        $transaksiProduk = DetailPenjualan::where('jenis_item', 'produk')
            ->whereHas('penjualan', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->whereHas('produk.komposisi', function ($query) {
                $query->where('bahan_baku_id', $this->id);
            })
            ->distinct('penjualan_id')
            ->count('penjualan_id');

        $jumlahTransaksi += $transaksiProduk;

        return $jumlahTransaksi;
    }

    /**
     * Method untuk mendapatkan total penggunaan per hari
     */
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

    /**
     * Method untuk mendapatkan jumlah transaksi per hari
     */
    public function getTransaksiPerHari($rangeHari = 30)
    {
        $startDate = now()->subDays($rangeHari)->startOfDay();
        $endDate = now()->endOfDay();

        $transaksiPerHari = [];

        // Loop melalui setiap hari dalam rentang
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $hari = $currentDate->format('Y-m-d');
            $nextDay = (clone $currentDate)->addDay()->startOfDay();

            // Hitung jumlah transaksi untuk hari ini
            $jumlahTransaksiHari = $this->getJumlahTransaksiPeriode($currentDate, $currentDate->copy()->endOfDay());

            $transaksiPerHari[$hari] = $jumlahTransaksiHari;

            $currentDate = $nextDay;
        }

        return $transaksiPerHari;
    }

    /**
     * Hitung statistik penggunaan dengan rata-rata per transaksi yang benar
     * Rata-rata = Total penggunaan / Jumlah transaksi
     */
    public function hitungStatistikPenggunaan($rangeHari = 30)
    {
        $startDate = now()->subDays($rangeHari)->startOfDay();
        $endDate = now()->endOfDay();

        // Hitung total penggunaan dalam periode
        $totalPenggunaan = $this->getTotalPenggunaanPeriode($startDate, $endDate);

        // Hitung jumlah transaksi dalam periode
        $jumlahTransaksi = $this->getJumlahTransaksiPeriode($startDate, $endDate);

        // Ambil data penggunaan per hari untuk perhitungan maksimum
        $penggunaanPerHari = $this->getPenggunaanPerHari($rangeHari);

        // Hitung maksimum penggunaan harian
        $maksKeluar = max($penggunaanPerHari);

        // Hitung hari dengan penggunaan > 0
        $hariDenganPenggunaan = array_filter($penggunaanPerHari, function ($value) {
            return $value > 0;
        });
        $hariAktif = count($hariDenganPenggunaan);

        if ($jumlahTransaksi > 0) {
            $rataRata = $totalPenggunaan / $jumlahTransaksi;
        } else {
            $rataRata = 0;
        }

        return [
            'total_keluar' => $totalPenggunaan,
            'jumlah_transaksi' => $jumlahTransaksi,
            'hari_aktif' => $hariAktif,
            'rata_rata' => round($rataRata, 2),
            'maks_keluar' => $maksKeluar,
            'range_hari' => $rangeHari,
            'penggunaan_per_hari' => $penggunaanPerHari
        ];
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

    /**
     * Cek apakah bahan baku perlu pembelian (stok <= min)
     */
    public function isPerluPembelian()
    {
        return $this->stok <= $this->min && $this->min > 0;
    }

    /**
     * Cek apakah stok tidak aman (stok <= safety_stock)
     */
    public function isStokTidakAman()
    {
        return $this->stok <= $this->safety_stock && $this->safety_stock > 0;
    }

    /**
     * Cek apakah bahan baku perlu dibeli (untuk pembelian cepat)
     * Menggabungkan kedua kondisi: stok <= min ATAU stok <= safety_stock
     */
    public function isPerluDibeli()
    {
        return ($this->stok <= $this->min || $this->stok <= $this->safety_stock)
            && ($this->min > 0 || $this->safety_stock > 0);
    }

    /**
     * Cek status berdasarkan safety stock
     * Stok < SS = Perlu Pembelian
     * Stok > SS = Aman
     */
    public function isStokAmanSS()
    {
        if ($this->safety_stock <= 0) {
            return true; // Jika SS belum diatur, dianggap aman
        }
        return $this->stok > $this->safety_stock;
    }

    /**
     * Cek apakah stok mencukupi untuk jumlah tertentu
     */
    public function isStokCukup($jumlah)
    {
        return $this->stok >= $jumlah;
    }

    /**
     * Get label status berdasarkan safety stock
     */
    public function getStatusSsLabelAttribute()
    {
        if ($this->safety_stock <= 0) {
            return 'Belum diatur SS';
        }

        if ($this->stok <= $this->safety_stock) {
            return 'Perlu Pembelian';
        } else {
            return 'Aman';
        }
    }

    /**
     * Get status stok untuk bahan baku (untuk dropdown)
     */
    public function getStatusStokBahanAttribute()
    {
        if ($this->stok <= 0) {
            return 'Stok Habis';
        } elseif ($this->stok <= $this->safety_stock && $this->safety_stock > 0) {
            return 'Perlu Pembelian (≤ SS)';
        } elseif ($this->safety_stock <= 0) {
            return 'Aman (SS belum diatur)';
        } else {
            return 'Aman (> SS)';
        }
    }

    /**
     * Get badge HTML untuk status SS
     */
    public function getStatusSsBadgeAttribute()
    {
        if ($this->safety_stock <= 0) {
            return '<span class="badge badge-secondary">Belum diatur SS</span>';
        }

        if ($this->stok <= $this->safety_stock) {
            return '<span class="badge badge-danger">Perlu Pembelian (≤ SS)</span>';
        } else {
            return '<span class="badge badge-success">Aman (> SS)</span>';
        }
    }

    /**
     * Get badge HTML untuk status stok bahan baku
     */
    public function getStatusStokBadgeAttribute()
    {
        if ($this->stok <= 0) {
            return '<span class="badge badge-danger">Stok Habis</span>';
        } elseif ($this->stok <= $this->safety_stock && $this->safety_stock > 0) {
            return '<span class="badge badge-warning">Perlu Pembelian (≤ SS)</span>';
        } elseif ($this->safety_stock <= 0) {
            return '<span class="badge badge-info">Aman (SS belum diatur)</span>';
        } else {
            return '<span class="badge badge-success">Aman (> SS)</span>';
        }
    }

    /**
     * Get status stok dalam bentuk HTML
     */
    public function getStatusStokAttribute()
    {
        if ($this->stok <= $this->min && $this->min > 0) {
            return '<span class="badge badge-danger">Kritis (≤ Min)</span>';
        } elseif ($this->stok <= $this->safety_stock && $this->safety_stock > 0) {
            return '<span class="badge badge-warning">Tidak Aman (≤ Safety Stock)</span>';
        } else {
            return '<span class="badge badge-success">Aman</span>';
        }
    }

    /**
     * Jumlah rekomendasi pembelian berdasarkan ROP
     * Mengembalikan ROP (max - min) jika stok ≤ min
     */
    public function jumlahPemesananRekomendasiRop()
    {
        if ($this->isPerluPembelian() && $this->rop > 0) {
            return $this->rop;
        }
        return 0;
    }

    /**
     * Total nilai pembelian rekomendasi
     */
    public function totalNilaiPemesananRekomendasiRop()
    {
        $quantity = $this->jumlahPemesananRekomendasiRop();
        return $quantity * $this->harga_beli;
    }

    /**
     * Scope untuk bahan baku yang perlu pembelian (stok <= min)
     */
    public function scopePerluPembelian($query)
    {
        return $query->whereColumn('stok', '<=', 'min')
            ->where('min', '>', 0);
    }

    /**
     * Scope untuk stok tidak aman (stok <= safety_stock)
     */
    public function scopeStokTidakAman($query)
    {
        return $query->whereColumn('stok', '<=', 'safety_stock')
            ->where('safety_stock', '>', 0);
    }

    /**
     * Scope untuk semua bahan baku yang perlu dibeli (pembelian cepat)
     */
    public function scopePerluDibeli($query)
    {
        return $query->where(function ($q) {
            $q->whereColumn('stok', '<=', 'min')
                ->orWhereColumn('stok', '<=', 'safety_stock');
        })
            ->where(function ($q) {
                $q->where('min', '>', 0)
                    ->orWhere('safety_stock', '>', 0);
            });
    }

    /**
     * Scope untuk bahan baku yang perlu pembelian berdasarkan SS
     */
    public function scopePerluPembelianSS($query)
    {
        return $query->whereColumn('stok', '<=', 'safety_stock')
            ->where('safety_stock', '>', 0);
    }

    /**
     * Scope untuk bahan baku yang aman berdasarkan SS
     */
    public function scopeAmanSS($query)
    {
        return $query->whereColumn('stok', '>', 'safety_stock')
            ->where('safety_stock', '>', 0);
    }

    /**
     * Get URL foto
     */
    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return Storage::disk('public')->url($this->foto);
        }
        return asset('vendors/images/default-bahan-baku.jpg');
    }

    /**
     * Format harga beli
     */
    public function getHargaBeliFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga_beli, 0, ',', '.');
    }

    /**
     * Format harga jual
     */
    public function getHargaJualFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga_jual, 0, ',', '.');
    }

    /**
     * Format lead time
     */
    public function getLeadTimeFormattedAttribute()
    {
        return $this->lead_time . ' hari';
    }

    public function getLeadTimeMaxFormattedAttribute()
    {
        return $this->lead_time_max . ' hari';
    }

    /**
     * Get rekomendasi pembelian ROP untuk bahan baku ini
     */
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
                'lead_time' => $this->lead_time_formatted,
                'lead_time_max' => $this->lead_time_max_formatted,
                'lead_time_days' => $this->lead_time,
                'lead_time_max_days' => $this->lead_time_max,
                'perlu_pembelian' => true
            ];
        }
        return null;
    }

    /**
     * Get data untuk pembelian cepat (semua bahan baku yang perlu dibeli)
     */
    public function getDataPembelianCepatAttribute()
    {
        if ($this->isPerluDibeli()) {
            $jumlahRekomendasi = $this->jumlahPemesananRekomendasiRop();

            // Jika tidak ada rekomendasi dari ROP, gunakan safety stock atau min
            if ($jumlahRekomendasi <= 0) {
                if ($this->safety_stock > 0) {
                    $jumlahRekomendasi = $this->safety_stock;
                } elseif ($this->min > 0) {
                    $jumlahRekomendasi = $this->min;
                }
            }

            return [
                'bahan_baku_id' => $this->id,
                'nama' => $this->nama,
                'stok' => $this->stok,
                'min' => $this->min,
                'max' => $this->max,
                'rop' => $this->rop,
                'safety_stock' => $this->safety_stock,
                'status_ss' => $this->status_ss_label,
                'status_stok_bahan' => $this->status_stok_bahan,
                'jumlah_rekomendasi' => $jumlahRekomendasi,
                'harga_beli' => $this->harga_beli,
                'satuan' => $this->satuan,
                'lead_time' => $this->lead_time_formatted,
                'lead_time_max' => $this->lead_time_max_formatted,
                'status_stok' => $this->stok <= $this->min ? 'Kritis' : ($this->stok <= $this->safety_stock ? 'Tidak Aman' : 'Aman'),
                'perlu_pembelian' => true,
                'total_nilai' => $jumlahRekomendasi * $this->harga_beli
            ];
        }
        return null;
    }

    /**
     * Get semua bahan baku yang perlu dibeli untuk pembelian cepat
     */
    public static function getAllUntukPembelianCepat()
    {
        return self::perluDibeli()
            ->get()
            ->map(function ($bahan) {
                return $bahan->data_pembelian_cepat;
            })
            ->filter()
            ->values();
    }

    /**
     * Update stok setelah pembelian
     */
    public function tambahStok($jumlah)
    {
        $this->stok += $jumlah;
        $this->save();

        return $this;
    }

    /**
     * Kurangi stok setelah penggunaan
     */
    public function kurangiStok($jumlah)
    {
        if ($this->stok >= $jumlah) {
            $this->stok -= $jumlah;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Get informasi lengkap untuk form penjualan
     */
    public function getInfoForPenjualan()
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'stok' => $this->stok,
            'harga_jual' => $this->harga_jual,
            'satuan' => $this->satuan,
            'safety_stock' => $this->safety_stock,
            'status_ss' => $this->status_ss_label,
            'status_ss_badge' => $this->status_ss_badge,
            'status_stok_badge' => $this->status_stok_badge,
            'status_stok_bahan' => $this->status_stok_bahan,
            'perlu_pembelian' => !$this->isStokAmanSS(),
            'stok_cukup' => $this->stok > 0,
            'stok_habis' => $this->stok <= 0
        ];
    }
}
