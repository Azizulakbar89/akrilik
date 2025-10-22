<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian - {{ date('d-m-Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
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
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
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
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin: 20px 0;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none;
            }

            .header {
                margin-bottom: 20px;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN PEMBELIAN BERHASIL</h1>
        <p>Sistem Manajemen Inventory</p>
        <p>Periode: {{ \Carbon\Carbon::parse($request->tanggal_awal)->translatedFormat('d F Y') }} -
            {{ \Carbon\Carbon::parse($request->tanggal_akhir)->translatedFormat('d F Y') }}</p>
    </div>

    <div class="info">
        <p><strong>Tanggal Cetak:</strong> {{ date('d-m-Y H:i:s') }}</p>
        <p><strong>Total Data:</strong> {{ $pembelian->count() }} transaksi</p>
        <p><strong>Total Pembelian:</strong> Rp {{ number_format($totalPembelian, 0, ',', '.') }}</p>
    </div>

    @if ($pembelian->isEmpty())
        <div class="no-data">
            <h3>Tidak Ada Data Pembelian</h3>
            <p>Tidak ada data pembelian berhasil pada periode yang dipilih.</p>
        </div>
    @else
        <h3>Data Transaksi Pembelian</h3>
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Kode Pembelian</th>
                    <th width="20%">Supplier</th>
                    <th width="15%">Tanggal</th>
                    <th width="15%">Jumlah Item</th>
                    <th width="15%">Total Pembelian</th>
                    <th width="15%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pembelian as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->kode_pembelian ?? 'PB-' . $item->id }}</td>
                        <td>{{ $item->supplier->nama }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                        <td class="text-center">{{ $item->detailPembelian->count() }} item</td>
                        <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span style="color: #28a745;">●</span> Selesai
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="text-right"><strong>GRAND TOTAL:</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($totalPembelian, 0, ',', '.') }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        @if ($supplierTerbanyak->count() > 0)
            <h3>Supplier Terbanyak</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Supplier</th>
                        <th>Jumlah Transaksi</th>
                        <th>Total Pembelian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($supplierTerbanyak as $key => $item)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td>{{ $item->supplier->nama ?? 'Supplier Tidak Ditemukan' }}</td>
                            <td class="text-center">{{ $item->jumlah_transaksi }}</td>
                            <td class="text-right">Rp {{ number_format($item->total_pembelian, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if ($bahanBakuTerbanyak->count() > 0)
            <h3>Bahan Baku Terbanyak Dibeli</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Bahan Baku</th>
                        <th>Jumlah Dibeli</th>
                        <th>Total Pembelian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bahanBakuTerbanyak as $key => $item)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td>{{ $item->bahanBaku->nama ?? 'Bahan Baku Tidak Ditemukan' }}</td>
                            <td class="text-center">{{ $item->total_dibeli }} {{ $item->bahanBaku->satuan ?? '' }}
                            </td>
                            <td class="text-right">Rp {{ number_format($item->total_pembelian, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @foreach ($pembelian as $index => $item)
            <div style="page-break-inside: avoid; margin-bottom: 20px;">
                <h4>Detail Pembelian: {{ $item->kode_pembelian ?? 'PB-' . $item->id }}</h4>
                <table>
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="35%">Bahan Baku</th>
                            <th width="15%">Jumlah</th>
                            <th width="20%">Harga Satuan</th>
                            <th width="25%">Sub Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item->detailPembelian as $detailIndex => $detail)
                            <tr>
                                <td class="text-center">{{ $detailIndex + 1 }}</td>
                                <td>{{ $detail->bahanBaku->nama }}</td>
                                <td class="text-center">{{ $detail->jumlah }} {{ $detail->bahanBaku->satuan }}</td>
                                <td class="text-right">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f8f9fa;">
                            <td colspan="4" class="text-right"><strong>Total:</strong></td>
                            <td class="text-right"><strong>Rp {{ number_format($item->total, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach
    @endif

    <div class="footer">
        <p>Dicetak pada: {{ date('d-m-Y H:i:s') }}</p>
        <p>Oleh: Sistem Manajemen Inventory</p>
    </div>

    <script>
        window.onload = function() {
            window.print();

            setTimeout(function() {
                window.history.back();
            }, 1000);
        }
    </script>
</body>

</html>
