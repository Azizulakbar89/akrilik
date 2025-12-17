<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .header p {
            margin: 5px 0;
        }

        .info-box {
            margin: 15px 0;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary {
            margin-top: 20px;
            padding: 15px;
            border-top: 2px solid #333;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        .page-break {
            page-break-before: always;
        }

        .section-title {
            background-color: #4a5568;
            color: white;
            padding: 8px;
            margin: 15px 0;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN PENJUALAN</h1>
        <p>Periode: {{ date('d/m/Y', strtotime($tanggalAwal)) }} - {{ date('d/m/Y', strtotime($tanggalAkhir)) }}</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info-box">
        <h3 style="margin: 0 0 10px 0;">Ringkasan Transaksi</h3>
        <div class="summary-row">
            <span>Total Transaksi:</span>
            <span><strong>{{ $penjualan->count() }} Transaksi</strong></span>
        </div>
        <div class="summary-row">
            <span>Total Penjualan:</span>
            <span><strong>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</strong></span>
        </div>
        <div class="summary-row">
            <span>Total Uang Masuk:</span>
            <span><strong>Rp {{ number_format($totalBayar, 0, ',', '.') }}</strong></span>
        </div>
        <div class="summary-row">
            <span>Total Kembalian:</span>
            <span><strong>Rp {{ number_format($totalKembalian, 0, ',', '.') }}</strong></span>
        </div>
    </div>

    <div class="section-title">
        <h3 style="margin: 0; color: white;">10 PRODUK TERLARIS</h3>
    </div>

    @if ($produkTerlaris->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="40%">Nama Produk</th>
                    <th width="15%">Satuan</th>
                    <th width="20%">Jumlah Terjual</th>
                    <th width="20%" class="text-right">Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($produkTerlaris as $index => $produk)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $produk['nama'] }}</td>
                        <td>{{ $produk['satuan'] }}</td>
                        <td>{{ number_format($produk['total_terjual'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($produk['total_pendapatan'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #666; padding: 10px;">
            Tidak ada data produk terjual pada periode ini.
        </p>
    @endif

    <div class="section-title">
        <h3 style="margin: 0; color: white;">10 BAHAN BAKU TERLARIS</h3>
        <small style="color: white;">*Termasuk dari penjualan produk</small>
    </div>

    @if ($bahanBakuTerlaris->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="55%">Nama Bahan Baku</th>
                    <th width="20%">Satuan</th>
                    <th width="20%">Jumlah Digunakan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bahanBakuTerlaris as $index => $bahan)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $bahan['nama'] }}</td>
                        <td>{{ $bahan['satuan'] }}</td>
                        <td>{{ number_format($bahan['total_penggunaan'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #666; padding: 10px;">
            Tidak ada data bahan baku digunakan pada periode ini.
        </p>
    @endif

    <div class="page-break"></div>

    <div class="header">
        <h1>DETAIL TRANSAKSI PENJUALAN</h1>
        <p>Periode: {{ date('d/m/Y', strtotime($tanggalAwal)) }} - {{ date('d/m/Y', strtotime($tanggalAkhir)) }}</p>
    </div>

    @if ($penjualan->count() > 0)
        @foreach ($penjualan as $index => $transaksi)
            <div style="margin-bottom: 20px; page-break-inside: avoid;">
                <h3 style="margin-bottom: 5px; color: #333;">
                    Transaksi #{{ $index + 1 }} - {{ $transaksi->kode_penjualan }}
                </h3>
                <p style="margin: 0 0 10px 0; color: #666;">
                    Tanggal: {{ date('d/m/Y', strtotime($transaksi->tanggal)) }} |
                    Customer: {{ $transaksi->nama_customer }} |
                    Admin/Kasir: <strong>{{ $transaksi->admin ? $transaksi->admin->name : '-' }}</strong>
                </p>

                <table>
                    <thead>
                        <tr>
                            <th width="8%">No</th>
                            <th width="42%">Item</th>
                            <th width="12%">Jumlah</th>
                            <th width="23%" class="text-right">Subtotal</th>
                            <th width="15%">Admin/Kasir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksi->detailPenjualan as $detailIndex => $detail)
                            <tr>
                                <td class="text-center">{{ $detailIndex + 1 }}</td>
                                <td>
                                    {{ $detail->nama_produk }}
                                    <br><small>{{ $detail->jenis_item == 'produk' ? 'Produk' : 'Bahan Baku' }}</small>
                                </td>
                                <td>{{ $detail->jumlah }}</td>
                                <td class="text-right">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                                <td>{{ $transaksi->admin ? $transaksi->admin->name : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Total:</strong></td>
                            <td class="text-right"><strong>Rp
                                    {{ number_format($transaksi->total, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Bayar:</strong></td>
                            <td class="text-right"><strong>Rp
                                    {{ number_format($transaksi->bayar, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Kembalian:</strong></td>
                            <td class="text-right"><strong>Rp
                                    {{ number_format($transaksi->kembalian, 0, ',', '.') }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($index < $penjualan->count() - 1)
                <hr style="border: 1px dashed #ddd; margin: 20px 0;">
            @endif
        @endforeach
    @else
        <p style="text-align: center; color: #666; padding: 20px;">
            Tidak ada transaksi penjualan pada periode ini.
        </p>
    @endif

    <div style="margin-top: 50px; padding-top: 20px; border-top: 1px solid #333;">
        <table>
            <tr>
                <td width="70%">
                    <p style="margin: 0; font-size: 11px;">
                        <strong>Catatan:</strong><br>
                        1. Laporan ini dicetak secara otomatis oleh sistem.<br>
                        2. Data bahan baku terlaris dihitung berdasarkan penggunaan dalam produk yang terjual.<br>
                        3. Periode laporan: {{ date('d/m/Y', strtotime($tanggalAwal)) }} -
                        {{ date('d/m/Y', strtotime($tanggalAkhir)) }}
                    </p>
                </td>
                <td width="30%" style="text-align: center;">
                    <p style="margin: 0; padding-top: 40px;">
                        <strong>Dicetak Oleh Sistem</strong><br>
                        {{ date('d/m/Y H:i') }}
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
