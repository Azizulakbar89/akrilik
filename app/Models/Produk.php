<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function komposisi()
    {
        return $this->hasMany(KomposisiBahanBaku::class, 'produk_id');
    }

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'produk_id');
    }

    public function bisaDiproduksi($jumlah)
    {
        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;
            $kebutuhan = $komposisi->jumlah * $jumlah;

            if ($bahanBaku->stok < $kebutuhan) {
                return false;
            }
        }
        return true;
    }

    public function kurangiStokBahanBaku($jumlah)
    {
        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;
            $kebutuhan = $komposisi->jumlah * $jumlah;

            $bahanBaku->stok -= $kebutuhan;
            $bahanBaku->save();

            PenggunaanBahanBaku::create([
                'bahan_baku_id' => $bahanBaku->id,
                'jumlah' => $kebutuhan,
                'tanggal' => now(),
                'keterangan' => 'Penjualan produk: ' . $this->nama . ' (x' . $jumlah . ')'
            ]);

            if (method_exists($bahanBaku, 'updateParameterStok')) {
                $bahanBaku->updateParameterStok();
            }
        }
    }

    public function kembalikanStokBahanBaku($jumlah)
    {
        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;
            $kebutuhan = $komposisi->jumlah * $jumlah;

            $bahanBaku->stok += $kebutuhan;
            $bahanBaku->save();

            PenggunaanBahanBaku::create([
                'bahan_baku_id' => $bahanBaku->id,
                'jumlah' => -$kebutuhan,
                'tanggal' => now(),
                'keterangan' => 'Pembatalan penjualan produk: ' . $this->nama . ' (x' . $jumlah . ')'
            ]);

            if (method_exists($bahanBaku, 'updateParameterStok')) {
                $bahanBaku->updateParameterStok();
            }
        }
    }

    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return Storage::disk('public')->url($this->foto);
        }
        return asset('vendors/images/default-produk.jpg');
    }

    public function getHargaJualAttribute()
    {
        return $this->harga;
    }

    public function getHppAttribute()
    {
        $hpp = 0;
        foreach ($this->komposisi as $komposisi) {
            $hpp += $komposisi->bahanBaku->harga_beli * $komposisi->jumlah;
        }
        return $hpp;
    }

    public function getMarginAttribute()
    {
        return $this->harga - $this->hpp;
    }

    public function getMarginPersenAttribute()
    {
        if ($this->hpp > 0) {
            return round((($this->harga - $this->hpp) / $this->hpp) * 100, 2);
        }
        return 0;
    }

    public function prosesPenjualan($jumlah)
    {
        if (!$this->bisaDiproduksi($jumlah)) {
            throw new \Exception("Bahan baku tidak mencukupi untuk memproduksi {$jumlah} {$this->nama}");
        }

        $this->kurangiStokBahanBaku($jumlah);
        return $this;
    }

    public function batalkanPenjualan($jumlah)
    {
        $this->kembalikanStokBahanBaku($jumlah);
        return $this;
    }

    // Method untuk mendapatkan detail status produksi
    public function getStatusProduksi($jumlah)
    {
        $status = [
            'bisa_diproduksi' => true,
            'detail' => []
        ];

        foreach ($this->komposisi as $komposisi) {
            $bahanBaku = $komposisi->bahanBaku;
            $kebutuhan = $komposisi->jumlah * $jumlah;
            $cukup = $bahanBaku->stok >= $kebutuhan;

            $status['detail'][] = [
                'bahan_baku' => $bahanBaku->nama,
                'kebutuhan' => $kebutuhan,
                'stok_tersedia' => $bahanBaku->stok,
                'cukup' => $cukup,
                'satuan' => $bahanBaku->satuan
            ];

            if (!$cukup) {
                $status['bisa_diproduksi'] = false;
            }
        }

        return $status;
    }
}
