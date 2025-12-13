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

        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
        }

        .badge-produk {
            background-color: #007bff;
            color: white;
        }

        .badge-bahan-baku {
            background-color: #28a745;
            color: white;
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

    @if ($top10Terlaris->count() > 0)
        <h3>10 Item Terlaris (Produk & Bahan Baku)</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Item</th>
                    <th>Jenis</th>
                    <th>Total Terjual</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($top10Terlaris as $key => $item)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td>{{ $item->nama }}</td>
                        <td class="text-center">
                            @if ($item->jenis == 'produk')
                                <span class="badge badge-produk">Produk</span>
                            @else
                                <span class="badge badge-bahan-baku">Bahan Baku</span>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($item->total_terjual, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Tidak ada data item terlaris pada periode ini.</p>
    @endif

    <div class="footer">
        <p>Dicetak oleh: Owner</p>
        <p>Halaman 1</p>
    </div>
</body>

</html>
