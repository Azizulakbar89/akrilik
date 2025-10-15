@extends('layoutsAPP.deskapp')

@section('title', 'Bahan Baku - Akrilik')

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
                            <a
                                href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('owner.dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Bahan Baku</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 col-sm-12 text-right">
                @if (Auth::user()->role === 'admin')
                    <button class="btn btn-primary" data-toggle="modal" data-target="#tambahBahanBakuModal">
                        <i class="fas fa-plus"></i> Tambah Bahan Baku
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="card-box mb-30">
        <div class="pd-20">
            <h4 class="text-blue h4">Daftar Bahan Baku</h4>
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
                                <td>{{ $bb->stok }}</td>
                                <td>{{ $bb->safety_stock }}</td>
                                <td>{{ $bb->rop }}</td>
                                <td>{{ $bb->min }}</td>
                                <td>{{ $bb->max }}</td>
                                <td>
                                    @if ($bb->stok <= $bb->min)
                                        <span class="badge badge-warning">Perlu Pembelian</span>
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
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                            @if (Auth::user()->role === 'admin')
                                                <a class="dropdown-item edit-btn" href="#"
                                                    data-id="{{ $bb->id }}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a class="dropdown-item delete-btn" href="#"
                                                    data-id="{{ $bb->id }}">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            @endif
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
    <!-- Modal Tambah Bahan Baku -->
    @if (Auth::user()->role === 'admin')
        <div class="modal fade" id="tambahBahanBakuModal" tabindex="-1" role="dialog"
            aria-labelledby="tambahBahanBakuModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="tambahBahanBakuModalLabel">Tambah Bahan Baku</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="tambahBahanBakuForm" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Bahan Baku</label>
                                        <input type="text" class="form-control" name="nama" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Satuan</label>
                                        <select class="form-control select2" name="satuan" required>
                                            <option value="">Pilih Satuan</option>
                                            <option value="Cm">Cm</option>
                                            <option value="gram">gram</option>
                                            <option value="liter">liter</option>
                                            <option value="pcs">pcs</option>
                                            <option value="kg">kg</option>
                                            <option value="m">m</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Harga Beli</label>
                                        <input type="number" step="0.01" class="form-control" name="harga_beli"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Harga Jual</label>
                                        <input type="number" step="0.01" class="form-control" name="harga_jual"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Safety Stock</label>
                                        <input type="number" class="form-control" name="safety_stock" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>ROP</label>
                                        <input type="number" class="form-control" name="rop" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Stok Awal</label>
                                        <input type="number" class="form-control" name="stok" value="0"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Min (Reorder Point)</label>
                                        <input type="number" class="form-control" name="min" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Max (Maximum Stock)</label>
                                        <input type="number" class="form-control" name="max" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Foto Bahan Baku</label>
                                <input type="file" class="form-control" name="foto" accept="image/*">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Edit Bahan Baku -->
    @if (Auth::user()->role === 'admin')
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
                    <form id="editBahanBakuForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="edit_id">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Bahan Baku</label>
                                        <input type="text" class="form-control" name="nama" id="edit_nama"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Satuan</label>
                                        <select class="form-control select2" name="satuan" id="edit_satuan" required>
                                            <option value="">Pilih Satuan</option>
                                            <option value="Cm">Cm</option>
                                            <option value="gram">gram</option>
                                            <option value="liter">liter</option>
                                            <option value="pcs">pcs</option>
                                            <option value="kg">kg</option>
                                            <option value="m">m</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Harga Beli</label>
                                        <input type="number" step="0.01" class="form-control" name="harga_beli"
                                            id="edit_harga_beli" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Harga Jual</label>
                                        <input type="number" step="0.01" class="form-control" name="harga_jual"
                                            id="edit_harga_jual" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Safety Stock</label>
                                        <input type="number" class="form-control" name="safety_stock"
                                            id="edit_safety_stock" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>ROP</label>
                                        <input type="number" class="form-control" name="rop" id="edit_rop"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Stok</label>
                                        <input type="number" class="form-control" name="stok" id="edit_stok"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Min (Reorder Point)</label>
                                        <input type="number" class="form-control" name="min" id="edit_min"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Max (Maximum Stock)</label>
                                        <input type="number" class="form-control" name="max" id="edit_max"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Foto Bahan Baku</label>
                                <input type="file" class="form-control" name="foto" accept="image/*">
                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah foto</small>
                                <div class="invalid-feedback"></div>
                                <div id="current_foto" class="mt-2"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Show Bahan Baku -->
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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Safety Stock</label>
                                        <p id="show_safety_stock" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>ROP</label>
                                        <p id="show_rop" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status Stok</label>
                                        <p id="show_status" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Min (Reorder Point)</label>
                                        <p id="show_min" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Max (Maximum Stock)</label>
                                        <p id="show_max" class="form-control-plaintext"></p>
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
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            console.log('Document ready - initializing components');

            $('.data-table').DataTable({
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
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });

            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Pilih Satuan',
                allowClear: true
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
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
                    url: '{{ url('admin/bahan-baku') }}/' + id,
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
                            $('#show_safety_stock').text(data.safety_stock || '0');
                            $('#show_rop').text(data.rop || '0');
                            $('#show_min').text(data.min || '0');
                            $('#show_max').text(data.max || '0');

                            var statusText = data.stok <= data.min ?
                                '<span class="badge badge-warning">Perlu Pembelian</span>' :
                                '<span class="badge badge-success">Aman</span>';
                            $('#show_status').html(statusText);

                            $('#show_foto').html(data.foto ?
                                `<img src="{{ asset('storage') }}/${data.foto}" alt="Foto Bahan Baku" style="max-width: 200px; height: auto; border-radius: 5px;">` :
                                '<div style="width: 200px; height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 5px;"><i class="fas fa-image" style="font-size: 48px; color: #6c757d;"></i></div>'
                            );

                            $('#showBahanBakuModal').modal('show');
                            console.log('Show modal should be visible now');
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
                    url: '{{ url('admin/bahan-baku') }}/' + id,
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        console.log('Response received:', response);

                        if (response.status === 'success') {
                            var data = response.data;

                            $('#edit_id').val(data.id);
                            $('#edit_nama').val(data.nama);
                            $('#edit_satuan').val(data.satuan).trigger('change');
                            $('#edit_harga_beli').val(data.harga_beli);
                            $('#edit_harga_jual').val(data.harga_jual);
                            $('#edit_stok').val(data.stok);
                            $('#edit_safety_stock').val(data.safety_stock);
                            $('#edit_rop').val(data.rop);
                            $('#edit_min').val(data.min);
                            $('#edit_max').val(data.max);

                            $('#current_foto').html(data.foto ?
                                `<p>Foto saat ini:</p><img src="{{ asset('storage') }}/${data.foto}" alt="Foto Bahan Baku" style="max-width: 100px; height: auto; border-radius: 5px; margin-top: 5px;">` :
                                '<p class="text-muted">Tidak ada foto</p>'
                            );

                            $('#editBahanBakuModal').modal('show');
                            console.log('Edit modal should be visible now');
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

            $('#tambahBahanBakuForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                Swal.fire({
                    title: 'Menyimpan...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route('admin.bahan-baku.store') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            $('#tambahBahanBakuModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses',
                                text: response.message
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $('#tambahBahanBakuForm').find('.invalid-feedback').text('');
                            $('#tambahBahanBakuForm').find('.is-invalid').removeClass(
                                'is-invalid');

                            $.each(errors, function(key, value) {
                                var input = $('#tambahBahanBakuForm [name="' + key +
                                    '"]');
                                input.addClass('is-invalid');
                                input.siblings('.invalid-feedback').text(value[0]);
                            });

                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Error',
                                text: xhr.responseJSON.message ||
                                    'Terdapat kesalahan dalam pengisian form'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    }
                });
            });

            $('#editBahanBakuForm').on('submit', function(e) {
                e.preventDefault();
                var id = $('#edit_id').val();
                var formData = new FormData(this);

                Swal.fire({
                    title: 'Mengupdate...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ url('admin/bahan-baku') }}/' + id,
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
                            $('#editBahanBakuModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses',
                                text: response.message
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $('#editBahanBakuForm').find('.invalid-feedback').text('');
                            $('#editBahanBakuForm').find('.is-invalid').removeClass(
                                'is-invalid');

                            $.each(errors, function(key, value) {
                                var input = $('#editBahanBakuForm [name="' + key +
                                    '"]');
                                input.addClass('is-invalid');
                                input.siblings('.invalid-feedback').text(value[0]);
                            });

                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Error',
                                text: xhr.responseJSON.message ||
                                    'Terdapat kesalahan dalam pengisian form'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    }
                });
            });

            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var id = $(this).data('id');

                $(this).closest('.dropdown-menu').prev('.dropdown-toggle').dropdown('toggle');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: 'Data yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '{{ url('admin/bahan-baku') }}/' + id,
                            type: 'DELETE',
                            success: function(response) {
                                Swal.close();
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Sukses',
                                        text: response.message
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.close();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message ||
                                        'Terjadi kesalahan saat menghapus data'
                                });
                            }
                        });
                    }
                });
            });

            $('#tambahBahanBakuModal').on('hidden.bs.modal', function() {
                $('#tambahBahanBakuForm')[0].reset();
                $('#tambahBahanBakuForm').find('.invalid-feedback').text('');
                $('#tambahBahanBakuForm').find('.is-invalid').removeClass('is-invalid');
                $('#tambahBahanBakuForm select').val('').trigger('change');
            });

            $('#editBahanBakuModal').on('hidden.bs.modal', function() {
                $('#editBahanBakuForm').find('.invalid-feedback').text('');
                $('#editBahanBakuForm').find('.is-invalid').removeClass('is-invalid');
                $('#current_foto').empty();
                $('#editBahanBakuForm select').val('').trigger('change');
            });

            console.log('Modals initialized:', {
                showModal: $('#showBahanBakuModal').length,
                editModal: $('#editBahanBakuModal').length,
                addModal: $('#tambahBahanBakuModal').length
            });
        });
    </script>
@endpush
