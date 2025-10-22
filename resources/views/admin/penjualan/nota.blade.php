<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Penjualan - {{ $penjualan->kode_penjualan }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }

        .nota-container {
            width: 80mm;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
        }

        .header p {
            margin: 2px 0;
            font-size: 10px;
        }

        .info-section {
            margin-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .table th {
            border-bottom: 1px dashed #000;
            padding: 5px 2px;
            text-align: left;
        }

        .table td {
            padding: 3px 2px;
            border-bottom: 1px dashed #ddd;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-section {
            border-top: 2px solid #000;
            margin-top: 10px;
            padding-top: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
            font-size: 10px;
        }

        @media print {
            body {
                margin: 0;
                padding: 10px;
            }

            .no-print {
                display: none;
            }

            .nota-container {
                width: 80mm;
            }
        }

        .btn-print {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px auto;
            display: block;
        }
    </style>
</head>

<body>
    <div class="nota-container nota-print">
        <div class="header">
            <h1>Permata Biru Onix</h1>
            <p>Jl. Contoh No. 123, Kota Contoh</p>
            <p>Telp: 0812-3456-7890</p>
        </div>

        <div class="info-section">
            <div class="info-row">
                <span><strong>Kode:</strong> {{ $penjualan->kode_penjualan }}</span>
            </div>
            <div class="info-row">
                <span><strong>Customer:</strong> {{ $penjualan->nama_customer }}</span>
            </div>
            <div class="info-row">
                <span><strong>Tanggal:</strong>
                    {{ \Carbon\Carbon::parse($penjualan->tanggal)->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($penjualan->detailPenjualan as $detail)
                    <tr>
                        <td>{{ $detail->nama_produk }}</td>
                        <td class="text-right">{{ $detail->jumlah }}</td>
                        <td class="text-right">{{ number_format($detail->harga_sat, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <div class="info-row">
                <span><strong>Total:</strong></span>
                <span><strong>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</strong></span>
            </div>
            <div class="info-row">
                <span>Bayar:</span>
                <span>Rp {{ number_format($penjualan->bayar, 0, ',', '.') }}</span>
            </div>
            <div class="info-row">
                <span>Kembalian:</span>
                <span>Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Terima kasih atas kunjungan Anda</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button class="btn-print" onclick="window.print()">
            🖨️ Print Nota
        </button>
        <button class="btn-print" onclick="window.close()" style="background: #6c757d;">
            ✕ Tutup
        </button>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }

        window.onafterprint = function() {}
    </script>
</body>

</html>
