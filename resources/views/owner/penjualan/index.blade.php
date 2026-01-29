@extends('layoutsAPP.deskapp')

@section('title', 'Penjualan - Owner')

@section('content')
    <div class="pd-ltr-20">
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Laporan Penjualan</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Penjualan</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 col-sm-12 text-right">
                    <button class="btn btn-info" data-toggle="modal" data-target="#modalPrintLaporan">
                        <i class="fa fa-file-pdf"></i> Print PDF
                    </button>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card-box bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title text-white">Total Pendapatan</h5>
                        <h3 class="font-weight-bold">{{ $totalPendapatanFormatted }}</h3>
                        <p class="card-text">
                            Periode: {{ date('d/m/Y', strtotime($tanggalAwal)) }} -
                            {{ date('d/m/Y', strtotime($tanggalAkhir)) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-box bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title text-white">Total Transaksi</h5>
                        <h3 class="font-weight-bold">{{ $penjualan->count() }}</h3>
                        <p class="card-text">Transaksi Penjualan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-box bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title text-white">Margin Keuntungan</h5>
                        <h3 class="font-weight-bold">{{ $marginKeuntunganFormatted }}</h3>
                        <p class="card-text">Dari Harga Beli ke Jual</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-box bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title text-white">Laba Kotor</h5>
                        <h3 class="font-weight-bold">Rp {{ number_format($labaKotor, 0, ',', '.') }}</h3>
                        <p class="card-text">Pendapatan - Biaya Bahan Baku</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Filter -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card-box">
                    <div class="card-header">
                        <h5 class="text-blue h5">Filter Laporan</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('owner.penjualan.index') }}" method="GET" class="form-inline">
                            <div class="form-group mr-2">
                                <label for="tanggal_awal" class="mr-2">Tanggal Awal:</label>
                                <input type="date" name="tanggal_awal" class="form-control"
                                    value="{{ $tanggalAwal ?? date('Y-m-01') }}">
                            </div>
                            <div class="form-group mr-2">
                                <label for="tanggal_akhir" class="mr-2">Tanggal Akhir:</label>
                                <input type="date" name="tanggal_akhir" class="form-control"
                                    value="{{ $tanggalAkhir ?? date('Y-m-d') }}">
                            </div>
                            <div class="form-group mr-2">
                                <label for="search_bahan_baku" class="mr-2">Cari Bahan Baku:</label>
                                <select name="search_bahan_baku" class="form-control select2" style="min-width: 200px;">
                                    <option value="">-- Semua Bahan Baku --</option>
                                    @foreach ($bahanBakuList as $bahan)
                                        <option value="{{ $bahan->nama }}"
                                            {{ $searchBahanBaku == $bahan->nama ? 'selected' : '' }}>
                                            {{ $bahan->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('owner.penjualan.index') }}" class="btn btn-secondary">
                                <i class="fa fa-refresh"></i> Reset
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="row mb-3">
            <!-- 10 Produk Terlaris -->
            <div class="col-md-6">
                <div class="card-box">
                    <div class="card-header">
                        <h4 class="text-blue h4">10 Produk Terlaris</h4>
                        <p class="text-muted">Periode: {{ $tanggalAwal }} s/d {{ $tanggalAkhir }}</p>
                        @if ($searchBahanBaku)
                            <small class="text-warning">Filter: {{ $searchBahanBaku }}</small>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Produk</th>
                                        <th>Terjual</th>
                                        <th>Pendapatan</th>
                                        <th>Margin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($produkTerlaris as $index => $produk)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $produk['nama'] }}</td>
                                            <td>{{ $produk['total_terjual'] }} {{ $produk['satuan'] }}</td>
                                            <td>{{ $produk['total_pendapatan_formatted'] }}</td>
                                            <td>
                                                @php
                                                    $marginClass =
                                                        $produk['margin_keuntungan'] >= 30
                                                            ? 'badge-success'
                                                            : ($produk['margin_keuntungan'] >= 15
                                                                ? 'badge-warning'
                                                                : 'badge-danger');
                                                @endphp
                                                <span class="badge {{ $marginClass }}">
                                                    {{ $produk['margin_keuntungan_formatted'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Tidak ada data produk terjual</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 10 Bahan Baku Terlaris -->
            <div class="col-md-6">
                <div class="card-box">
                    <div class="card-header">
                        <h4 class="text-blue h4">10 Bahan Baku Terlaris</h4>
                        <p class="text-muted">Periode: {{ $tanggalAwal }} s/d {{ $tanggalAkhir }}</p>
                        <small class="text-muted">*Termasuk dari penjualan produk</small>
                        @if ($searchBahanBaku)
                            <small class="text-warning d-block">Filter: {{ $searchBahanBaku }}</small>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Bahan Baku</th>
                                        <th>Digunakan</th>
                                        <th>Satuan</th>
                                        <th>Margin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bahanBakuTerlaris as $index => $bahan)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $bahan['nama'] }}</td>
                                            <td>{{ $bahan['total_penggunaan_formatted'] }}</td>
                                            <td>{{ $bahan['satuan'] }}</td>
                                            <td>
                                                @php
                                                    $marginClass =
                                                        $bahan['margin'] >= 30
                                                            ? 'badge-success'
                                                            : ($bahan['margin'] >= 15
                                                                ? 'badge-warning'
                                                                : 'badge-danger');
                                                @endphp
                                                <span class="badge {{ $marginClass }}">
                                                    {{ $bahan['margin_formatted'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Tidak ada data bahan baku digunakan
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Transaksi -->
        <div class="row">
            <div class="col-12">
                <div class="card-box mb-30">
                    <div class="card-header">
                        <h4 class="text-blue h4">Data Transaksi Penjualan</h4>
                        <p class="text-muted">Hanya dapat melihat data penjualan</p>
                        @if ($searchBahanBaku)
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fa fa-info-circle"></i> Menampilkan penjualan yang mengandung bahan baku:
                                <strong>{{ $searchBahanBaku }}</strong>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="riwayatTable">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Bayar</th>
                                        <th>Kembalian</th>
                                        <th>Bahan Baku Digunakan</th>
                                        <th>Tanggal</th>
                                        <th>Admin/Kasir</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($penjualan as $item)
                                        <tr>
                                            <td>{{ $item->kode_penjualan }}</td>
                                            <td>{{ $item->nama_customer }}</td>
                                            <td>{{ $item->total_formatted }}</td>
                                            <td>{{ $item->bayar_formatted }}</td>
                                            <td>{{ $item->kembalian_formatted }}</td>
                                            <td>
                                                @if (isset($item->bahan_baku_digunakan) && count($item->bahan_baku_digunakan) > 0)
                                                    @foreach ($item->bahan_baku_digunakan as $index => $bahan)
                                                        <span class="badge badge-info mb-1">
                                                            {{ $bahan['nama'] }}: {{ $bahan['jumlah'] }}
                                                            {{ $bahan['satuan'] }}
                                                        </span>
                                                        @if (!$loop->last)
                                                            <br>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                                            <td>
                                                @if ($item->admin)
                                                    <span class="badge badge-info">{{ $item->admin->name }}</span>
                                                @else
                                                    <span class="badge badge-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                        href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                        <button class="dropdown-item view-penjualan"
                                                            data-id="{{ $item->id }}">
                                                            <i class="dw dw-eye"></i> Lihat Detail
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Total Bahan Baku Keluar dengan Margin Keuntungan -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card-box">
                    <div class="card-header">
                        <h4 class="text-blue h4">
                            <i class="fa fa-box"></i> Total Bahan Baku Keluar & Margin Keuntungan
                            <span class="float-right badge badge-primary">{{ $totalBahanBakuKeluar->count() }}
                                Jenis</span>
                        </h4>
                        <p class="text-muted mb-0">Periode: {{ $tanggalAwal }} s/d {{ $tanggalAkhir }}</p>
                        @if ($searchBahanBaku)
                            <small class="text-warning">
                                <i class="fa fa-filter"></i> Filter: {{ $searchBahanBaku }}
                            </small>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Nama Bahan Baku</th>
                                        <th width="10%">Satuan</th>
                                        <th width="12%">Harga Beli/Unit</th>
                                        <th width="12%">Harga Jual/Unit</th>
                                        <th width="10%">Total Digunakan</th>
                                        <th width="12%">Total Biaya</th>
                                        <th width="12%">Total Pendapatan</th>
                                        <th width="12%">Laba</th>
                                        <th width="15%">Margin Keuntungan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($totalBahanBakuKeluar as $index => $bahan)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $bahan['nama'] }}</strong>
                                            </td>
                                            <td class="text-center">{{ $bahan['satuan'] }}</td>
                                            <td class="text-right">{{ $bahan['harga_beli_formatted'] }}</td>
                                            <td class="text-right">{{ $bahan['harga_jual_formatted'] }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-info">{{ $bahan['total_penggunaan_formatted'] }}</span>
                                            </td>
                                            <td class="text-right">
                                                <strong
                                                    class="text-primary">{{ $bahan['total_harga_beli_formatted'] }}</strong>
                                            </td>
                                            <td class="text-right">
                                                <strong
                                                    class="text-info">{{ $bahan['total_pendapatan_formatted'] }}</strong>
                                            </td>
                                            <td class="text-right">
                                                @php
                                                    $labaClass = $bahan['laba'] >= 0 ? 'text-success' : 'text-danger';
                                                @endphp
                                                <strong
                                                    class="{{ $labaClass }}">{{ $bahan['laba_formatted'] }}</strong>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $marginClass =
                                                        $bahan['margin_keuntungan'] >= 30
                                                            ? 'badge-success'
                                                            : ($bahan['margin_keuntungan'] >= 15
                                                                ? 'badge-warning'
                                                                : 'badge-danger');
                                                @endphp
                                                <span class="badge {{ $marginClass }}">
                                                    {{ $bahan['margin_keuntungan_formatted'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <i class="fa fa-box fa-2x mb-2"></i><br>
                                                Tidak ada data bahan baku keluar pada periode ini
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if ($totalBahanBakuKeluar->count() > 0)
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="5" class="text-right">
                                                <strong>TOTAL KESELURUHAN:</strong>
                                            </td>
                                            <td class="text-center">
                                                <strong>
                                                    {{ number_format($totalBahanBakuKeluar->sum('total_penggunaan'), 2, ',', '.') }}
                                                </strong>
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-primary">
                                                    Rp
                                                    {{ number_format($totalBahanBakuKeluar->sum('total_harga_beli'), 0, ',', '.') }}
                                                </strong>
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-info">
                                                    Rp
                                                    {{ number_format($totalBahanBakuKeluar->sum('total_pendapatan'), 0, ',', '.') }}
                                                </strong>
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-success">
                                                    Rp
                                                    {{ number_format($totalBahanBakuKeluar->sum('laba'), 0, ',', '.') }}
                                                </strong>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $totalMargin =
                                                        $totalBahanBakuKeluar->sum('total_harga_beli') > 0
                                                            ? ($totalBahanBakuKeluar->sum('laba') /
                                                                    $totalBahanBakuKeluar->sum('total_harga_beli')) *
                                                                100
                                                            : 0;
                                                    $totalMarginClass =
                                                        $totalMargin >= 30
                                                            ? 'badge-success'
                                                            : ($totalMargin >= 15
                                                                ? 'badge-warning'
                                                                : 'badge-danger');
                                                @endphp
                                                <strong class="badge {{ $totalMarginClass }}">
                                                    {{ number_format($totalMargin, 2, ',', '.') }}%
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-right">
                                                <strong>TOTAL PENDAPATAN:</strong>
                                            </td>
                                            <td colspan="5" class="text-right">
                                                <strong class="text-danger" style="font-size: 1.2em;">
                                                    {{ $totalPendapatanFormatted }}
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-right">
                                                <strong>TOTAL BIAYA BAHAN BAKU:</strong>
                                            </td>
                                            <td colspan="5" class="text-right">
                                                <strong class="text-primary" style="font-size: 1.1em;">
                                                    Rp {{ number_format($totalBiayaBahanBaku, 0, ',', '.') }}
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-right">
                                                <strong>LABA KOTOR:</strong>
                                            </td>
                                            <td colspan="5" class="text-right">
                                                <strong class="text-success" style="font-size: 1.2em;">
                                                    Rp {{ number_format($labaKotor, 0, ',', '.') }}
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-right">
                                                <strong>MARGIN KEUNTUNGAN (Keseluruhan):</strong>
                                            </td>
                                            <td colspan="5" class="text-center">
                                                @php
                                                    $overallMarginClass =
                                                        $marginKeuntunganFormatted >= 30
                                                            ? 'badge-success'
                                                            : ($marginKeuntunganFormatted >= 15
                                                                ? 'badge-warning'
                                                                : 'badge-danger');
                                                @endphp
                                                <strong class="badge {{ $overallMarginClass }}"
                                                    style="font-size: 1.1em;">
                                                    {{ $marginKeuntunganFormatted }}
                                                </strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Penjualan -->
    <div class="modal fade" id="detailPenjualanModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Penjualan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detailContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-success" id="printNotaBtn">
                        <i class="fa fa-print"></i> Print Nota
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Print PDF -->
    <div class="modal fade" id="modalPrintLaporan" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Print Laporan PDF</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('owner.penjualan.generate-pdf') }}" method="GET" target="_blank">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="tanggal_awal">Tanggal Awal</label>
                            <input type="date" name="tanggal_awal" class="form-control"
                                value="{{ $tanggalAwal ?? date('Y-m-01') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_akhir">Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" class="form-control"
                                value="{{ $tanggalAkhir ?? date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="search_bahan_baku">Filter Bahan Baku (Opsional)</label>
                            <select name="search_bahan_baku" class="form-control select2">
                                <option value="">-- Semua Bahan Baku --</option>
                                @foreach ($bahanBakuList as $bahan)
                                    <option value="{{ $bahan->nama }}"
                                        {{ $searchBahanBaku == $bahan->nama ? 'selected' : '' }}>
                                        {{ $bahan->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-file-pdf"></i> Print Laporan PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card-box.bg-primary,
        .card-box.bg-success,
        .card-box.bg-info,
        .card-box.bg-warning {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-box.bg-primary h3,
        .card-box.bg-success h3,
        .card-box.bg-info h3,
        .card-box.bg-warning h3 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .card-box.bg-warning {
            background-color: #ffc107 !important;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .detail-summary {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .detail-summary .row {
            margin-bottom: 10px;
        }

        .detail-summary strong {
            color: #333;
        }

        .info-box {
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #007bff;
        }

        .info-box.bg-success {
            border-left-color: #28a745;
        }

        .info-box.bg-info {
            border-left-color: #17a2b8;
        }

        .badge {
            padding: 5px 10px;
            font-size: 12px;
            font-weight: normal;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-warning {
            background-color: #ffc107;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .bahan-baku-list {
            list-style-type: none;
            padding-left: 0;
        }

        .bahan-baku-list li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        .bahan-baku-list li:last-child {
            border-bottom: none;
        }

        .select2-container {
            min-width: 200px;
        }

        /* Custom styles for tables */
        .table-hover tbody tr:hover {
            background-color: #f5f5f5;
        }

        .table-bordered {
            border: 1px solid #dee2e6;
        }

        .table-bordered thead th {
            border-bottom-width: 2px;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .text-primary {
            color: #007bff !important;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-info {
            color: #17a2b8 !important;
        }

        .badge-primary {
            background-color: #007bff;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        .form-inline .form-group {
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .form-inline .form-group {
                display: block;
                margin-bottom: 15px;
            }

            .form-inline .form-group label {
                display: block;
                margin-bottom: 5px;
            }

            .select2-container {
                width: 100% !important;
            }

            .card-box.bg-primary h3,
            .card-box.bg-success h3,
            .card-box.bg-info h3,
            .card-box.bg-warning h3 {
                font-size: 1.4rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                placeholder: 'Pilih Bahan Baku',
                allowClear: true
            });

            let currentPenjualanId = null;

            // View Penjualan Detail
            $('.view-penjualan').click(function() {
                const id = $(this).data('id');
                currentPenjualanId = id;

                $.get('{{ url('owner/penjualan') }}/' + id, function(response) {
                    if (response.status === 'success') {
                        const penjualan = response.data;

                        let detailHtml = `
                        <div class="detail-summary">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Kode Penjualan:</strong> ${penjualan.kode_penjualan}</p>
                                    <p><strong>Customer:</strong> ${penjualan.nama_customer}</p>
                                    <p><strong>Tanggal:</strong> ${new Date(penjualan.tanggal).toLocaleDateString('id-ID')}</p>
                                    <p><strong>Admin/Kasir:</strong> ${penjualan.admin ? penjualan.admin : '-'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total:</strong> ${penjualan.total_formatted}</p>
                                    <p><strong>Bayar:</strong> ${penjualan.bayar_formatted}</p>
                                    <p><strong>Kembalian:</strong> ${penjualan.kembalian_formatted}</p>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6>Detail Items:</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Jenis</th>
                                        <th>Nama Item</th>
                                        <th>Jumlah</th>
                                        <th>Harga Satuan</th>
                                        <th>Subtotal</th>
                                        <th>Bahan Baku Digunakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        penjualan.detail_penjualan.forEach(detail => {
                            let bahanBakuHtml = '';
                            if (detail.bahan_baku_digunakan && detail.bahan_baku_digunakan
                                .length > 0) {
                                bahanBakuHtml = '<ul class="bahan-baku-list">';
                                detail.bahan_baku_digunakan.forEach(bb => {
                                    bahanBakuHtml +=
                                        `<li>${bb.nama}: ${bb.jumlah} ${bb.satuan}</li>`;
                                });
                                bahanBakuHtml += '</ul>';
                            } else {
                                bahanBakuHtml = '-';
                            }

                            detailHtml += `
                            <tr>
                                <td>${detail.jenis_item === 'produk' ? 'Produk' : 'Bahan Baku'}</td>
                                <td>${detail.nama_produk}</td>
                                <td>${detail.jumlah}</td>
                                <td>${detail.harga_sat_formatted}</td>
                                <td>${detail.sub_total_formatted}</td>
                                <td>${bahanBakuHtml}</td>
                            </tr>
                        `;
                        });

                        detailHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;

                        if (penjualan.total_bahan_baku && penjualan.total_bahan_baku.length > 0) {
                            detailHtml += `
                            <hr>
                            <h6>Ringkasan Bahan Baku Digunakan:</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Bahan Baku</th>
                                            <th>Jumlah Digunakan</th>
                                            <th>Satuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;

                            penjualan.total_bahan_baku.forEach(bb => {
                                detailHtml += `
                                <tr>
                                    <td>${bb.nama}</td>
                                    <td>${bb.jumlah}</td>
                                    <td>${bb.satuan}</td>
                                </tr>
                                `;
                            });

                            detailHtml += `
                                    </tbody>
                                </table>
                            </div>
                            `;
                        }

                        $('#detailContent').html(detailHtml);
                        $('#detailPenjualanModal').modal('show');
                    }
                });
            });

            // Print Nota Button
            $('#printNotaBtn').click(function() {
                if (currentPenjualanId) {
                    window.open('{{ url('owner/penjualan') }}/' + currentPenjualanId + '/print', '_blank');
                }
            });

            // DataTable initialization
            if ($.fn.DataTable) {
                $('#riwayatTable').DataTable({
                    "pageLength": 10,
                    "lengthMenu": [10, 25, 50, 100],
                    "language": {
                        "paginate": {
                            "previous": "<i class='fa fa-angle-left'></i>",
                            "next": "<i class='fa fa-angle-right'></i>"
                        },
                        "search": "Cari:",
                        "lengthMenu": "Tampilkan _MENU_ data",
                        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        "infoEmpty": "Tidak ada data",
                        "infoFiltered": "(disaring dari _MAX_ total data)"
                    }
                });
            }
        });
    </script>
@endpush
