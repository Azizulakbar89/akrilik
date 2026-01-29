@extends('layoutsAPP.deskapp')

@section('title', 'Penjualan')

@section('content')
    <div class="pd-ltr-20">
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Penjualan</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Penjualan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-md-4">
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
            <div class="col-md-4">
                <div class="card-box bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title text-white">Total Transaksi</h5>
                        <h3 class="font-weight-bold">{{ $penjualan->count() }}</h3>
                        <p class="card-text">Transaksi Penjualan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-box bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title text-white">Bahan Baku Digunakan</h5>
                        <h3 class="font-weight-bold">{{ $totalBahanBakuKeluar->count() }}</h3>
                        <p class="card-text">Jenis Bahan Baku</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card-box">
                    <div class="card-body">
                        <form action="{{ route('admin.penjualan.index') }}" method="GET" class="form-inline">
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
                            <a href="{{ route('admin.penjualan.index') }}" class="btn btn-secondary">
                                <i class="fa fa-refresh"></i> Reset
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Filter -->
        @if ($searchBahanBaku)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fa fa-filter"></i> Filter Aktif: Menampilkan penjualan yang mengandung bahan baku
                        <strong>{{ $searchBahanBaku }}</strong>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card-box mb-30">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="text-blue h4">Transaksi Penjualan</h4>
                        <button type="button" class="btn btn-primary" id="addItemBtn">
                            <i class="fa fa-plus"></i> Tambah Item
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="penjualanForm">
                            @csrf
                            <div class="form-group">
                                <label for="nama_customer">Nama Customer</label>
                                <input type="text" class="form-control" id="nama_customer" name="nama_customer" required>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th>Jenis Item</th>
                                            <th>Nama Item</th>
                                            <th>Bahan Baku Digunakan</th>
                                            <th>Status SS</th>
                                            <th>Jumlah</th>
                                            <th>Harga Satuan</th>
                                            <th>Subtotal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Belum ada item</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-right"><strong>Total</strong></td>
                                            <td colspan="2"><strong id="totalAmount">Rp 0</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-right"><strong>Bayar</strong></td>
                                            <td colspan="2"><strong id="bayarAmount">Rp 0</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-right"><strong>Kembalian</strong></td>
                                            <td colspan="2"><strong id="kembalianAmount">Rp 0</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="total">Total</label>
                                        <input type="text" class="form-control" id="total" readonly
                                            value="Rp 0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="bayar">Bayar</label>
                                        <input type="number" class="form-control" id="bayar" name="bayar"
                                            min="0" step="1000" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="kembalian">Kembalian</label>
                                        <input type="text" class="form-control" id="kembalian" readonly
                                            value="Rp 0">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg btn-block mt-3">
                                <i class="fa fa-save"></i> Simpan Penjualan (Kasir: {{ Auth::user()->name }})
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-box mb-30">
                    <div class="card-header">
                        <h4 class="text-blue h4">Riwayat Penjualan</h4>
                        <p class="text-muted mb-0">Periode: {{ $tanggalAwal }} s/d {{ $tanggalAkhir }}</p>
                        @if ($searchBahanBaku)
                            <small class="text-warning">Filter: {{ $searchBahanBaku }}</small>
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
                                        <th>Bahan Baku Digunakan</th>
                                        <th>Kasir</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($penjualan as $item)
                                        <tr>
                                            <td>{{ $item->kode_penjualan }}</td>
                                            <td>{{ $item->nama_customer }}</td>
                                            <td>{{ $item->total_formatted }}</td>
                                            <td>
                                                @if (isset($item->bahan_baku_digunakan) && count($item->bahan_baku_digunakan) > 0)
                                                    @foreach ($item->bahan_baku_digunakan as $index => $bahan)
                                                        <span class="badge badge-info mb-1" data-toggle="tooltip"
                                                            title="{{ $bahan['jumlah'] }} {{ $bahan['satuan'] }}">
                                                            {{ $bahan['nama'] }}
                                                        </span>
                                                        @if (!$loop->last)
                                                            <br>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->admin)
                                                    <span class="badge badge-info">{{ $item->admin->name }}</span>
                                                @else
                                                    <span class="badge badge-secondary">Unknown</span>
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
                                                        <button class="dropdown-item print-penjualan"
                                                            data-id="{{ $item->id }}">
                                                            <i class="dw dw-print"></i> Print
                                                        </button>
                                                        <button class="dropdown-item delete-penjualan"
                                                            data-id="{{ $item->id }}">
                                                            <i class="dw dw-delete-3"></i> Hapus
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
    </div>

    <!-- Modal Tambah Item -->
    <div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="itemForm">
                        <div class="form-group">
                            <label for="jenis_item">Jenis Item</label>
                            <select class="form-control" id="jenis_item" name="jenis_item" required>
                                <option value="">Pilih Jenis</option>
                                <option value="produk">Produk</option>
                                <option value="bahan_baku">Bahan Baku</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="item_id">Item</label>
                            <select class="form-control select2" id="item_id" name="item_id" required>
                                <option value="">Pilih Item</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="jumlah">Jumlah</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" min="1"
                                required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Stok Tersedia:</label>
                                    <div id="statusInfo" class="badge badge-info">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Satuan:</label>
                                    <div id="satuanInfo" class="badge badge-secondary">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Harga Satuan:</label>
                                    <div id="hargaInfo" class="badge badge-success">Rp 0</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Subtotal:</label>
                                    <div id="subtotalInfo" class="badge badge-primary">Rp 0</div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Bahan Baku untuk Produk -->
                        <div id="bahanBakuInfo" style="display: none;">
                            <hr>
                            <h6>Bahan Baku yang Digunakan:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="bahanBakuTable">
                                    <thead>
                                        <tr>
                                            <th>Bahan Baku</th>
                                            <th>Jumlah per Unit</th>
                                            <th>Satuan</th>
                                            <th>Stok Tersedia</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bahanBakuBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="alert alert-success" id="successAlert" style="display: none;">
                            <i class="fa fa-check-circle"></i> <span id="successText"></span>
                        </div>
                        <div class="alert alert-info" id="infoAlert" style="display: none;">
                            <i class="fa fa-info-circle"></i> <span id="infoText"></span>
                        </div>
                        <div class="alert alert-warning" id="warningAlert" style="display: none;">
                            <i class="fa fa-exclamation-triangle"></i> <span id="warningText"></span>
                        </div>
                        <div class="alert alert-danger" id="dangerAlert" style="display: none;">
                            <i class="fa fa-exclamation-circle"></i> <span id="dangerText"></span>
                        </div>
                        <div class="alert alert-danger" id="errorAlert" style="display: none;">
                            <i class="fa fa-times-circle"></i> <span id="errorText"></span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="saveItemBtn">Tambah ke Keranjang</button>
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
                    <div id="detailContent">
                    </div>
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

    <!-- Modal Laporan Bahan Baku -->
    <div class="modal fade" id="modalLaporan" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-chart-bar"></i> Laporan Bahan Baku Keluar & Pendapatan
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Nama Bahan Baku</th>
                                    <th width="12%">Satuan</th>
                                    <th width="15%">Harga Beli/Unit</th>
                                    <th width="15%">Total Digunakan</th>
                                    <th width="15%">Total Biaya Bahan Baku</th>
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
                                        <td class="text-center">
                                            <span
                                                class="badge badge-info">{{ $bahan['total_penggunaan_formatted'] }}</span>
                                        </td>
                                        <td class="text-right">
                                            <strong
                                                class="text-primary">{{ $bahan['total_harga_beli_formatted'] }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fa fa-box fa-2x mb-2"></i><br>
                                            Tidak ada data bahan baku keluar pada periode ini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($totalBahanBakuKeluar->count() > 0)
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="4" class="text-right">
                                            <strong>TOTAL BIAYA BAHAN BAKU:</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong>
                                                {{ number_format($totalBahanBakuKeluar->sum('total_penggunaan'), 2, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td class="text-right">
                                            <strong class="text-success">
                                                Rp
                                                {{ number_format($totalBahanBakuKeluar->sum('total_harga_beli'), 0, ',', '.') }}
                                            </strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right">
                                            <strong>TOTAL PENDAPATAN:</strong>
                                        </td>
                                        <td colspan="2" class="text-right">
                                            <strong class="text-danger" style="font-size: 1.2em;">
                                                {{ $totalPendapatanFormatted }}
                                            </strong>
                                        </td>
                                    </tr>
                                    @php
                                        $totalBiayaBahanBaku = $totalBahanBakuKeluar->sum('total_harga_beli');
                                        $labaKotor = $totalPendapatan - $totalBiayaBahanBaku;
                                    @endphp
                                    <tr>
                                        <td colspan="4" class="text-right">
                                            <strong>ESTIMASI LABA KOTOR:</strong>
                                        </td>
                                        <td colspan="2" class="text-right">
                                            <strong class="text-success" style="font-size: 1.2em;">
                                                Rp {{ number_format($labaKotor, 0, ',', '.') }}
                                            </strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    <div class="alert alert-info mt-3">
                        <h6><i class="fa fa-info-circle"></i> Keterangan:</h6>
                        <ul class="mb-0">
                            <li>Data dihitung berdasarkan periode: <strong>{{ date('d/m/Y', strtotime($tanggalAwal)) }} -
                                    {{ date('d/m/Y', strtotime($tanggalAkhir)) }}</strong></li>
                            <li>Total bahan baku digunakan termasuk dari produk yang terjual dan penjualan bahan baku
                                langsung</li>
                            <li>Estimasi laba kotor = Total Pendapatan - Total Biaya Bahan Baku</li>
                            @if ($searchBahanBaku)
                                <li class="text-warning">Filter aktif: {{ $searchBahanBaku }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="printLaporan">
                        <i class="fa fa-print"></i> Print Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card-box.bg-primary,
        .card-box.bg-success,
        .card-box.bg-info {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-box.bg-primary h3,
        .card-box.bg-success h3,
        .card-box.bg-info h3 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .select2-container {
            width: 100% !important;
        }

        .badge {
            font-size: 12px;
            padding: 5px 10px;
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

        .item-detail {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .item-detail:last-child {
            border-bottom: none;
        }

        .status-ss-cell {
            max-width: 200px;
            min-width: 150px;
        }

        .select2-container--default .select2-results__option[aria-disabled=true] {
            color: #999;
            background-color: #f5f5f5;
        }

        .select2-container--default .select2-results__option--highlighted[aria-disabled=true] {
            background-color: #f5f5f5;
            color: #999;
        }

        .item-disabled {
            color: #999;
        }

        .item-enabled {
            color: #333;
        }

        .bahan-baku-badge {
            margin: 2px;
            font-size: 11px;
            cursor: help;
        }

        #bahanBakuTable th {
            font-size: 12px;
            padding: 5px;
        }

        #bahanBakuTable td {
            font-size: 12px;
            padding: 5px;
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
            .card-box.bg-info h3 {
                font-size: 1.5rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let itemCounter = 0;
            let currentItems = [];
            let currentPenjualanId = null;

            // Inisialisasi tooltip
            $('[data-toggle="tooltip"]').tooltip();

            // Inisialisasi Select2 untuk filter
            $('.select2').select2({
                placeholder: 'Pilih Bahan Baku',
                allowClear: true
            });

            $('#item_id').select2({
                placeholder: "Pilih Item",
                allowClear: true,
                templateResult: function(item) {
                    if (!item.id) {
                        return item.text;
                    }

                    // Tambahkan status di sebelah nama item
                    var $item = $(
                        '<span>' + item.text +
                        (item.element.dataset.status ?
                            ' <span class="float-right">' + item.element.dataset.status +
                            '</span>' :
                            '') +
                        '</span>'
                    );
                    return $item;
                },
                templateSelection: function(item) {
                    if (!item.id) {
                        return item.text;
                    }

                    // Hanya tampilkan nama item tanpa status saat dipilih
                    var $item = $('<span>' + item.text.split('(')[0].trim() + '</span>');
                    return $item;
                }
            });

            // Handle modal laporan print
            $('#printLaporan').click(function() {
                const tanggalAwal = $('input[name="tanggal_awal"]').val();
                const tanggalAkhir = $('input[name="tanggal_akhir"]').val();
                const searchBahanBaku = $('select[name="search_bahan_baku"]').val();

                let url = '{{ route('admin.penjualan.index') }}?tanggal_awal=' + tanggalAwal +
                    '&tanggal_akhir=' + tanggalAkhir;
                if (searchBahanBaku) {
                    url += '&search_bahan_baku=' + encodeURIComponent(searchBahanBaku);
                }
                url += '&print_laporan=true';

                window.open(url, '_blank');
                $('#modalLaporan').modal('hide');
            });

            $('#addItemModal').on('hidden.bs.modal', function() {
                $('#itemForm')[0].reset();
                $('#statusInfo').text('-').removeClass('badge-danger badge-warning badge-success').addClass(
                    'badge-info');
                $('#hargaInfo').text('Rp 0').removeClass('badge-success').addClass('badge-success');
                $('#subtotalInfo').text('Rp 0').removeClass('badge-primary').addClass('badge-primary');
                $('#satuanInfo').text('-').removeClass('badge-secondary').addClass('badge-secondary');
                $('#statusSSInfo').html('');
                $('#item_id').empty().append('<option value="">Pilih Item</option>');
                $('#jenis_item').val('');
                $('#successAlert').hide();
                $('#infoAlert').hide();
                $('#warningAlert').hide();
                $('#dangerAlert').hide();
                $('#errorAlert').hide();
                $('#bahanBakuInfo').hide();
                $('#bahanBakuBody').empty();
            });

            $('#addItemBtn').click(function() {
                $('#addItemModal').modal('show');
            });

            $('#jenis_item').change(function() {
                const jenis = $(this).val();
                $('#item_id').empty().append('<option value="">Pilih Item</option>');
                $('#bahanBakuInfo').hide();
                $('#bahanBakuBody').empty();

                if (jenis === 'produk') {
                    @foreach ($produk as $item)
                        @php
                            $info = $item->info_penjualan;
                            $status = '';
                            $disabled = false;

                            if (!$info['bisa_diproduksi_satu_unit']) {
                                $status = '<span class="badge badge-danger">Tidak bisa diproduksi</span>';
                                $disabled = true;
                            } elseif ($info['perlu_pembelian_bahan']) {
                                $status = '<span class="badge badge-warning">Perlu Pembelian</span>';
                            } else {
                                $status = '<span class="badge badge-success">Aman</span>';
                            }
                        @endphp
                        $('#item_id').append(
                            `<option value="{{ $item->id }}" 
                             data-harga="{{ $item->harga }}" 
                             data-satuan="{{ $item->satuan }}"
                             data-info='@json($info)'
                             data-status='{!! $status !!}'
                             {{ $disabled ? 'disabled' : '' }}>
                             {{ $item->nama }}
                            </option>`
                        );
                    @endforeach
                } else if (jenis === 'bahan_baku') {
                    @foreach ($bahanBaku as $item)
                        @php
                            $info = $item->info_penjualan;
                            $status = '';
                            $disabled = false;

                            if ($item->stok <= 0) {
                                $status = '<span class="badge badge-danger">Perlu Pembelian</span>';
                                $disabled = true;
                            } elseif ($info['perlu_pembelian']) {
                                $status = '<span class="badge badge-warning">Perlu Pembelian</span>';
                            } else {
                                $status = '<span class="badge badge-success">Aman</span>';
                            }
                        @endphp
                        $('#item_id').append(
                            `<option value="{{ $item->id }}" 
                             data-stok="{{ $item->stok }}" 
                             data-harga="{{ $item->harga_jual }}" 
                             data-satuan="{{ $item->satuan }}"
                             data-info='@json($info)'
                             data-status='{!! $status !!}'
                             {{ $disabled ? 'disabled' : '' }}>
                             {{ $item->nama }} (Stok: {{ $item->stok }})
                            </option>`
                        );
                    @endforeach
                }

                resetStatusFields();
                $('#item_id').trigger('change.select2');
            });

            $('#item_id').change(function() {
                const selectedOption = $(this).find('option:selected');
                const jenis = $('#jenis_item').val();
                const harga = selectedOption.data('harga');
                const satuan = selectedOption.data('satuan');
                const info = selectedOption.data('info');
                const isDisabled = selectedOption.prop('disabled');

                // Reset
                $('#bahanBakuInfo').hide();
                $('#bahanBakuBody').empty();

                if (isDisabled) {
                    $('#saveItemBtn').prop('disabled', true);
                    if (jenis === 'produk') {
                        $('#errorText').text(
                            'Produk ini tidak bisa ditambahkan karena bahan baku tidak cukup.');
                    } else {
                        $('#errorText').text('Bahan baku ini tidak bisa ditambahkan karena stok habis.');
                    }
                    $('#errorAlert').show();
                    $('#successAlert').hide();
                    $('#infoAlert').hide();
                    $('#warningAlert').hide();
                    $('#dangerAlert').hide();
                    return;
                } else {
                    $('#saveItemBtn').prop('disabled', false);
                    $('#errorAlert').hide();
                }

                if (harga !== undefined) {
                    // Update informasi dasar
                    if (jenis === 'produk') {
                        $('#statusInfo').text('Produk - Bahan baku akan diproses saat penjualan')
                            .removeClass('badge-danger badge-warning badge-success')
                            .addClass('badge-info');

                        // Ambil info bahan baku dari server
                        $.ajax({
                            url: '{{ url('admin/penjualan/get-item-info') }}',
                            type: 'GET',
                            data: {
                                jenis_item: jenis,
                                item_id: selectedOption.val()
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    const data = response.data;

                                    // Tampilkan informasi bahan baku
                                    if (data.bahan_baku_digunakan && data.bahan_baku_digunakan
                                        .length > 0) {
                                        $('#bahanBakuInfo').show();
                                        data.bahan_baku_digunakan.forEach(function(bahan) {
                                            const row = `
                                                <tr>
                                                    <td>${bahan.nama}</td>
                                                    <td>${bahan.jumlah_per_unit}</td>
                                                    <td>${bahan.satuan}</td>
                                                    <td>
                                                        ${bahan.stok_tersedia}
                                                        ${bahan.stok_tersedia < bahan.jumlah_per_unit ? 
                                                            '<span class="badge badge-danger float-right">Kurang</span>' : 
                                                            '<span class="badge badge-success float-right">Cukup</span>'}
                                                    </td>
                                                </tr>
                                            `;
                                            $('#bahanBakuBody').append(row);
                                        });
                                    }
                                }
                            }
                        });

                        if (info.bisa_diproduksi_satu_unit) {
                            if (info.perlu_pembelian_bahan) {
                                $('#warningText').text(
                                    'Produk ini mengandung bahan baku yang perlu pembelian (≤ Safety Stock).'
                                );
                                $('#warningAlert').show();
                                $('#successAlert').hide();
                            } else {
                                $('#successText').text('Produk bisa diproduksi. Semua bahan baku aman.');
                                $('#successAlert').show();
                                $('#warningAlert').hide();
                            }
                        }
                    } else {
                        const stok = selectedOption.data('stok');
                        $('#statusInfo').text(stok);
                        if (stok <= 0) {
                            $('#statusInfo').removeClass('badge-info badge-warning badge-success').addClass(
                                'badge-danger');
                            $('#dangerText').text('Stok habis! Tidak dapat menjual item ini.');
                            $('#dangerAlert').show();
                            $('#warningAlert').hide();
                            $('#successAlert').hide();
                        } else if (stok <= 10) {
                            $('#statusInfo').removeClass('badge-info badge-danger badge-success').addClass(
                                'badge-warning');
                            $('#warningText').text('Stok rendah! Hati-hati dalam penjualan.');
                            $('#warningAlert').show();
                            $('#dangerAlert').hide();
                            $('#successAlert').hide();
                        } else {
                            $('#statusInfo').removeClass('badge-info badge-danger badge-warning').addClass(
                                'badge-success');
                            if (info.perlu_pembelian) {
                                $('#warningText').text(
                                    'Bahan baku perlu pembelian karena stok ≤ Safety Stock.');
                                $('#warningAlert').show();
                                $('#successAlert').hide();
                            } else {
                                $('#successText').text('Stok tersedia dan aman.');
                                $('#successAlert').show();
                                $('#warningAlert').hide();
                            }
                        }
                    }

                    $('#hargaInfo').text('Rp ' + numberFormat(harga));
                    $('#satuanInfo').text(satuan);

                    // Update status Safety Stock
                    if (info && info.status_ss_badge) {
                        $('#statusSSInfo').html(info.status_ss_badge);
                    } else if (info && info.status_bahan_baku_badge) {
                        $('#statusSSInfo').html(info.status_bahan_baku_badge);
                    } else {
                        $('#statusSSInfo').html('<span class="badge badge-secondary">-</span>');
                    }

                    calculateSubtotal();
                } else {
                    resetStatusFields();
                }
            });

            $('#jumlah').on('input', function() {
                calculateSubtotal();

                // Validasi khusus untuk bahan baku dan produk
                const jenis = $('#jenis_item').val();
                const selectedOption = $('#item_id').find('option:selected');
                const info = selectedOption.data('info');
                const jumlah = parseInt($('#jumlah').val()) || 0;

                if (jenis === 'bahan_baku') {
                    const stok = selectedOption.data('stok');

                    if (jumlah > stok) {
                        $('#dangerText').text('Jumlah melebihi stok tersedia! Stok tersedia: ' + stok);
                        $('#dangerAlert').show();
                        $('#warningAlert').hide();
                        $('#successAlert').hide();
                        $('#saveItemBtn').prop('disabled', true);
                    } else if (stok > 0) {
                        $('#dangerAlert').hide();
                        $('#saveItemBtn').prop('disabled', false);
                    }
                } else if (jenis === 'produk' && info && info.bahan_tidak_cukup && info.bahan_tidak_cukup
                    .length > 0) {
                    // Cek untuk produk apakah bahan baku cukup untuk jumlah yang dimasukkan
                    let semuaCukup = true;
                    let pesanError = '';

                    if (jumlah > 10) {
                        $('#warningText').text('Jumlah besar, pastikan bahan baku mencukupi.');
                        $('#warningAlert').show();
                        $('#successAlert').hide();
                    }
                }
            });

            function calculateSubtotal() {
                const jumlah = parseInt($('#jumlah').val()) || 0;
                const hargaText = $('#hargaInfo').text();
                const harga = parseFloat(hargaText.replace('Rp ', '').replace(/\./g, '')) || 0;
                const subtotal = jumlah * harga;

                $('#subtotalInfo').text('Rp ' + numberFormat(subtotal));
            }

            function resetStatusFields() {
                $('#statusInfo').text('-').removeClass('badge-danger badge-warning badge-success')
                    .addClass('badge-info');
                $('#hargaInfo').text('Rp 0').removeClass('badge-success').addClass('badge-success');
                $('#subtotalInfo').text('Rp 0').removeClass('badge-primary').addClass('badge-primary');
                $('#satuanInfo').text('-').removeClass('badge-secondary').addClass('badge-secondary');
                $('#statusSSInfo').html('');
                $('#successAlert').hide();
                $('#infoAlert').hide();
                $('#warningAlert').hide();
                $('#dangerAlert').hide();
                $('#errorAlert').hide();
                $('#saveItemBtn').prop('disabled', false);
                $('#bahanBakuInfo').hide();
                $('#bahanBakuBody').empty();
            }

            $('#saveItemBtn').click(function() {
                const jenisItem = $('#jenis_item').val();
                const itemId = $('#item_id').val();
                const jumlah = $('#jumlah').val();
                const selectedOption = $('#item_id').find('option:selected');
                const info = selectedOption.data('info');

                if (!jenisItem || !itemId || !jumlah) {
                    Swal.fire('Peringatan', 'Harap lengkapi semua field', 'warning');
                    return;
                }

                // Cek jika item disabled
                if (selectedOption.prop('disabled')) {
                    if (jenisItem === 'produk') {
                        Swal.fire('Error',
                            'Produk ini tidak bisa ditambahkan karena bahan baku tidak cukup.', 'error');
                    } else {
                        Swal.fire('Error', 'Bahan baku ini tidak bisa ditambahkan karena stok habis.',
                            'error');
                    }
                    return;
                }

                if (jenisItem === 'bahan_baku') {
                    const stok = selectedOption.data('stok');
                    if (jumlah > stok) {
                        Swal.fire('Error', 'Jumlah melebihi stok tersedia. Stok tersedia: ' + stok,
                            'error');
                        return;
                    }
                }

                if (jenisItem === 'produk') {
                    // Validasi stok bahan baku untuk produk
                    $.ajax({
                        url: '{{ url('admin/penjualan/get-item-info') }}',
                        type: 'GET',
                        data: {
                            jenis_item: jenisItem,
                            item_id: itemId
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                const data = response.data;

                                if (jumlah > 1) {
                                    Swal.fire({
                                        title: 'Validasi Jumlah',
                                        text: 'Apakah Anda yakin jumlah ' + jumlah +
                                            ' unit dapat diproduksi?',
                                        icon: 'question',
                                        showCancelButton: true,
                                        confirmButtonText: 'Ya, lanjutkan',
                                        cancelButtonText: 'Periksa ulang'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            addItemToCart(jenisItem, itemId, jumlah,
                                                selectedOption, info, data);
                                        }
                                    });
                                } else {
                                    // Cek apakah produk bisa diproduksi untuk 1 unit
                                    if (!data.bisa_diproduksi) {
                                        let errorMsg =
                                            'Produk tidak bisa diproduksi karena bahan baku tidak cukup:\n';
                                        data.bahan_tidak_cukup.forEach(function(bahan) {
                                            errorMsg +=
                                                `- ${bahan.nama}: Stok ${bahan.stok_tersedia}, Dibutuhkan ${bahan.dibutuhkan}, Kurang ${bahan.kekurangan}\n`;
                                        });
                                        Swal.fire('Error', errorMsg, 'error');
                                        return;
                                    }

                                    addItemToCart(jenisItem, itemId, jumlah, selectedOption,
                                        info, data);
                                }
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Gagal memvalidasi stok bahan baku';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', errorMessage, 'error');
                        }
                    });
                } else {
                    // Untuk bahan baku langsung tambahkan
                    addItemToCart(jenisItem, itemId, jumlah, selectedOption, info, null);
                }
            });

            function addItemToCart(jenisItem, itemId, jumlah, selectedOption, info, dataFromServer) {
                const harga = parseFloat(selectedOption.data('harga'));
                const subtotal = harga * jumlah;
                const satuan = selectedOption.data('satuan');
                const nama = selectedOption.text().split('(')[0].trim();

                // Ambil informasi bahan baku yang digunakan
                let bahanBakuDigunakan = '';
                if (jenisItem === 'produk' && dataFromServer && dataFromServer.bahan_baku_digunakan) {
                    bahanBakuDigunakan = dataFromServer.bahan_baku_digunakan.map(bahan =>
                        `${bahan.nama}: ${bahan.jumlah_per_unit * jumlah} ${bahan.satuan}`
                    ).join('<br>');
                } else if (jenisItem === 'bahan_baku') {
                    bahanBakuDigunakan = `${nama}: ${jumlah} ${satuan}`;
                }

                const item = {
                    id: itemCounter++,
                    jenis_item: jenisItem,
                    item_id: itemId,
                    nama: nama,
                    jumlah: parseInt(jumlah),
                    harga: harga,
                    subtotal: subtotal,
                    satuan: satuan,
                    bahan_baku_digunakan: bahanBakuDigunakan,
                    status_ss: $('#statusSSInfo').html(),
                    perlu_pembelian: info ? (jenisItem === 'produk' ? info.perlu_pembelian_bahan : info
                        .perlu_pembelian) : false,
                    bisa_diproduksi: jenisItem === 'produk' ? (dataFromServer ? dataFromServer.bisa_diproduksi :
                        info.bisa_diproduksi_satu_unit) : true
                };

                currentItems.push(item);
                updateItemsTable();
                updateTotal();
                $('#addItemModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Item berhasil ditambahkan ke keranjang',
                    timer: 1500,
                    showConfirmButton: false
                });
            }

            function updateItemsTable() {
                const tbody = $('#itemsBody');
                tbody.empty();

                if (currentItems.length === 0) {
                    tbody.append('<tr><td colspan="8" class="text-center text-muted">Belum ada item</td></tr>');
                } else {
                    currentItems.forEach((item, index) => {
                        const row = `
                    <tr>
                        <td>${item.jenis_item === 'produk' ? 'Produk' : 'Bahan Baku'}</td>
                        <td>${item.nama}</td>
                        <td><small>${item.bahan_baku_digunakan || '-'}</small></td>
                        <td class="status-ss-cell">${item.status_ss || '<span class="badge badge-secondary">-</span>'}</td>
                        <td>${item.jumlah} ${item.satuan}</td>
                        <td>Rp ${numberFormat(item.harga)}</td>
                        <td>Rp ${numberFormat(item.subtotal)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-item" data-index="${index}" title="Hapus item">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                        tbody.append(row);
                    });

                    // Re-inisialisasi tooltip untuk baris baru
                    $('[title]').tooltip();
                }
            }

            $(document).on('click', '.remove-item', function() {
                const index = $(this).data('index');
                currentItems.splice(index, 1);
                updateItemsTable();
                updateTotal();
            });

            function updateTotal() {
                const total = currentItems.reduce((sum, item) => sum + item.subtotal, 0);
                $('#totalAmount').text('Rp ' + numberFormat(total));
                $('#total').val('Rp ' + numberFormat(total));
                calculateKembalian();
            }

            $('#bayar').on('input', function() {
                calculateKembalian();
            });

            function calculateKembalian() {
                const total = parseFloat($('#totalAmount').text().replace('Rp ', '').replace(/\./g, '')) || 0;
                const bayar = parseFloat($('#bayar').val()) || 0;
                const kembalian = bayar - total;

                $('#kembalian').val('Rp ' + numberFormat(kembalian > 0 ? kembalian : 0));
                $('#bayarAmount').text('Rp ' + numberFormat(bayar));
                $('#kembalianAmount').text('Rp ' + numberFormat(kembalian > 0 ? kembalian : 0));
            }

            $('#penjualanForm').submit(function(e) {
                e.preventDefault();

                if (currentItems.length === 0) {
                    Swal.fire('Peringatan', 'Harap tambahkan minimal satu item', 'warning');
                    return;
                }

                const bayar = parseFloat($('#bayar').val()) || 0;
                const total = parseFloat($('#totalAmount').text().replace('Rp ', '').replace(/\./g, '')) ||
                    0;

                if (bayar < total) {
                    Swal.fire('Error', 'Jumlah pembayaran kurang dari total', 'error');
                    return;
                }

                // Tampilkan konfirmasi jika ada item yang perlu pembelian
                const itemsPerluPembelian = currentItems.filter(item => item.perlu_pembelian);
                if (itemsPerluPembelian.length > 0) {
                    const itemNames = itemsPerluPembelian.map(item => item.nama).join(', ');
                    Swal.fire({
                        title: 'Peringatan Safety Stock',
                        html: `<p>Beberapa item yang akan dijual memiliki stok ≤ Safety Stock:</p>
                               <p><strong>${itemNames}</strong></p>
                               <p>Anda yakin ingin melanjutkan penjualan?</p>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, lanjutkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            processPenjualan();
                        }
                    });
                } else {
                    processPenjualan();
                }
            });

            function processPenjualan() {
                const formData = {
                    nama_customer: $('#nama_customer').val(),
                    bayar: parseFloat($('#bayar').val()) || 0,
                    items: currentItems.map(item => ({
                        jenis_item: item.jenis_item,
                        item_id: item.item_id,
                        jumlah: item.jumlah
                    }))
                };

                $.ajax({
                    url: '{{ route('admin.penjualan.store') }}',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            let bahanBakuInfo = '';
                            if (response.data.bahan_baku_digunakan && response.data.bahan_baku_digunakan
                                .length > 0) {
                                bahanBakuInfo = '<p><strong>Bahan Baku Digunakan:</strong></p><ul>';
                                response.data.bahan_baku_digunakan.forEach(function(bahan) {
                                    bahanBakuInfo +=
                                        `<li>${bahan.nama}: ${bahan.jumlah} ${bahan.satuan}</li>`;
                                });
                                bahanBakuInfo += '</ul>';
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                html: `
                                <div class="text-left">
                                    <p><strong>Penjualan berhasil disimpan!</strong></p>
                                    <p>Kode: ${response.data.kode_penjualan}</p>
                                    <p>Total: Rp ${numberFormat(response.data.total)}</p>
                                    <p>Bayar: Rp ${numberFormat(response.data.bayar)}</p>
                                    <p>Kembalian: Rp ${numberFormat(response.data.kembalian)}</p>
                                    <p>Kasir: ${response.data.admin_name}</p>
                                    ${bahanBakuInfo}
                                </div>
                            `,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $('#penjualanForm')[0].reset();
                                currentItems = [];
                                updateItemsTable();
                                updateTotal();
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        let errorMessage = 'Terjadi kesalahan';

                        if (response && response.errors) {
                            errorMessage = Object.values(response.errors).join('<br>');
                        } else if (response && response.message) {
                            errorMessage = response.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: errorMessage
                        });
                    }
                });
            }

            $('.view-penjualan').click(function() {
                const id = $(this).data('id');
                currentPenjualanId = id;

                $.get('{{ url('admin/penjualan') }}/' + id, function(response) {
                    if (response.status === 'success') {
                        const penjualan = response.data;
                        let detailHtml = `
                        <div class="detail-summary">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Kode Penjualan:</strong> ${penjualan.kode_penjualan}</p>
                                    <p><strong>Customer:</strong> ${penjualan.nama_customer}</p>
                                    <p><strong>Tanggal:</strong> ${penjualan.tanggal_formatted}</p>
                                    <p><strong>Kasir:</strong> <span class="badge badge-info">${penjualan.admin ? penjualan.admin : 'Unknown'}</span></p>
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
                                        <th>Bahan Baku Digunakan</th>
                                        <th>Harga Satuan</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                        penjualan.detail_penjualan.forEach(detail => {
                            let bahanBakuHtml = '';
                            if (detail.bahan_baku_digunakan && detail.bahan_baku_digunakan
                                .length > 0) {
                                bahanBakuHtml =
                                    '<ul class="list-unstyled" style="font-size: 11px;">';
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
                                <td>${bahanBakuHtml}</td>
                                <td>${detail.harga_sat_formatted}</td>
                                <td>${detail.sub_total_formatted}</td>
                            </tr>
                        `;
                        });

                        detailHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;

                        // Tambahkan ringkasan bahan baku
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

            $('.print-penjualan').click(function() {
                const id = $(this).data('id');
                window.open('{{ url('admin/penjualan') }}/' + id + '/print', '_blank');
            });

            $('#printNotaBtn').click(function() {
                if (currentPenjualanId) {
                    window.open('{{ url('admin/penjualan') }}/' + currentPenjualanId + '/print', '_blank');
                }
            });

            $('.delete-penjualan').click(function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data penjualan akan dihapus dan stok bahan baku akan dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url('admin/penjualan') }}/' + id,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: response.message
                                    }).then(() => {
                                        location.reload();
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Terjadi kesalahan saat menghapus data'
                                });
                            }
                        });
                    }
                });
            });

            function numberFormat(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            updateItemsTable();
        });
    </script>
@endpush
