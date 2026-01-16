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
                <button class="btn btn-primary" onclick="printLaporan()">
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
        </div>
        <div class="pb-20">
            <div class="table-responsive-scroll">
                <table class="data-table table stripe hover nowrap" id="bahanBakuTable">
                    <thead>
                        <tr>
                            <th class="text-center" width="40">No</th>
                            <th class="text-center" width="60">Foto</th>
                            <th width="120">Nama Bahan Baku</th>
                            <th class="text-center" width="60">Satuan</th>
                            <th class="text-right" width="100">Harga Beli</th>
                            <th class="text-right" width="100">Harga Jual</th>
                            <th class="text-center" width="70">Lead Time Rata-rata</th>
                            <th class="text-center" width="70">Lead Time Maksimal</th>
                            <th class="text-center" width="60">Stok</th>
                            <th class="text-center" width="70">Safety Stock</th>
                            <th class="text-center" width="70">ROP</th>
                            <th class="text-center" width="60">Min</th>
                            <th class="text-center" width="60">Max</th>
                            <th class="text-center" width="100">Status Stok</th>
                            <th class="text-center" width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bahanBaku as $index => $bb)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">
                                    @if ($bb->foto)
                                        <img src="{{ asset('storage/' . $bb->foto) }}" alt="{{ $bb->nama }}"
                                            class="img-thumbnail-sm">
                                    @else
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $bb->nama }}</td>
                                <td class="text-center">{{ $bb->satuan }}</td>
                                <td class="text-right">Rp {{ number_format($bb->harga_beli, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($bb->harga_jual, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="lead-time-badge">{{ $bb->lead_time }} hari</span>
                                </td>
                                <td class="text-center">
                                    <span class="lead-time-badge max">{{ $bb->lead_time_max }} hari</span>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="stok-indicator {{ $bb->stok <= $bb->min ? 'danger' : ($bb->stok <= $bb->safety_stock ? 'warning' : 'success') }}">
                                        {{ $bb->stok }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $bb->safety_stock }}</td>
                                <td class="text-center">{{ $bb->rop }}</td>
                                <td class="text-center">{{ $bb->min }}</td>
                                <td class="text-center">{{ $bb->max }}</td>
                                <td class="text-center">
                                    @if ($bb->stok <= $bb->min)
                                        <span class="badge badge-danger">Perlu Pembelian</span>
                                    @elseif ($bb->stok <= $bb->safety_stock)
                                        <span class="badge badge-warning">Stok Menipis</span>
                                    @else
                                        <span class="badge badge-success">Aman</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-info show-btn" data-id="{{ $bb->id }}"
                                            title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning calculation-detail-btn"
                                            data-id="{{ $bb->id }}" title="Detail Perhitungan">
                                            <i class="fas fa-calculator"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary edit-btn" data-id="{{ $bb->id }}"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $bb->id }}"
                                            data-nama="{{ $bb->nama }}" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama">Nama Bahan Baku <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama" name="nama" required>
                                    <div class="invalid-feedback"></div>
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
                                        <option value="rim">rim</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="harga_beli">Harga Beli <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="harga_beli"
                                        name="harga_beli" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="harga_jual">Harga Jual <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="harga_jual"
                                        name="harga_jual" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="foto">Foto Bahan Baku</label>
                            <input type="file" class="form-control-file" id="foto" name="foto"
                                accept="image/*">
                            <div class="invalid-feedback"></div>
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
                            <i class="fas fa-info-circle"></i>
                            <strong>Catatan:</strong> Stok hanya bisa diubah melalui transaksi. Safety Stock, ROP, Min, dan
                            Max akan dihitung ulang otomatis berdasarkan data penggunaan 30 hari terakhir.
                        </div>
                        <input type="hidden" id="edit_id" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_nama">Nama Bahan Baku <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_nama" name="nama" required>
                                    <div class="invalid-feedback"></div>
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
                                        <option value="rim">rim</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_harga_beli">Harga Beli <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="edit_harga_beli"
                                        name="harga_beli" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_harga_jual">Harga Jual <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="edit_harga_jual"
                                        name="harga_jual" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_lead_time">Lead Time Rata-rata (hari) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_lead_time" name="lead_time"
                                        min="0" required>
                                    <small class="form-text text-muted">Waktu rata-rata pengiriman bahan baku</small>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_lead_time_max">Lead Time Maksimal (hari) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_lead_time_max"
                                        name="lead_time_max" min="0" required>
                                    <small class="form-text text-muted">Waktu maksimal pengiriman bahan baku</small>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_foto">Foto Bahan Baku</label>
                            <input type="file" class="form-control-file" id="edit_foto" name="foto"
                                accept="image/*">
                            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah foto</small>
                            <div class="invalid-feedback"></div>
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
                                <label>Nama Bahan Baku</label>
                                <p id="show_nama" class="form-control-plaintext font-weight-bold"></p>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Satuan</label>
                                        <p id="show_satuan" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Stok</label>
                                        <p id="show_stok" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Harga Beli</label>
                                        <p id="show_harga_beli" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Harga Jual</label>
                                        <p id="show_harga_jual" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Lead Time Rata-rata</label>
                                        <p id="show_lead_time" class="form-control-plaintext font-weight-bold"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Lead Time Maksimal</label>
                                        <p id="show_lead_time_max" class="form-control-plaintext font-weight-bold"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status Stok</label>
                                        <p id="show_status" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Terakhir Diperbarui</label>
                                        <p id="show_updated_at" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Safety Stock</label>
                                        <p id="show_safety_stock" class="form-control-plaintext font-weight-bold"></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>ROP</label>
                                        <p id="show_rop" class="form-control-plaintext font-weight-bold"></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Min Stock</label>
                                        <p id="show_min" class="form-control-plaintext font-weight-bold"></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Max Stock</label>
                                        <p id="show_max" class="form-control-plaintext font-weight-bold"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
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
                    <div id="calculationDetailContent"></div>
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
        .table-responsive-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        #bahanBakuTable {
            width: 100%;
            min-width: 1500px;
            margin-bottom: 0;
        }

        #bahanBakuTable th,
        #bahanBakuTable td {
            white-space: nowrap;
            padding: 8px 10px;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }

        #bahanBakuTable th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .img-thumbnail-sm {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .no-image {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            color: #6c757d;
        }

        .stok-indicator {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }

        .stok-indicator.success {
            background-color: #d4edda;
            color: #155724;
        }

        .stok-indicator.warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .stok-indicator.danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .lead-time-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            background-color: #e7f3ff;
            color: #004085;
            border: 1px solid #b8daff;
        }

        .lead-time-badge.max {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .action-buttons .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
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

        .formula-highlight {
            background: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            margin: 10px 0;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .card-box,
            .card-box * {
                visibility: visible;
            }

            .card-box {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
                border: none;
            }

            .pd-20,
            .pb-20 {
                padding: 0 !important;
            }

            .action-buttons,
            .page-header,
            .breadcrumb,
            .alert,
            .btn {
                display: none !important;
            }

            .table-responsive-scroll {
                overflow: visible;
                border: none;
            }

            #bahanBakuTable {
                min-width: 100%;
                border-collapse: collapse;
            }

            #bahanBakuTable th,
            #bahanBakuTable td {
                border: 1px solid #000;
            }

            .stok-indicator,
            .lead-time-badge {
                border: 1px solid #000;
            }
        }

        .table-responsive-scroll::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive-scroll::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .table-responsive-scroll::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#bahanBakuTable').DataTable({
                responsive: false,
                scrollX: true,
                scrollCollapse: true,
                paging: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Semua"]
                ],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampil _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Selanjutnya"
                    },
                    emptyTable: "Tidak ada data bahan baku",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data"
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                        orderable: false,
                        targets: [1, 14]
                    },
                    {
                        className: 'text-center',
                        targets: [0, 1, 3, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                    },
                    {
                        className: 'text-right',
                        targets: [4, 5]
                    }
                ]
            });

            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Pilih Satuan',
                allowClear: true
            });

            $(document).on('click', '.show-btn', function(e) {
                e.preventDefault();
                var id = $(this).data('id');

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
                        if (response.status === 'success') {
                            var data = response.data;

                            $('#show_nama').text(data.nama || '-');
                            $('#show_satuan').text(data.satuan || '-');
                            $('#show_harga_beli').text(data.harga_beli ? 'Rp ' + parseFloat(data
                                .harga_beli).toLocaleString('id-ID') : '-');
                            $('#show_harga_jual').text(data.harga_jual ? 'Rp ' + parseFloat(data
                                .harga_jual).toLocaleString('id-ID') : '-');
                            $('#show_stok').text(data.stok || '0');
                            $('#show_lead_time').text((data.lead_time || '0') + ' hari');
                            $('#show_lead_time_max').text((data.lead_time_max || '0') +
                                ' hari');
                            $('#show_safety_stock').text(data.safety_stock || '0');
                            $('#show_rop').text(data.rop || '0');
                            $('#show_min').text(data.min || '0');
                            $('#show_max').text(data.max || '0');

                            var statusText = data.stok <= data.min ?
                                '<span class="badge badge-danger">Perlu Pembelian</span>' :
                                data.stok <= data.safety_stock ?
                                '<span class="badge badge-warning">Stok Menipis</span>' :
                                '<span class="badge badge-success">Aman</span>';
                            $('#show_status').html(statusText);

                            $('#show_updated_at').text(data.updated_at ?
                                new Date(data.updated_at).toLocaleDateString('id-ID', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) : '-');

                            $('#show_foto').html(data.foto ?
                                `<img src="{{ asset('storage') }}/${data.foto}" alt="Foto Bahan Baku" style="max-width: 200px; height: auto; border-radius: 5px;">` :
                                '<div style="width: 200px; height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 5px;"><i class="fas fa-image" style="font-size: 48px; color: #6c757d;"></i></div>'
                            );

                            $('#showBahanBakuModal').modal('show');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat data'
                        });
                    }
                });
            });

            $(document).on('click', '.calculation-detail-btn', function(e) {
                e.preventDefault();
                var id = $(this).data('id');

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
                            html += '<p><strong>Lead Time Rata-rata:</strong> ' + data
                                .lead_time_rata_rata + '</p>';
                            html += '<p><strong>Lead Time Maksimal:</strong> ' + data
                                .lead_time_maksimum + '</p>';
                            html += '</div>';

                            if (data.memiliki_data) {
                                html += '<div class="mb-4">';
                                html += '<h5>Statistik Penggunaan (30 Hari Terakhir)</h5>';
                                html += '<div class="row">';
                                html +=
                                    '<div class="col-md-6"><p><strong>Total Keluar:</strong> ' +
                                    data.statistik_penggunaan.total_keluar + '</p></div>';
                                html +=
                                    '<div class="col-md-6"><p><strong>Hari Aktif:</strong> ' +
                                    data.statistik_penggunaan.hari_aktif + ' hari</p></div>';
                                html +=
                                    '<div class="col-md-6"><p><strong>Total Hari Analisis:</strong> ' +
                                    data.statistik_penggunaan.total_hari_analisis +
                                    ' hari</p></div>';
                                html +=
                                    '<div class="col-md-6"><p><strong>Rata-rata per Hari:</strong> ' +
                                    data.statistik_penggunaan.rata_rata_per_hari + '</p></div>';
                                html +=
                                    '<div class="col-md-6"><p><strong>Maksimum per Hari:</strong> ' +
                                    data.statistik_penggunaan.maks_keluar_per_hari +
                                    '</p></div>';
                                html += '</div>';
                                html += '</div>';

                                html += '<div class="mb-4">';
                                html += '<h5>Detail Perhitungan</h5>';

                                html += '<div class="formula-highlight">';
                                html += '<h6>Rumus Safety Stock:</h6>';
                                html +=
                                    '<p class="mb-1"><strong>(Penjualan Maksimal Harian × Lead Time Maksimum) - (Penjualan Harian Rata-rata × Lead Time Rata-rata)</strong></p>';
                                html += '</div>';

                                html += '<div class="calculation-step">';
                                html += '<h6>Safety Stock (SS)</h6>';
                                html += '<p class="mb-1">' + data.perhitungan.safety_stock +
                                    '</p>';
                                html += '</div>';

                                html += '<div class="calculation-step">';
                                html += '<h6>Minimal Stock (Min)</h6>';
                                html += '<p class="mb-1">' + data.perhitungan.min_stock +
                                    '</p>';
                                html += '</div>';

                                html += '<div class="calculation-step">';
                                html += '<h6>Maksimal Stock (Max)</h6>';
                                html += '<p class="mb-1">' + data.perhitungan.max_stock +
                                    '</p>';
                                html += '</div>';

                                html += '<div class="calculation-step">';
                                html += '<h6>Reorder Point (ROP)</h6>';
                                html += '<p class="mb-1">Rumus: Max - Min</p>';
                                html += '<p class="mb-1">' + data.perhitungan.rop + '</p>';
                                html += '</div>';
                                html += '</div>';

                                html += '<div class="calculation-result">';
                                html += '<h5>Hasil Perhitungan</h5>';
                                html += '<div class="row">';
                                html +=
                                    '<div class="col-md-3"><p><strong>Safety Stock:</strong> ' +
                                    data.hasil.safety_stock + '</p></div>';
                                html += '<div class="col-md-3"><p><strong>ROP:</strong> ' + data
                                    .hasil.rop + '</p></div>';
                                html +=
                                    '<div class="col-md-3"><p><strong>Min Stock:</strong> ' +
                                    data.hasil.min + '</p></div>';
                                html +=
                                    '<div class="col-md-3"><p><strong>Max Stock:</strong> ' +
                                    data.hasil.max + '</p></div>';
                                html += '</div>';
                                html += '</div>';
                            } else {
                                html += '<div class="alert alert-warning">';
                                html +=
                                    '<i class="fas fa-exclamation-triangle"></i> Belum ada data penggunaan dalam 30 hari terakhir. Parameter stok akan dihitung otomatis setelah ada data penggunaan.';
                                html += '</div>';
                            }

                            $('#calculationDetailContent').html(html);
                            $('#calculationDetailModal').modal('show');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat detail perhitungan'
                        });
                    }
                });
            });

            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                var id = $(this).data('id');

                Swal.fire({
                    title: 'Memuat...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ url('owner/bahan-baku') }}/' + id + '/edit',
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            var data = response.data;

                            $('#edit_id').val(data.id);
                            $('#edit_nama').val(data.nama);
                            $('#edit_satuan').val(data.satuan).trigger('change');
                            $('#edit_harga_beli').val(data.harga_beli);
                            $('#edit_harga_jual').val(data.harga_jual);
                            $('#edit_lead_time').val(data.lead_time);
                            $('#edit_lead_time_max').val(data.lead_time_max);

                            if (data.foto) {
                                $('#edit_foto_preview').html(`
                                    <img src="{{ asset('storage') }}/${data.foto}" class="img-thumbnail" style="max-width: 200px;">
                                    <div class="mt-1">
                                        <small class="text-muted">Foto saat ini</small>
                                    </div>
                                `);
                            } else {
                                $('#edit_foto_preview').html(`
                                    <div class="text-center">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                        <div class="mt-1">
                                            <small class="text-muted">Tidak ada foto</small>
                                        </div>
                                    </div>
                                `);
                            }

                            $('#editForm').attr('action', '{{ url('owner/bahan-baku') }}/' +
                                id);

                            $('#editBahanBakuModal').modal('show');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat data untuk edit'
                        });
                    }
                });
            });

            $('#createForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = new FormData(this);

                Swal.fire({
                    title: 'Menyimpan...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message ||
                                    'Bahan baku berhasil ditambahkan dengan stok awal 0!',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                $('#createBahanBakuModal').modal('hide');
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorMessage = '';
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Error',
                                text: errorMessage
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ||
                                    'Gagal menambahkan data'
                            });
                        }
                    }
                });
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = new FormData(this);

                Swal.fire({
                    title: 'Mengupdate...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                $('#editBahanBakuModal').modal('hide');
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorMessage = '';
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Error',
                                text: errorMessage
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ||
                                    'Gagal mengupdate data'
                            });
                        }
                    }
                });
            });

            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var nama = $(this).data('nama');

                $('#deleteNama').text(nama);
                $('#deleteForm').attr('action', '{{ url('owner/bahan-baku') }}/' + id);
                $('#deleteModal').modal('show');
            });

            $('#deleteForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = form.serialize();

                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                $('#deleteModal').modal('hide');
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Gagal menghapus data'
                        });
                    }
                });
            });

            // Reset form ketika modal ditutup
            $('#createBahanBakuModal').on('hidden.bs.modal', function() {
                $('#createForm')[0].reset();
                $('#createForm').find('.is-invalid').removeClass('is-invalid');
                $('#createForm').find('.invalid-feedback').text('');
                $('#createForm select').val('').trigger('change');
            });

            $('#editBahanBakuModal').on('hidden.bs.modal', function() {
                $('#editForm')[0].reset();
                $('#editForm').find('.is-invalid').removeClass('is-invalid');
                $('#editForm').find('.invalid-feedback').text('');
                $('#edit_foto_preview').empty();
            });
        });

        function printLaporan() {
            var printContents = `
                <html>
                <head>
                    <title>Laporan Bahan Baku</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .header { text-align: center; margin-bottom: 20px; }
                        .header h2 { margin: 0; }
                        .header p { margin: 5px 0; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
                        th { background-color: #f2f2f2; font-weight: bold; }
                        .footer { margin-top: 30px; text-align: right; }
                        @media print {
                            @page { size: landscape; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h2>Laporan Bahan Baku</h2>
                        <p>Permata Biru Onix</p>
                        <p>Tanggal: ${new Date().toLocaleDateString('id-ID')}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Bahan Baku</th>
                                <th>Satuan</th>
                                <th>Harga Beli</th>
                                <th>Harga Jual</th>
                                <th>Lead Time Rata-rata</th>
                                <th>Lead Time Maksimal</th>
                                <th>Stok</th>
                                <th>Safety Stock</th>
                                <th>ROP</th>
                                <th>Min</th>
                                <th>Max</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>`;

            $('#bahanBakuTable tbody tr').each(function(index) {
                var row = $(this);
                var status = row.find('td:eq(13)').text().trim();

                printContents += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${row.find('td:eq(2)').text().trim()}</td>
                        <td>${row.find('td:eq(3)').text().trim()}</td>
                        <td>${row.find('td:eq(4)').text().trim()}</td>
                        <td>${row.find('td:eq(5)').text().trim()}</td>
                        <td>${row.find('td:eq(6)').text().trim()}</td>
                        <td>${row.find('td:eq(7)').text().trim()}</td>
                        <td>${row.find('td:eq(8)').text().trim()}</td>
                        <td>${row.find('td:eq(9)').text().trim()}</td>
                        <td>${row.find('td:eq(10)').text().trim()}</td>
                        <td>${row.find('td:eq(11)').text().trim()}</td>
                        <td>${row.find('td:eq(12)').text().trim()}</td>
                        <td>${status}</td>
                    </tr>`;
            });

            printContents += `
                        </tbody>
                    </table>
                    <div class="footer">
                        <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                    </div>
                </body>
                </html>`;

            var printWindow = window.open('', '_blank');
            printWindow.document.open();
            printWindow.document.write(printContents);
            printWindow.document.close();

            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        }
    </script>
@endpush
