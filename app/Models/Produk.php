<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $fillable = [
        'nama',
        'satuan',
        'harga',
        'foto'
    ];

    protected $casts = [
        'harga' => 'decimal:2'
    ];

    protected $appends = [
        'harga_formatted',
        'status_bahan_baku_badge',
        'status_bisa_diproduksi',
        'status_bahan_baku_label'
    ];

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'produk_id');
    }

    public function komposisi()
    {
        return $this->hasMany(KomposisiBahanBaku::class, 'produk_id');
    }

    public function getHargaFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    /**
     * Cek apakah produk bisa diproduksi untuk jumlah tertentu
     */
    public function bisaDiproduksi($jumlah)
    {
        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;
            $jumlahDibutuhkan = $komposisi->jumlah * $jumlah;

            if ($bahanBaku->stok < $jumlahDibutuhkan) {
                return false;
            }
        }
        return true;
    }

    /**
     * Cek apakah produk bisa diproduksi minimal 1 unit
     */
    public function bisaDiproduksiSatuUnit()
    {
        return $this->bisaDiproduksi(1);
    }

    /**
     * Cek bahan baku yang tidak cukup untuk jumlah tertentu
     */
    public function bahanBakuYangTidakCukup($jumlah)
    {
        $bahanTidakCukup = [];

        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;
            $jumlahDibutuhkan = $komposisi->jumlah * $jumlah;

            if ($bahanBaku->stok < $jumlahDibutuhkan) {
                $kekurangan = $jumlahDibutuhkan - $bahanBaku->stok;
                $bahanTidakCukup[] = [
                    'nama' => $bahanBaku->nama,
                    'stok_tersedia' => $bahanBaku->stok,
                    'dibutuhkan' => $jumlahDibutuhkan,
                    'kekurangan' => $kekurangan,
                    'satuan' => $bahanBaku->satuan
                ];
            }
        }

        return $bahanTidakCukup;
    }

    /**
     * Proses penjualan produk
     */
    public function prosesPenjualan($jumlah)
    {
        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;
            $jumlahDibutuhkan = $komposisi->jumlah * $jumlah;

            $bahanBaku->kurangiStok($jumlahDibutuhkan);

            // Catat penggunaan bahan baku
            PenggunaanBahanBaku::create([
                'bahan_baku_id' => $bahanBaku->id,
                'jumlah' => $jumlahDibutuhkan,
                'tanggal' => now(),
                'keterangan' => 'Produksi produk: ' . $this->nama
            ]);

            // Update parameter stok bahan baku
            $bahanBaku->updateParameterStok();
        }
    }

    /**
     * Batalkan penjualan produk
     */
    public function batalkanPenjualan($jumlah)
    {
        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;
            $jumlahDibutuhkan = $komposisi->jumlah * $jumlah;

            $bahanBaku->tambahStok($jumlahDibutuhkan);

            // Catat pengembalian bahan baku
            PenggunaanBahanBaku::create([
                'bahan_baku_id' => $bahanBaku->id,
                'jumlah' => -$jumlahDibutuhkan,
                'tanggal' => now(),
                'keterangan' => 'Pembatalan produk: ' . $this->nama
            ]);

            // Update parameter stok bahan baku
            $bahanBaku->updateParameterStok();
        }
    }

    /**
     * Get badge status bahan baku untuk produk
     */
    public function getStatusBahanBakuBadgeAttribute()
    {
        $status = [];
        $warna = 'success'; // default aman
        $bisaDiproduksi = true;

        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;

            // Cek apakah stok cukup untuk 1 unit
            if ($bahanBaku->stok < $komposisi->jumlah) {
                $status[] = $bahanBaku->nama . ': Stok tidak cukup';
                $warna = 'danger';
                $bisaDiproduksi = false;
            } elseif ($bahanBaku->safety_stock > 0 && $bahanBaku->stok <= $bahanBaku->safety_stock) {
                $status[] = $bahanBaku->nama . ': Perlu Pembelian (≤ SS)';
                if ($warna !== 'danger') $warna = 'warning';
            } elseif ($bahanBaku->safety_stock <= 0) {
                $status[] = $bahanBaku->nama . ': Belum diatur SS';
                if ($warna !== 'danger' && $warna !== 'warning') $warna = 'info';
            } else {
                $status[] = $bahanBaku->nama . ': Aman (> SS)';
            }
        }

        if (empty($status)) {
            return '<span class="badge badge-info">Tidak ada komposisi</span>';
        }

        $tooltip = htmlspecialchars(implode(', ', $status));
        if (!$bisaDiproduksi) {
            return '<span class="badge badge-danger" title="' . $tooltip . '" data-toggle="tooltip">Tidak bisa diproduksi</span>';
        }

        return '<span class="badge badge-' . $warna . '" title="' . $tooltip . '" data-toggle="tooltip">' .
            ($warna === 'danger' ? 'Stok tidak cukup' : ($warna === 'warning' ? 'Ada bahan perlu pembelian' : ($warna === 'info' ? 'Ada bahan belum diatur SS' : 'Bisa diproduksi'))) .
            '</span>';
    }

    /**
     * Get label status bahan baku
     */
    public function getStatusBahanBakuLabelAttribute()
    {
        $bisaDiproduksi = $this->bisaDiproduksiSatuUnit();

        if (!$bisaDiproduksi) {
            return 'Tidak bisa diproduksi';
        }

        $status = [];
        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;

            if ($bahanBaku->safety_stock > 0 && $bahanBaku->stok <= $bahanBaku->safety_stock) {
                $status[] = 'Perlu Pembelian';
            }
        }

        if (!empty($status)) {
            return 'Bisa diproduksi (Perlu Pembelian)';
        }

        return 'Bisa diproduksi';
    }

    /**
     * Get status bisa diproduksi
     */
    public function getStatusBisaDiproduksiAttribute()
    {
        return $this->bisaDiproduksiSatuUnit();
    }

    /**
     * Get informasi lengkap untuk form penjualan
     */
    public function getInfoForPenjualan()
    {
        $statusBahan = [];
        $perluPembelian = false;
        $bisaDiproduksi = $this->bisaDiproduksiSatuUnit();
        $bahanTidakCukup = [];

        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;
            $statusBahan[] = [
                'nama' => $bahanBaku->nama,
                'stok' => $bahanBaku->stok,
                'dibutuhkan_per_unit' => $komposisi->jumlah,
                'safety_stock' => $bahanBaku->safety_stock,
                'status_ss' => $bahanBaku->status_ss_label,
                'status_stok_bahan' => $bahanBaku->status_stok_bahan,
                'perlu_pembelian' => !$bahanBaku->isStokAmanSS(),
                'stok_cukup_1unit' => $bahanBaku->stok >= $komposisi->jumlah
            ];

            if (!$bahanBaku->isStokAmanSS() && $bahanBaku->safety_stock > 0) {
                $perluPembelian = true;
            }

            // Cek jika stok tidak cukup untuk 1 unit
            if ($bahanBaku->stok < $komposisi->jumlah) {
                $bahanTidakCukup[] = [
                    'nama' => $bahanBaku->nama,
                    'stok_tersedia' => $bahanBaku->stok,
                    'dibutuhkan' => $komposisi->jumlah,
                    'kekurangan' => $komposisi->jumlah - $bahanBaku->stok,
                    'satuan' => $bahanBaku->satuan
                ];
            }
        }

        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'harga' => $this->harga,
            'satuan' => $this->satuan,
            'status_bahan_baku_badge' => $this->status_bahan_baku_badge,
            'status_bahan_baku_label' => $this->status_bahan_baku_label,
            'bisa_diproduksi' => $bisaDiproduksi,
            'bahan_baku_info' => $statusBahan,
            'perlu_pembelian_bahan' => $perluPembelian,
            'bahan_tidak_cukup' => $bahanTidakCukup,
            'bisa_diproduksi_satu_unit' => $bisaDiproduksi
        ];
    }
}
