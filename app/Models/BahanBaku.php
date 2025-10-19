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
        'lead_time',
        'safety_stock',
        'rop',
        'min',
        'max',
        'foto'
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok' => 'integer',
        'lead_time' => 'integer',
        'safety_stock' => 'integer',
        'rop' => 'integer',
        'min' => 'integer',
        'max' => 'integer'
    ];

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

    public function isPerluPembelian()
    {
        return $this->stok <= $this->min;
    }

    public function isStokTidakAman()
    {
        return $this->stok <= $this->min;
    }

    public function getStatusStokAttribute()
    {
        if ($this->stok <= $this->min) {
            return '<span class="badge badge-danger">Stok Tidak Aman</span>';
        } elseif ($this->stok <= $this->safety_stock) {
            return '<span class="badge badge-warning">Stok Menipis</span>';
        } else {
            return '<span class="badge badge-success">Stok Aman</span>';
        }
    }

    public function jumlahPemesananRekomendasi()
    {
        if ($this->isPerluPembelian()) {
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
        return $query->whereColumn('stok', '<=', 'min');
    }

    public function scopeStokTidakAman($query)
    {
        return $query->whereColumn('stok', '<=', 'min');
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

    public function jualSebagaiProduk($jumlah)
    {
        if ($this->stok < $jumlah) {
            throw new \Exception("Stok bahan baku {$this->nama} tidak mencukupi");
        }

        $this->stok -= $jumlah;
        $this->save();

        return $this->harga_jual * $jumlah;
    }

    public function kembalikanStok($jumlah)
    {
        $this->stok += $jumlah;
        $this->save();
    }

    // Method untuk menghitung safety stock, min, max, rop secara otomatis
    public function hitungParameterStok()
    {
        // Ambil data penjualan 1 bulan terakhir
        $satuBulanLalu = now()->subMonth();

        $dataPenjualan = $this->detailPenjualan()
            ->whereHas('penjualan', function ($query) use ($satuBulanLalu) {
                $query->where('created_at', '>=', $satuBulanLalu);
            })
            ->selectRaw('SUM(jumlah) as total_keluar, COUNT(DISTINCT DATE(created_at)) as count_hari')
            ->first();

        $totalKeluar = $dataPenjualan->total_keluar ?? 0;
        $countHari = $dataPenjualan->count_hari ?: 1; // Minimal 1 hari untuk menghindari division by zero

        // Hitung penggunaan rata-rata per hari
        $penggunaanRataRata = $totalKeluar / $countHari;

        // Hitung penggunaan rata-rata per periode (dalam contoh: per hari)
        $T = round($penggunaanRataRata);

        // Ambil lead time (dalam hari)
        $LT = $this->lead_time ?: 2; // Default 2 hari jika tidak diisi

        // Hitung pemakaian maksimum (ambil dari data historis)
        $pemakaianMaksimum = $this->detailPenjualan()
            ->whereHas('penjualan', function ($query) use ($satuBulanLalu) {
                $query->where('created_at', '>=', $satuBulanLalu);
            })
            ->selectRaw('DATE(created_at) as tanggal, SUM(jumlah) as total_harian')
            ->groupBy('tanggal')
            ->orderByDesc('total_harian')
            ->value('total_harian') ?? ($T * 2); // Default 2x rata-rata jika tidak ada data

        // Hitung Safety Stock
        $SS = ($pemakaianMaksimum - $T) * $LT;
        $SS = max(0, round($SS)); // Pastikan tidak negatif

        // Hitung Minimal Stock
        $minStock = ($T * $LT) + $SS;

        // Hitung Maksimal Stock
        $maxStock = 2 * ($T * $LT) + $SS;

        // Hitung Reorder Point
        $ROP = $maxStock - $minStock;

        // Update data ke model
        $this->update([
            'safety_stock' => $SS,
            'min' => $minStock,
            'max' => $maxStock,
            'rop' => $ROP
        ]);

        return [
            'safety_stock' => $SS,
            'min' => $minStock,
            'max' => $maxStock,
            'rop' => $ROP,
            'penggunaan_rata_rata' => $T,
            'pemakaian_maksimum' => $pemakaianMaksimum,
            'lead_time' => $LT
        ];
    }

    // Method untuk mendapatkan statistik penggunaan
    public function getStatistikPenggunaan()
    {
        $satuBulanLalu = now()->subMonth();

        $statistik = $this->detailPenjualan()
            ->whereHas('penjualan', function ($query) use ($satuBulanLalu) {
                $query->where('created_at', '>=', $satuBulanLalu);
            })
            ->selectRaw('
                SUM(jumlah) as total_keluar,
                COUNT(DISTINCT DATE(created_at)) as count_hari,
                MAX(jumlah) as max_keluar_satu_kali,
                AVG(jumlah) as avg_keluar_per_transaksi
            ')
            ->first();

        return [
            'total_keluar' => $statistik->total_keluar ?? 0,
            'count_hari' => $statistik->count_hari ?? 0,
            'max_keluar_satu_kali' => $statistik->max_keluar_satu_kali ?? 0,
            'avg_keluar_per_transaksi' => $statistik->avg_keluar_per_transaksi ?? 0,
            'penggunaan_rata_rata_per_hari' => $statistik->total_keluar / max($statistik->count_hari, 1)
        ];
    }
}
