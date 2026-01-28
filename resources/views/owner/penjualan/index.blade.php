@extends('layoutsAPP.deskapp')

@section('title', 'Penjualan - Owner')

@section('content')
    <div class="pd-ltr-20">
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Data Penjualan</h4>
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

        <!-- Form Filter -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card-box">
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
                                        <th>Jumlah Terjual</th>
                                        <th>Total Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($produkTerlaris as $index => $produk)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $produk['nama'] }}</td>
                                            <td>{{ $produk['total_terjual'] }} {{ $produk['satuan'] }}</td>
                                            <td>Rp {{ number_format($produk['total_pendapatan'], 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">Tidak ada data produk terjual</td>
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
                                        <th>Jumlah Digunakan</th>
                                        <th>Satuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bahanBakuTerlaris as $index => $bahan)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $bahan['nama'] }}</td>
                                            <td>{{ number_format($bahan['total_penggunaan'], 2) }}</td>
                                            <td>{{ $bahan['satuan'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">Tidak ada data bahan baku digunakan</td>
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
    </div>

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
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Pilih Bahan Baku',
                allowClear: true
            });

            let currentPenjualanId = null;

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

            $('#printNotaBtn').click(function() {
                if (currentPenjualanId) {
                    window.open('{{ url('owner/penjualan') }}/' + currentPenjualanId + '/print', '_blank');
                }
            });
        });
    </script>
@endpush
