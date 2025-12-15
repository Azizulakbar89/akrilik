<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian - {{ date('d-m-Y') }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 10px 15px;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
            width: 100%;
        }

        .header h1 {
            margin: 0 0 5px 0;
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }

        .header p {
            margin: 2px 0;
            font-size: 11px;
            color: #555;
        }

        .info {
            margin-bottom: 15px;
            padding: 8px 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 3px;
            width: 100%;
        }

        .info p {
            margin: 3px 0;
            display: inline-block;
            width: 33%;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
            word-wrap: break-word;
            font-size: 10px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 5px 4px;
            text-align: left;
            vertical-align: top;
            overflow: hidden;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #000;
            font-size: 10px;
            padding: 6px 4px;
        }

        .no-col {
            width: 5%;
        }

        .kode-col {
            width: 12%;
        }

        .supplier-col {
            width: 18%;
        }

        .tanggal-col {
            width: 10%;
        }

        .item-col {
            width: 8%;
        }

        .total-col {
            width: 12%;
        }

        .status-col {
            width: 8%;
        }

        .detail-no {
            width: 5%;
        }

        .detail-nama {
            width: 35%;
        }

        .detail-jumlah {
            width: 10%;
        }

        .detail-harga {
            width: 15%;
        }

        .detail-subtotal {
            width: 15%;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .total-row {
            background-color: #e8e8e8 !important;
            font-weight: bold;
        }

        .alternate-row {
            background-color: #f9f9f9;
        }

        .detail-section {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .detail-header {
            background-color: #e9ecef;
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-bottom: none;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .detail-content {
            border: 1px solid #ddd;
            border-top: none;
        }

        .detail-table {
            width: 100%;
            margin-bottom: 0;
            font-size: 9px;
        }

        .detail-table th,
        .detail-table td {
            padding: 3px 4px;
            border: 1px solid #ddd;
        }

        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            text-align: right;
            font-size: 9px;
            color: #666;
            width: 100%;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px dashed #ccc;
            margin: 15px 0;
        }

        @media print {
            body {
                padding: 0.3cm 0.5cm !important;
                margin: 0 !important;
                font-size: 10pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            table {
                width: 100% !important;
                font-size: 9pt !important;
            }

            th,
            td {
                padding: 3px 3px !important;
            }

            th {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
            }

            .total-row,
            .detail-header {
                background-color: #e9ecef !important;
                -webkit-print-color-adjust: exact;
            }

            .detail-section {
                page-break-inside: auto;
                break-inside: auto;
            }

            @page {
                size: A4 portrait;
                margin: 0.5cm;
            }

            @page :first {
                margin-top: 0.5cm;
            }
        }

        @media screen {
            body {
                padding: 15px;
                background-color: #f5f5f5;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN PEMBELIAN @if ($status && $status !== 'semua')
                - {{ strtoupper(str_replace('_', ' ', $status)) }}
            @endif
        </h1>
        <p>Sistem Manajemen Inventory</p>
        <p>Periode: {{ \Carbon\Carbon::parse($tanggal_awal)->translatedFormat('d F Y') }} -
            {{ \Carbon\Carbon::parse($tanggal_akhir)->translatedFormat('d F Y') }}</p>
    </div>

    <div class="info">
        <p><strong>Tanggal Cetak:</strong> {{ date('d-m-Y H:i:s') }}</p>
        <p><strong>Total Data:</strong> {{ $pembelian->count() }} transaksi</p>
        <p><strong>Total Pembelian:</strong> Rp {{ number_format($totalPembelian, 0, ',', '.') }}</p>
    </div>

    @if ($pembelian->isEmpty())
        <div class="no-data">
            <h3>Tidak Ada Data Pembelian</h3>
            <p>Tidak ada data pembelian pada periode yang dipilih.</p>
        </div>
    @else
        <h3>Ringkasan Transaksi Pembelian</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="no-col text-center">No</th>
                        <th class="kode-col">Kode</th>
                        <th class="supplier-col">Supplier</th>
                        <th class="tanggal-col">Tanggal</th>
                        <th class="item-col text-center">Item</th>
                        <th class="total-col text-right">Total</th>
                        <th class="status-col text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pembelian as $index => $item)
                        <tr class="{{ $loop->even ? 'alternate-row' : '' }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $item->kode_pembelian ?? 'PB-' . str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $item->supplier->nama }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d/m/Y') }}</td>
                            <td class="text-center">{{ $item->detailPembelian->count() }}</td>
                            <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if ($item->status == 'completed')
                                    <span style="color: #28a745;">● Selesai</span>
                                @elseif($item->status == 'menunggu_persetujuan')
                                    <span style="color: #ffc107;">● Menunggu</span>
                                @elseif($item->status == 'ditolak')
                                    <span style="color: #dc3545;">● Ditolak</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5" class="text-right"><strong>TOTAL KESELURUHAN:</strong></td>
                        <td class="text-right"><strong>Rp {{ number_format($totalPembelian, 0, ',', '.') }}</strong>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <h3>Detail Semua Transaksi</h3>

        @foreach ($pembelian as $index => $item)
            <div class="detail-section">
                <div class="detail-header">
                    <span>{{ $index + 1 }}.
                        {{ $item->kode_pembelian ?? 'PB-' . str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</span>
                    <span>Supplier: {{ $item->supplier->nama }} | Total: Rp
                        {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
                <div class="detail-content">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="40%">Bahan Baku</th>
                                <th width="10%" class="text-center">Jumlah</th>
                                <th width="20%" class="text-right">Harga</th>
                                <th width="15%" class="text-right">Sub Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($item->detailPembelian as $detailIndex => $detail)
                                <tr>
                                    <td class="text-center">{{ $detailIndex + 1 }}</td>
                                    <td>{{ $detail->bahanBaku->nama }}</td>
                                    <td class="text-center">{{ $detail->jumlah }} {{ $detail->bahanBaku->satuan }}
                                    </td>
                                    <td class="text-right">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8f9fa;">
                                <td colspan="4" class="text-right"><strong>Total Transaksi:</strong></td>
                                <td class="text-right"><strong>Rp
                                        {{ number_format($item->total, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach

        @if ($supplierTerbanyak->count() > 0 || $bahanBakuTerbanyak->count() > 0)
            <h3>Analisis Pembelian</h3>

            @if ($supplierTerbanyak->count() > 0)
                <div style="margin-bottom: 15px;">
                    <h4 style="margin-bottom: 5px;">Supplier Terbanyak</h4>
                    <table style="width: 100%; font-size: 9px;">
                        <thead>
                            <tr>
                                <th width="8%" class="text-center">No</th>
                                <th width="47%">Nama Supplier</th>
                                <th width="20%" class="text-center">Transaksi</th>
                                <th width="25%" class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($supplierTerbanyak as $key => $item)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td>{{ $item->supplier->nama ?? 'Supplier Tidak Ditemukan' }}</td>
                                    <td class="text-center">{{ $item->jumlah_transaksi }}</td>
                                    <td class="text-right">Rp {{ number_format($item->total_pembelian, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($bahanBakuTerbanyak->count() > 0)
                <div>
                    <h4 style="margin-bottom: 5px;">Bahan Baku Terbanyak Dibeli</h4>
                    <table style="width: 100%; font-size: 9px;">
                        <thead>
                            <tr>
                                <th width="8%" class="text-center">No</th>
                                <th width="47%">Bahan Baku</th>
                                <th width="25%" class="text-center">Jumlah</th>
                                <th width="20%" class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bahanBakuTerbanyak as $key => $item)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td>{{ $item->bahanBaku->nama ?? 'Bahan Baku Tidak Ditemukan' }}</td>
                                    <td class="text-center">{{ $item->total_dibeli }}
                                        {{ $item->bahanBaku->satuan ?? '' }}</td>
                                    <td class="text-right">Rp {{ number_format($item->total_pembelian, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    @endif

    <div class="footer">
        <p>Dicetak pada: {{ date('d-m-Y H:i:s') }}</p>
        <p>Oleh: Sistem Manajemen Inventory</p>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);

            setTimeout(function() {
                if (!document.hidden) {
                    window.history.back();
                }
            }, 2000);
        }
    </script>
</body>

</html>
