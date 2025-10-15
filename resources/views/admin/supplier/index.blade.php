@extends('layoutsAPP.deskapp')

@section('title', 'Supplier')

@section('content')
    <div class="page-header">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title">
                    <h4>Supplier</h4>
                </div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a
                                href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('owner.dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Supplier</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 col-sm-12 text-right">
                @if (Auth::user()->role === 'admin')
                    <button class="btn btn-primary" data-toggle="modal" data-target="#tambahSupplierModal">
                        <i class="fas fa-plus"></i> Tambah Supplier
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="card-box mb-30">
        <div class="pd-20">
            <h4 class="text-blue h4">Daftar Supplier</h4>
        </div>
        <div class="pb-20">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="data-table table stripe hover nowrap" style="width: 100%; min-width: 1000px;">
                    <thead>
                        <tr>
                            <th class="table-plus datatable-nosort">No</th>
                            <th>Nama Supplier</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Lead Time (hari)</th>
                            <th class="datatable-nosort">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($supplier as $index => $s)
                            <tr>
                                <td class="table-plus">{{ $index + 1 }}</td>
                                <td>{{ $s->nama }}</td>
                                <td>{{ $s->alamat }}</td>
                                <td>{{ $s->notel }}</td>
                                <td>{{ $s->lead_time }} hari</td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                            href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item show-btn" href="#" data-id="{{ $s->id }}">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                            @if (Auth::user()->role === 'admin')
                                                <a class="dropdown-item edit-btn" href="#"
                                                    data-id="{{ $s->id }}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a class="dropdown-item delete-btn" href="#"
                                                    data-id="{{ $s->id }}">
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
    <!-- Modal Tambah Supplier -->
    @if (Auth::user()->role === 'admin')
        <div class="modal fade" id="tambahSupplierModal" tabindex="-1" role="dialog"
            aria-labelledby="tambahSupplierModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="tambahSupplierModalLabel">Tambah Supplier</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="tambahSupplierForm">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Nama Supplier</label>
                                        <input type="text" class="form-control" name="nama" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Alamat</label>
                                        <textarea class="form-control" name="alamat" rows="3" required></textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Telepon</label>
                                        <input type="text" class="form-control" name="notel" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Lead Time (hari)</label>
                                        <input type="number" class="form-control" name="lead_time" min="1" required>
                                        <small class="form-text text-muted">Waktu pengiriman dalam hari</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
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

    <!-- Modal Edit Supplier -->
    @if (Auth::user()->role === 'admin')
        <div class="modal fade" id="editSupplierModal" tabindex="-1" role="dialog"
            aria-labelledby="editSupplierModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="editSupplierForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="edit_id">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Nama Supplier</label>
                                        <input type="text" class="form-control" name="nama" id="edit_nama"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Alamat</label>
                                        <textarea class="form-control" name="alamat" id="edit_alamat" rows="3" required></textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Telepon</label>
                                        <input type="text" class="form-control" name="notel" id="edit_notel"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Lead Time (hari)</label>
                                        <input type="number" class="form-control" name="lead_time" id="edit_lead_time"
                                            min="1" required>
                                        <small class="form-text text-muted">Waktu pengiriman dalam hari</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
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

    <!-- Modal Show Supplier -->
    <div class="modal fade" id="showSupplierModal" tabindex="-1" role="dialog"
        aria-labelledby="showSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="showSupplierModalLabel">Detail Supplier</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Supplier</label>
                                <p id="show_nama" class="form-control-plaintext font-weight-bold"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Alamat</label>
                                <p id="show_alamat" class="form-control-plaintext"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Telepon</label>
                                <p id="show_notel" class="form-control-plaintext"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lead Time</label>
                                <p id="show_lead_time" class="form-control-plaintext"></p>
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

        /* Memastikan tabel bisa di-scroll horizontal */
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

            // Initialize DataTable with horizontal scroll
            $('.data-table').DataTable({
                responsive: false, // Nonaktifkan responsive default
                scrollX: true, // Aktifkan scroll horizontal
                scrollCollapse: true,
                fixedColumns: {
                    left: 1, // Kolom No tetap
                    right: 1 // Kolom Aksi tetap
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

            // Get CSRF token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Show Supplier details
            $(document).on('click', '.show-btn', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Mencegah event bubbling

                var id = $(this).data('id');
                console.log('Show button clicked for ID:', id);

                // Tutup dropdown jika terbuka
                $(this).closest('.dropdown-menu').prev('.dropdown-toggle').dropdown('toggle');

                Swal.fire({
                    title: 'Memuat...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ url('admin/supplier') }}/' + id,
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        console.log('Response received:', response);

                        // Isi data ke modal
                        $('#show_nama').text(response.nama || '-');
                        $('#show_alamat').text(response.alamat || '-');
                        $('#show_notel').text(response.notel || '-');
                        $('#show_lead_time').text((response.lead_time || '0') + ' hari');

                        // Tampilkan modal
                        $('#showSupplierModal').modal('show');
                        console.log('Show modal should be visible now');
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.error('AJAX Error:', status, error, xhr.responseJSON);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Gagal memuat data supplier'
                        });
                    }
                });
            });

            // Edit Supplier - Load data
            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Mencegah event bubbling

                var id = $(this).data('id');
                console.log('Edit button clicked for ID:', id);

                // Tutup dropdown jika terbuka
                $(this).closest('.dropdown-menu').prev('.dropdown-toggle').dropdown('toggle');

                Swal.fire({
                    title: 'Memuat...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ url('admin/supplier') }}/' + id,
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        console.log('Response received:', response);

                        // Isi data ke form edit
                        $('#edit_id').val(response.id);
                        $('#edit_nama').val(response.nama);
                        $('#edit_alamat').val(response.alamat);
                        $('#edit_notel').val(response.notel);
                        $('#edit_lead_time').val(response.lead_time);

                        // Tampilkan modal
                        $('#editSupplierModal').modal('show');
                        console.log('Edit modal should be visible now');
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.error('AJAX Error:', status, error, xhr.responseJSON);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Gagal memuat data supplier'
                        });
                    }
                });
            });

            // Handle form submission for adding new Supplier
            $('#tambahSupplierForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();

                Swal.fire({
                    title: 'Menyimpan...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route('admin.supplier.store') }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            $('#tambahSupplierModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses',
                                text: response.success
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $('#tambahSupplierForm').find('.invalid-feedback').text('');
                            $('#tambahSupplierForm').find('.is-invalid').removeClass(
                                'is-invalid');

                            $.each(errors, function(key, value) {
                                var input = $('#tambahSupplierForm [name="' + key +
                                    '"]');
                                input.addClass('is-invalid');
                                input.siblings('.invalid-feedback').text(value[0]);
                            });

                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Error',
                                text: 'Terdapat kesalahan dalam pengisian form'
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

            // Edit Supplier - Update data
            $('#editSupplierForm').on('submit', function(e) {
                e.preventDefault();
                var id = $('#edit_id').val();
                var formData = $(this).serialize();

                Swal.fire({
                    title: 'Mengupdate...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ url('admin/supplier') }}/' + id,
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            $('#editSupplierModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses',
                                text: response.success
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $('#editSupplierForm').find('.invalid-feedback').text('');
                            $('#editSupplierForm').find('.is-invalid').removeClass(
                                'is-invalid');

                            $.each(errors, function(key, value) {
                                var input = $('#editSupplierForm [name="' + key + '"]');
                                input.addClass('is-invalid');
                                input.siblings('.invalid-feedback').text(value[0]);
                            });

                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Error',
                                text: 'Terdapat kesalahan dalam pengisian form'
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

            // Delete Supplier
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Mencegah event bubbling

                var id = $(this).data('id');

                // Tutup dropdown jika terbuka
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
                            url: '{{ url('admin/supplier') }}/' + id,
                            type: 'DELETE',
                            success: function(response) {
                                Swal.close();
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Sukses',
                                        text: response.success
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

            // Clear form when modal is closed
            $('#tambahSupplierModal').on('hidden.bs.modal', function() {
                $('#tambahSupplierForm')[0].reset();
                $('#tambahSupplierForm').find('.invalid-feedback').text('');
                $('#tambahSupplierForm').find('.is-invalid').removeClass('is-invalid');
            });

            $('#editSupplierModal').on('hidden.bs.modal', function() {
                $('#editSupplierForm').find('.invalid-feedback').text('');
                $('#editSupplierForm').find('.is-invalid').removeClass('is-invalid');
            });

            console.log('Modals initialized:', {
                showModal: $('#showSupplierModal').length,
                editModal: $('#editSupplierModal').length,
                addModal: $('#tambahSupplierModal').length
            });
        });
    </script>
@endpush
