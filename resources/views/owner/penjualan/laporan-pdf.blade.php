<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - {{ $tanggalAwal }} hingga {{ $tanggalAkhir }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 18px;
            color: #666;
        }

        .header p {
            margin: 5px 0;
            color: #777;
        }

        .info-box {
            margin: 15px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 11px;
        }

        table th {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }

        table td {
            padding: 6px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .summary {
            margin-top: 30px;
            padding: 15px;
            border-top: 2px solid #333;
            background-color: #f8f9fa;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
        }

        .page-break {
            page-break-before: always;
        }

        .section-title {
            background-color: #34495e;
            color: white;
            padding: 8px 12px;
            margin: 20px 0 10px 0;
            border-radius: 3px;
            font-size: 14px;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            border-radius: 3px;
            margin: 0 2px;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-primary {
            background-color: #007bff;
            color: white;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .totals-row {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .grouped-item {
            border-bottom: 1px dashed #ddd;
            padding: 4px 0;
        }

        .grouped-item:last-child {
            border-bottom: none;
        }

        .group-header {
            background-color: #f0f8ff;
            font-weight: bold;
        }

        .vertical-top {
            vertical-align: top;
        }

        .rowspan-cell {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN PENJUALAN</h1>
        <h2>Permata Biru Onix</h2>
        <p>Jl. Contoh No. 123, Kota Contoh - Telp: 0812-3456-7890</p>
        <p>Periode: {{ date('d/m/Y', strtotime($tanggalAwal)) }} - {{ date('d/m/Y', strtotime($tanggalAkhir)) }}</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info-box">
        <div class="section-title" style="margin: 0 0 10px 0; font-size: 12px;">
            RINGKASAN LAPORAN
        </div>
        <div class="info-row">
            <span><strong>Total Transaksi:</strong></span>
            <span>{{ $penjualan->count() }} Transaksi</span>
        </div>
        <div class="info-row">
            <span><strong>Total Penjualan:</strong></span>
            <span><strong>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</strong></span>
        </div>
        <div class="info-row">
            <span><strong>Total Uang Masuk:</strong></span>
            <span><strong>Rp {{ number_format($totalBayar, 0, ',', '.') }}</strong></span>
        </div>
        <div class="info-row">
            <span><strong>Total Kembalian:</strong></span>
            <span><strong>Rp {{ number_format($totalKembalian, 0, ',', '.') }}</strong></span>
        </div>
        <div class="info-row">
            <span><strong>Periode Laporan:</strong></span>
            <span>{{ date('d F Y', strtotime($tanggalAwal)) }} - {{ date('d F Y', strtotime($tanggalAkhir)) }}</span>
        </div>
    </div>

    @if (!empty($laporanDetail))
        @php
            $groupedData = [];
            $processedKodes = [];

            foreach ($laporanDetail as $detail) {
                $kode = $detail['kode_penjualan'];

                if (!in_array($kode, $processedKodes)) {
                    $processedKodes[] = $kode;

                    $itemsWithSameKode = array_filter($laporanDetail, function ($item) use ($kode) {
                        return $item['kode_penjualan'] == $kode;
                    });

                    $firstItem = reset($itemsWithSameKode);

                    $groupedData[$kode] = [
                        'tanggal' => $firstItem['tanggal'],
                        'kode_penjualan' => $firstItem['kode_penjualan'],
                        'nama_customer' => $firstItem['nama_customer'],
                        'nama_admin' => $firstItem['nama_admin'],
                        'total' => $firstItem['total'],
                        'bayar' => $firstItem['bayar'],
                        'kembalian' => $firstItem['kembalian'],
                        'items' => array_values($itemsWithSameKode),
                    ];
                }
            }
        @endphp

        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="8%">Tanggal</th>
                    <th width="10%">Kode</th>
                    <th width="10%">Customer</th>
                    <th width="15%">Produk</th>
                    <th width="6%">Jumlah</th>
                    <th width="15%">Bahan Baku</th>
                    <th width="10%">Admin/Kasir</th>
                    <th width="9%">Total</th>
                    <th width="9%">Bayar</th>
                    <th width="9%">Kembalian</th>
                </tr>
            </thead>
            <tbody>
                @php $rowIndex = 1; @endphp
                @foreach ($groupedData as $kodePenjualan => $group)
                    @php $itemCount = count($group['items']); @endphp
                    <tr>
                        <td rowspan="{{ $itemCount }}" class="text-center rowspan-cell">{{ $rowIndex++ }}</td>
                        <td rowspan="{{ $itemCount }}" class="text-center rowspan-cell">
                            {{ date('d/m/Y', strtotime($group['tanggal'])) }}</td>
                        <td rowspan="{{ $itemCount }}" class="text-center rowspan-cell">
                            {{ $group['kode_penjualan'] }}</td>
                        <td rowspan="{{ $itemCount }}" class="text-center rowspan-cell">
                            {{ $group['nama_customer'] }}</td>

                        @foreach ($group['items'] as $index => $item)
                            @if ($index > 0)
                    </tr>
                    <tr>
                @endif

                <td>
                    <div class="grouped-item">
                        {{ $item['produk'] }}
                        @if ($item['jenis_item'] == 'produk')
                            <span class="badge badge-success">Produk</span>
                        @else
                            <span class="badge badge-info">Bahan Baku</span>
                        @endif
                    </div>
                </td>
                <td class="text-center">{{ number_format($item['jumlah_produk'], 0, ',', '.') }}</td>
                <td>
                    @if ($item['bahan_baku_digunakan'])
                        <div class="grouped-item">
                            {{ $item['bahan_baku_digunakan'] }}
                            @if (!empty($item['jumlah_digunakan']))
                                ({{ number_format($item['jumlah_digunakan'], 0, ',', '.') }})
                            @endif
                        </div>
                    @else
                        -
                    @endif
                </td>

                @if ($index === 0)
                    <td rowspan="{{ $itemCount }}" class="text-center rowspan-cell">
                        @if ($group['nama_admin'] != '-')
                            <span class="badge badge-primary">{{ $group['nama_admin'] }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td rowspan="{{ $itemCount }}" class="text-right rowspan-cell">Rp
                        {{ number_format($group['total'], 0, ',', '.') }}</td>
                    <td rowspan="{{ $itemCount }}" class="text-right rowspan-cell">Rp
                        {{ number_format($group['bayar'], 0, ',', '.') }}</td>
                    <td rowspan="{{ $itemCount }}" class="text-right rowspan-cell">Rp
                        {{ number_format($group['kembalian'], 0, ',', '.') }}</td>
                @endif
    @endforeach
    </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr class="totals-row">
            <td colspan="8" class="text-right"><strong>TOTAL KESELURUHAN:</strong></td>
            <td class="text-right"><strong>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</strong></td>
            <td class="text-right"><strong>Rp {{ number_format($totalBayar, 0, ',', '.') }}</strong></td>
            <td class="text-right"><strong>Rp {{ number_format($totalKembalian, 0, ',', '.') }}</strong></td>
        </tr>
    </tfoot>
    </table>
@else
    <div style="text-align: center; padding: 20px; border: 1px solid #ddd; margin: 20px 0;">
        <p style="color: #666; font-size: 14px;">Tidak ada data penjualan pada periode ini.</p>
    </div>
    @endif

    <div class="page-break"></div>

    <div class="header">
        <h1>STATISTIK PENJUALAN</h1>
        <p>Periode: {{ date('d/m/Y', strtotime($tanggalAwal)) }} - {{ date('d/m/Y', strtotime($tanggalAkhir)) }}</p>
    </div>

    <!-- 10 Produk Terlaris -->
    <div class="section-title">
        10 PRODUK TERLARIS
    </div>

    @if ($produkTerlaris->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="55%">Nama Produk</th>
                    <th width="15%">Satuan</th>
                    <th width="15%">Jumlah Terjual</th>
                    <th width="20%" class="text-right">Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($produkTerlaris as $index => $produk)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $produk['nama'] }}</td>
                        <td class="text-center">{{ $produk['satuan'] }}</td>
                        <td class="text-center">{{ number_format($produk['total_terjual'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($produk['total_pendapatan'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                    <td class="text-center">
                        <strong>{{ number_format($produkTerlaris->sum('total_terjual'), 0, ',', '.') }}</strong>
                    </td>
                    <td class="text-right"><strong>Rp
                            {{ number_format($produkTerlaris->sum('total_pendapatan'), 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        </table>
    @else
        <div style="text-align: center; padding: 10px; color: #666; font-style: italic;">
            Tidak ada data produk terjual pada periode ini.
        </div>
    @endif

    <div class="section-title">
        10 BAHAN BAKU TERLARIS
        <span style="float: right; font-size: 11px;">*Termasuk dari penjualan produk</span>
    </div>

    @if ($bahanBakuTerlaris->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="60%">Nama Bahan Baku</th>
                    <th width="15%">Satuan</th>
                    <th width="20%">Jumlah Digunakan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bahanBakuTerlaris as $index => $bahan)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $bahan['nama'] }}</td>
                        <td class="text-center">{{ $bahan['satuan'] }}</td>
                        <td class="text-center">{{ number_format($bahan['total_penggunaan'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="3" class="text-right"><strong>Total Penggunaan:</strong></td>
                    <td class="text-center">
                        <strong>{{ number_format($bahanBakuTerlaris->sum('total_penggunaan'), 0, ',', '.') }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    @else
        <div style="text-align: center; padding: 10px; color: #666; font-style: italic;">
            Tidak ada data bahan baku digunakan pada periode ini.
        </div>
    @endif

    <div class="footer">
        <table width="100%">
            <tr>
                <td width="70%">
                    <p style="margin: 0; font-size: 10px;">
                        <strong>CATATAN:</strong><br>
                        1. Laporan ini dicetak secara otomatis oleh sistem<br>
                        2. Data bahan baku terlaris dihitung berdasarkan penggunaan dalam produk yang terjual<br>
                        3. Periode laporan: {{ date('d/m/Y', strtotime($tanggalAwal)) }} -
                        {{ date('d/m/Y', strtotime($tanggalAkhir)) }}<br>
                        4. Total transaksi: {{ $penjualan->count() }} penjualan<br>
                        5. Data produk dalam satu transaksi ditampilkan dalam satu baris gabungan
                    </p>
                </td>
                <td width="30%" style="text-align: center; vertical-align: top;">
                    <p style="margin: 0; font-size: 10px;">
                        <strong>DICETAK OLEH SISTEM</strong><br>
                        Permata Biru Onix<br>
                        {{ date('d/m/Y H:i') }}
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
