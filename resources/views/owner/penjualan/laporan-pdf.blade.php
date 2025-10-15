<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 5px 0;
        }

        .info {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN PENJUALAN</h1>
        <p>Periode: {{ date('d/m/Y', strtotime($tanggalAwal)) }} - {{ date('d/m/Y', strtotime($tanggalAkhir)) }}</p>
        <p>Dibuat pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info">
        <p><strong>Total Transaksi:</strong> {{ $penjualan->count() }}</p>
        <p><strong>Total Penjualan:</strong> Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</p>
    </div>

    <h3>Data Transaksi Penjualan</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Bayar</th>
                <th>Kembalian</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penjualan as $key => $item)
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td>{{ $item->kode_penjualan }}</td>
                    <td>{{ $item->nama_customer }}</td>
                    <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->bayar, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->kembalian, 0, ',', '.') }}</td>
                    <td>{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalBayar, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalKembalian, 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    @if ($produkTerlaris->count() > 0)
        <h3>10 Produk Terlaris</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Total Terjual</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($produkTerlaris as $key => $item)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td>{{ $item->produk->nama ?? 'Produk Tidak Ditemukan' }}</td>
                        <td class="text-center">{{ $item->total_terjual }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($bahanBakuTerlaris->count() > 0)
        <h3>10 Bahan Baku Terlaris</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Bahan Baku</th>
                    <th>Total Terjual</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bahanBakuTerlaris as $key => $item)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td>{{ $item->bahanBaku->nama ?? 'Bahan Baku Tidak Ditemukan' }}</td>
                        <td class="text-center">{{ $item->total_terjual }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Dicetak oleh: Owner</p>
        <p>Halaman 1</p>
    </div>
</body>

</html>
