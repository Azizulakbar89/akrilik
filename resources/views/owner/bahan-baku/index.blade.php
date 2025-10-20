@extends('layoutsAPP.deskapp')

@section('title', 'Bahan Baku - Owner')

@section('content')
    <div class="page-header">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title">
                    <h4>Bahan Baku</h4>
                </div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('owner.dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Bahan Baku</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 col-sm-12 text-right">
                <button class="btn btn-primary" onclick="printTable()">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
                <button class="btn btn-success" data-toggle="modal" data-target="#createBahanBakuModal">
                    <i class="fas fa-plus"></i> Tambah Bahan Baku
                </button>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card-box mb-30">
        <div class="pd-20">
            <h4 class="text-blue h4">Daftar Bahan Baku</h4>
            <p class="mb-0">Parameter stok dihitung otomatis berdasarkan data penggunaan 7 hari terakhir</p>
        </div>
        <div class="pb-20">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="data-table table stripe hover nowrap" style="width: 100%; min-width: 1200px;">
                    <thead>
                        <tr>
                            <th class="table-plus datatable-nosort">No</th>
                            <th>Foto</th>
                            <th>Nama Bahan Baku</th>
                            <th>Satuan</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Lead Time</th>
                            <th>Safety Stock</th>
                            <th>ROP</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Status Stok</th>
                            <th class="datatable-nosort">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bahanBaku as $index => $bb)
                            <tr>
                                <td class="table-plus">{{ $index + 1 }}</td>
                                <td>
                                    @if ($bb->foto)
                                        <img src="{{ asset('storage/' . $bb->foto) }}" alt="{{ $bb->nama }}"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    @else
                                        <div
                                            style="width: 50px; height: 50px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                            <i class="fas fa-image" style="color: #6c757d;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $bb->nama }}</td>
                                <td>{{ $bb->satuan }}</td>
                                <td>Rp {{ number_format($bb->harga_beli, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($bb->harga_jual, 0, ',', '.') }}</td>
                                <td>
                                    <span
                                        class="font-weight-bold {{ $bb->stok <= $bb->min ? 'text-danger' : 'text-success' }}">
                                        {{ $bb->stok }}
                                    </span>
                                </td>
                                <td>{{ $bb->lead_time }} hari</td>
                                <td>{{ $bb->safety_stock }}</td>
                                <td>{{ $bb->rop }}</td>
                                <td>{{ $bb->min }}</td>
                                <td>{{ $bb->max }}</td>
                                <td>
                                    @if ($bb->stok <= $bb->min)
                                        <span class="badge badge-danger">Perlu Pembelian</span>
                                    @elseif ($bb->stok <= $bb->safety_stock)
                                        <span class="badge badge-warning">Stok Menipis</span>
                                    @else
                                        <span class="badge badge-success">Aman</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                            href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item show-btn" href="#" data-id="{{ $bb->id }}">
                                                <i class="fas fa-eye"></i> Lihat Detail
                                            </a>
                                            <a class="dropdown-item calculation-detail-btn" href="#"
                                                data-id="{{ $bb->id }}">
                                                <i class="fas fa-calculator"></i> Detail Perhitungan
                                            </a>
                                            <a class="dropdown-item edit-btn" href="#" data-id="{{ $bb->id }}"
                                                data-toggle="modal" data-target="#editBahanBakuModal">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger delete-btn" href="#"
                                                data-id="{{ $bb->id }}" data-nama="{{ $bb->nama }}">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
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
@endsection

@push('modals')
    <div class="modal fade" id="createBahanBakuModal" tabindex="-1" role="dialog"
        aria-labelledby="createBahanBakuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="createBahanBakuModalLabel">Tambah Bahan Baku</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createForm" method="POST" action="{{ route('owner.bahan-baku.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Isi parameter stok sesuai dengan kebutuhan. Parameter ini
                            akan digunakan untuk sistem peringatan stok.
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama">Nama Bahan Baku <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="satuan">Satuan <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="satuan" required>
                                        <option value="">Pilih Satuan</option>
                                        <option value="Cm">Cm</option>
                                        <option value="gram">gram</option>
                                        <option value="liter">liter</option>
                                        <option value="pcs">pcs</option>
                                        <option value="kg">kg</option>
                                        <option value="m">m</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="harga_beli">Harga Beli <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="harga_beli"
                                        name="harga_beli" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="harga_jual">Harga Jual <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="harga_jual"
                                        name="harga_jual" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="stok">Stok Awal <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="stok" name="stok"
                                        value="0" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lead_time">Lead Time (hari) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="lead_time" name="lead_time"
                                        value="1" required min="1">
                                    <small class="form-text text-muted">Waktu tunggu pesanan sampai diterima</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="safety_stock">Safety Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="safety_stock" name="safety_stock"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rop">ROP <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="rop" name="rop" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="min">Min Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="min" name="min" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max">Max Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="max" name="max" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="foto">Foto Bahan Baku</label>
                            <input type="file" class="form-control-file" id="foto" name="foto"
                                accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editBahanBakuModal" tabindex="-1" role="dialog"
        aria-labelledby="editBahanBakuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editBahanBakuModalLabel">Edit Bahan Baku</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Update parameter stok sesuai dengan kebutuhan.
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_nama">Nama Bahan Baku <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_nama" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_satuan">Satuan <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="satuan" id="edit_satuan" required>
                                        <option value="">Pilih Satuan</option>
                                        <option value="Cm">Cm</option>
                                        <option value="gram">gram</option>
                                        <option value="liter">liter</option>
                                        <option value="pcs">pcs</option>
                                        <option value="kg">kg</option>
                                        <option value="m">m</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_harga_beli">Harga Beli <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="edit_harga_beli"
                                        name="harga_beli" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_harga_jual">Harga Jual <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="edit_harga_jual"
                                        name="harga_jual" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_stok">Stok <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_stok" name="stok" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_lead_time">Lead Time (hari) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_lead_time" name="lead_time"
                                        required min="1">
                                    <small class="form-text text-muted">Waktu tunggu pesanan sampai diterima</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_safety_stock">Safety Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_safety_stock"
                                        name="safety_stock" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_rop">ROP <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_rop" name="rop" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_min">Min Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_min" name="min" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_max">Max Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_max" name="max" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_foto">Foto Bahan Baku</label>
                            <input type="file" class="form-control-file" id="edit_foto" name="foto"
                                accept="image/*">
                            <div id="edit_foto_preview" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="showBahanBakuModal" tabindex="-1" role="dialog"
        aria-labelledby="showBahanBakuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="showBahanBakuModalLabel">Detail Bahan Baku</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div id="show_foto" class="text-center mb-3"></div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="font-weight-bold">Nama Bahan Baku</label>
                                <p id="show_nama" class="form-control-plaintext font-weight-bold text-primary"></p>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Satuan</label>
                                        <p id="show_satuan" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Stok</label>
                                        <p id="show_stok" class="form-control-plaintext font-weight-bold"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Harga Beli</label>
                                        <p id="show_harga_beli" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Harga Jual</label>
                                        <p id="show_harga_jual" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Lead Time</label>
                                        <p id="show_lead_time" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Status Stok</label>
                                        <p id="show_status" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Safety Stock</label>
                                        <p id="show_safety_stock" class="form-control-plaintext font-weight-bold"></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">ROP</label>
                                        <p id="show_rop" class="form-control-plaintext font-weight-bold"></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Min Stock</label>
                                        <p id="show_min" class="form-control-plaintext font-weight-bold"></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Max Stock</label>
                                        <p id="show_max" class="form-control-plaintext font-weight-bold"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Analisis Stok</label>
                                        <div id="show_analisis" class="alert alert-info">
                                            <small>
                                                <i class="fas fa-info-circle"></i>
                                                <span id="analisis_text"></span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="printDetail()">
                        <i class="fas fa-print"></i> Cetak Detail
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="calculationDetailModal" tabindex="-1" role="dialog"
        aria-labelledby="calculationDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="calculationDetailModalLabel">Detail Perhitungan Parameter Stok</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="calculationDetailContent">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus bahan baku <strong id="deleteNama"></strong>?</p>
                    <p class="text-danger"><small>Data yang dihapus tidak dapat dikembalikan!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('styles')
    <style>
        .table-responsive {
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .data-table {
            margin-bottom: 0 !important;
        }

        .data-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            white-space: nowrap;
        }

        .data-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .widget-style1 .widget-data .h4 {
            font-size: 24px;
            font-weight: 700;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .calculation-step {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
        }

        .calculation-result {
            background: #e7f3ff;
            border-radius: 5px;
            padding: 15px;
            margin-top: 15px;
            border-left: 4px solid #28a745;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .card-box {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            console.log('Owner Bahan Baku - Document ready');

            var table = $('.data-table').DataTable({
                responsive: false,
                scrollX: true,
                scrollCollapse: true,
                fixedColumns: {
                    left: 1,
                    right: 1
                },
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampil _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Selanjutnya"
                    }
                },
                columnDefs: [{
                    orderable: false,
                    targets: 'datatable-nosort'
                }],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                order: [
                    [0, 'asc']
                ]
            });

            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Pilih Satuan',
                allowClear: true
            });

            // Calculation Detail Button
            $(document).on('click', '.calculation-detail-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var id = $(this).data('id');
                $(this).closest('.dropdown-menu').prev('.dropdown-toggle').dropdown('toggle');

                Swal.fire({
                    title: 'Memuat...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ url('owner/bahan-baku') }}/' + id + '/calculation-detail',
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            var data = response.data;
                            var html = '';

                            html += '<div class="mb-4">';
                            html += '<h5>Informasi Bahan Baku</h5>';
                            html += '<p><strong>Nama:</strong> ' + data.bahan_baku + '</p>';
                            html += '<p><strong>Lead Time:</strong> ' + data.lead_time + '</p>';
                            html += '</div>';

                            html += '<div class="mb-4">';
                            html += '<h5>Statistik Penggunaan (7 Hari Terakhir)</h5>';
                            html += '<div class="row">';
                            html += '<div class="col-md-6"><p><strong>Total Keluar:</strong> ' +
                                data.statistik_penggunaan.total_keluar + ' ' + data.bahan_baku +
                                '</p></div>';
                            html +=
                                '<div class="col-md-6"><p><strong>Jumlah Transaksi:</strong> ' +
                                data.statistik_penggunaan.count_keluar + ' kali</p></div>';
                            html +=
                                '<div class="col-md-6"><p><strong>Rata-rata per Hari:</strong> ' +
                                data.statistik_penggunaan.rata_rata_per_hari + ' ' + data
                                .bahan_baku + '</p></div>';
                            html +=
                                '<div class="col-md-6"><p><strong>Maksimum per Hari:</strong> ' +
                                data.statistik_penggunaan.maks_keluar_per_hari + ' ' + data
                                .bahan_baku + '</p></div>';
                            html += '</div>';
                            html += '</div>';

                            html += '<div class="mb-4">';
                            html += '<h5>Detail Perhitungan</h5>';

                            html += '<div class="calculation-step">';
                            html += '<h6>Safety Stock (SS)</h6>';
                            html +=
                                '<p class="mb-1">Rumus: (Pemakaian Maksimum - Rata-rata) × Lead Time</p>';
                            html += '<p class="mb-0"><strong>' + data.perhitungan.safety_stock +
                                '</strong></p>';
                            html += '</div>';

                            html += '<div class="calculation-step">';
                            html += '<h6>Minimal Stock (Min)</h6>';
                            html +=
                                '<p class="mb-1">Rumus: (Rata-rata × Lead Time) + Safety Stock</p>';
                            html += '<p class="mb-0"><strong>' + data.perhitungan.min_stock +
                                '</strong></p>';
                            html += '</div>';

                            html += '<div class="calculation-step">';
                            html += '<h6>Maksimal Stock (Max)</h6>';
                            html +=
                                '<p class="mb-1">Rumus: 2 × (Rata-rata × Lead Time) + Safety Stock</p>';
                            html += '<p class="mb-0"><strong>' + data.perhitungan.max_stock +
                                '</strong></p>';
                            html += '</div>';

                            html += '<div class="calculation-step">';
                            html += '<h6>Reorder Point (ROP)</h6>';
                            html += '<p class="mb-1">Rumus: Maksimal Stock - Minimal Stock</p>';
                            html += '<p class="mb-0"><strong>' + data.perhitungan.rop +
                                '</strong></p>';
                            html += '</div>';
                            html += '</div>';

                            html += '<div class="calculation-result">';
                            html += '<h5>Hasil Perhitungan</h5>';
                            html += '<div class="row">';
                            html += '<div class="col-md-3"><p><strong>Safety Stock:</strong> ' +
                                data.hasil.safety_stock + '</p></div>';
                            html += '<div class="col-md-3"><p><strong>ROP:</strong> ' + data
                                .hasil.rop + '</p></div>';
                            html += '<div class="col-md-3"><p><strong>Min Stock:</strong> ' +
                                data.hasil.min + '</p></div>';
                            html += '<div class="col-md-3"><p><strong>Max Stock:</strong> ' +
                                data.hasil.max + '</p></div>';
                            html += '</div>';
                            html += '</div>';

                            $('#calculationDetailContent').html(html);
                            $('#calculationDetailModal').modal('show');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memuat detail perhitungan'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Gagal memuat detail perhitungan'
                        });
                    }
                });
            });

            $(document).on('click', '.show-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var id = $(this).data('id');
                console.log('Show button clicked for ID:', id);

                $(this).closest('.dropdown-menu').prev('.dropdown-toggle').dropdown('toggle');

                Swal.fire({
                    title: 'Memuat...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ url('owner/bahan-baku') }}/' + id,
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        console.log('Response received:', response);

                        if (response.status === 'success') {
                            var data = response.data;

                            $('#show_nama').text(data.nama || '-');
                            $('#show_satuan').text(data.satuan || '-');
                            $('#show_harga_beli').text(data.harga_beli ? 'Rp ' + parseFloat(data
                                .harga_beli).toLocaleString('id-ID') : '-');
                            $('#show_harga_jual').text(data.harga_jual ? 'Rp ' + parseFloat(data
                                .harga_jual).toLocaleString('id-ID') : '-');
                            $('#show_stok').text(data.stok || '0');
                            $('#show_lead_time').text(data.lead_time ? data.lead_time +
                                ' hari' : '-');
                            $('#show_safety_stock').text(data.safety_stock || '0');
                            $('#show_rop').text(data.rop || '0');
                            $('#show_min').text(data.min || '0');
                            $('#show_max').text(data.max || '0');

                            var statusText, statusClass;
                            if (data.stok <= data.min) {
                                statusText =
                                    '<span class="badge badge-danger">Perlu Pembelian</span>';
                                statusClass = 'text-danger';
                            } else if (data.stok <= data.safety_stock) {
                                statusText =
                                    '<span class="badge badge-warning">Stok Menipis</span>';
                                statusClass = 'text-warning';
                            } else {
                                statusText = '<span class="badge badge-success">Aman</span>';
                                statusClass = 'text-success';
                            }
                            $('#show_status').html(statusText);
                            $('#show_stok').addClass(statusClass);

                            var analisisText = '';
                            if (data.stok <= data.min) {
                                analisisText =
                                    'Stok bahan baku berada di bawah titik pemesanan ulang. Segera lakukan pembelian untuk menghindari kekurangan stok.';
                            } else if (data.stok <= data.safety_stock) {
                                analisisText =
                                    'Stok bahan baku mendekati safety stock. Perlu dipantau secara berkala.';
                            } else {
                                analisisText =
                                    'Stok bahan baku dalam kondisi aman dan mencukupi untuk produksi.';
                            }
                            $('#analisis_text').text(analisisText);

                            $('#show_foto').html(data.foto ?
                                `<img src="{{ asset('storage') }}/${data.foto}" alt="Foto Bahan Baku" style="max-width: 200px; height: auto; border-radius: 5px; border: 2px solid #dee2e6;">` :
                                '<div style="width: 200px; height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 5px; border: 2px solid #dee2e6;"><i class="fas fa-image" style="font-size: 48px; color: #6c757d;"></i><br><small class="text-muted">Tidak ada foto</small></div>'
                            );

                            $('#showBahanBakuModal').modal('show');
                        } else {
                            console.error('Response status not success:', response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memuat data bahan baku'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.error('AJAX Error:', status, error, xhr.responseJSON);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Gagal memuat data bahan baku'
                        });
                    }
                });
            });

            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var id = $(this).data('id');
                console.log('Edit button clicked for ID:', id);

                $(this).closest('.dropdown-menu').prev('.dropdown-toggle').dropdown('toggle');

                Swal.fire({
                    title: 'Memuat...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ url('owner/bahan-baku') }}/' + id,
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        console.log('Response received:', response);

                        if (response.status === 'success') {
                            var data = response.data;

                            $('#editForm').attr('action', '{{ url('owner/bahan-baku') }}/' +
                                id);
                            $('#edit_nama').val(data.nama || '');
                            $('#edit_satuan').val(data.satuan || '').trigger('change');
                            $('#edit_harga_beli').val(data.harga_beli || '');
                            $('#edit_harga_jual').val(data.harga_jual || '');
                            $('#edit_stok').val(data.stok || '');
                            $('#edit_lead_time').val(data.lead_time || '');
                            $('#edit_safety_stock').val(data.safety_stock || '');
                            $('#edit_rop').val(data.rop || '');
                            $('#edit_min').val(data.min || '');
                            $('#edit_max').val(data.max || '');

                            $('#edit_foto_preview').html(data.foto ?
                                `<img src="{{ asset('storage') }}/${data.foto}" alt="Foto Bahan Baku" style="max-width: 200px; height: auto; border-radius: 5px; border: 2px solid #dee2e6;">` :
                                '<div style="width: 200px; height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 5px; border: 2px solid #dee2e6;"><i class="fas fa-image" style="font-size: 48px; color: #6c757d;"></i><br><small class="text-muted">Tidak ada foto</small></div>'
                            );

                            $('#editBahanBakuModal').modal('show');
                        } else {
                            console.error('Response status not success:', response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memuat data bahan baku'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.error('AJAX Error:', status, error, xhr.responseJSON);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Gagal memuat data bahan baku'
                        });
                    }
                });
            });

            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var id = $(this).data('id');
                var nama = $(this).data('nama');

                $(this).closest('.dropdown-menu').prev('.dropdown-toggle').dropdown('toggle');

                $('#deleteNama').text(nama);
                $('#deleteForm').attr('action', '{{ url('owner/bahan-baku') }}/' + id);

                $('#deleteModal').modal('show');
            });
        });

        function printTable() {
            var originalContents = document.body.innerHTML;
            var printContents = document.querySelector('.card-box').innerHTML;

            document.body.innerHTML = `
                <html>
                    <head>
                        <title>Laporan Bahan Baku - {{ \Carbon\Carbon::now()->format('d F Y') }}</title>
                        <style>
                            body { font-family: Arial, sans-serif; }
                            table { width: 100%; border-collapse: collapse; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background-color: #f8f9fa; }
                            .text-center { text-align: center; }
                            .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
                            .badge-warning { background-color: #ffc107; color: #212529; }
                            .badge-success { background-color: #28a745; color: white; }
                            .badge-info { background-color: #17a2b8; color: white; }
                        </style>
                    </head>
                    <body>
                        <h2 class="text-center">Laporan Bahan Baku</h2>
                        <p class="text-center">Tanggal: {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
                        ${printContents}
                    </body>
                </html>
            `;

            window.print();
            document.body.innerHTML = originalContents;
            window.location.reload();
        }

        function printDetail() {
            var originalContents = document.body.innerHTML;
            var printContents = document.querySelector('#showBahanBakuModal .modal-content').innerHTML;

            document.body.innerHTML = `
                <html>
                    <head>
                        <title>Detail Bahan Baku - {{ \Carbon\Carbon::now()->format('d F Y') }}</title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 20px; }
                            .modal-header, .modal-footer { display: none; }
                            .form-group { margin-bottom: 15px; }
                            .font-weight-bold { font-weight: bold; }
                            .text-primary { color: #007bff; }
                            .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
                            .badge-warning { background-color: #ffc107; color: #212529; }
                            .badge-success { background-color: #28a745; color: white; }
                            .badge-info { background-color: #17a2b8; color: white; }
                            .alert { padding: 10px; border-radius: 4px; }
                            .alert-info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
                        </style>
                    </head>
                    <body>
                        ${printContents}
                    </body>
                </html>
            `;

            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>
@endpush
