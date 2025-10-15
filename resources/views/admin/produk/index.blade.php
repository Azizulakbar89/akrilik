@extends('layoutsAPP.deskapp')

@section('title', 'Produk')

@section('content')
    <div class="page-header">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title">
                    <h4>Produk</h4>
                </div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a
                                href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('owner.dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Produk</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 col-sm-12 text-right">
                @if (Auth::user()->role === 'admin')
                    <button class="btn btn-primary" data-toggle="modal" data-target="#tambahProdukModal">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="card-box mb-30">
        <div class="pd-20">
            <h4 class="text-blue h4">Daftar Produk</h4>
        </div>
        <div class="pb-20">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="data-table table stripe hover nowrap" style="width: 100%; min-width: 1000px;">
                    <thead>
                        <tr>
                            <th class="table-plus datatable-nosort">No</th>
                            <th>Foto</th>
                            <th>Nama Produk</th>
                            <th>Satuan</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Komposisi Bahan Baku</th>
                            <th class="datatable-nosort">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($produk as $index => $p)
                            <tr>
                                <td class="table-plus">{{ $index + 1 }}</td>
                                <td>
                                    @if ($p->foto)
                                        <img src="{{ asset('storage/' . $p->foto) }}" alt="{{ $p->nama }}"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    @else
                                        <div
                                            style="width: 50px; height: 50px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                            <i class="fas fa-image" style="color: #6c757d;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $p->nama }}</td>
                                <td>{{ $p->satuan }}</td>
                                <td>Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                                <td>{{ $p->stok }}</td>
                                <td>
                                    @if ($p->komposisi->count() > 0)
                                        <button class="btn btn-sm btn-outline-info show-komposisi-btn"
                                            data-id="{{ $p->id }}" data-nama="{{ $p->nama }}">
                                            <i class="fas fa-list"></i> Lihat ({{ $p->komposisi->count() }})
                                        </button>
                                    @else
                                        <span class="badge badge-warning">Belum ada komposisi</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                            href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item show-btn" href="#" data-id="{{ $p->id }}">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                            @if (Auth::user()->role === 'admin')
                                                <a class="dropdown-item edit-btn" href="#"
                                                    data-id="{{ $p->id }}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a class="dropdown-item delete-btn" href="#"
                                                    data-id="{{ $p->id }}">
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
    @if (Auth::user()->role === 'admin')
        <div class="modal fade" id="tambahProdukModal" tabindex="-1" role="dialog"
            aria-labelledby="tambahProdukModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="tambahProdukModalLabel">Tambah Produk</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="tambahProdukForm" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Produk</label>
                                        <input type="text" class="form-control" name="nama" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Satuan</label>
                                        <select class="form-control select2" name="satuan" required>
                                            <option value="">Pilih Satuan</option>
                                            <option value="pcs">pcs</option>
                                            <option value="unit">unit</option>
                                            <option value="set">set</option>
                                            <option value="paket">paket</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Harga</label>
                                        <input type="number" step="0.01" class="form-control" name="harga" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Stok</label>
                                        <input type="number" class="form-control" name="stok" value="0"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Foto Produk</label>
                                <input type="file" class="form-control" name="foto" accept="image/*">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label>Komposisi Bahan Baku</label>
                                <div id="komposisi-container">
                                    <div class="komposisi-item row mb-2">
                                        <div class="col-md-6">
                                            <select class="form-control select2-bahan-baku"
                                                name="komposisi[0][bahan_baku_id]" required>
                                                <option value="">Pilih Bahan Baku</option>
                                                @foreach ($bahanBaku as $bb)
                                                    <option value="{{ $bb->id }}" data-stok="{{ $bb->stok }}"
                                                        data-satuan="{{ $bb->satuan }}">
                                                        {{ $bb->nama }} (Stok: {{ $bb->stok }}
                                                        {{ $bb->satuan }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" class="form-control" name="komposisi[0][jumlah]"
                                                placeholder="Jumlah" required min="1">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger btn-sm remove-komposisi"
                                                disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="tambah-komposisi">
                                    <i class="fas fa-plus"></i> Tambah Bahan Baku
                                </button>
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

    @if (Auth::user()->role === 'admin')
        <div class="modal fade" id="editProdukModal" tabindex="-1" role="dialog"
            aria-labelledby="editProdukModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="editProdukModalLabel">Edit Produk</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="editProdukForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="edit_id">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Produk</label>
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
                                            <option value="pcs">pcs</option>
                                            <option value="unit">unit</option>
                                            <option value="set">set</option>
                                            <option value="paket">paket</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Harga</label>
                                        <input type="number" step="0.01" class="form-control" name="harga"
                                            id="edit_harga" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Stok</label>
                                        <input type="number" class="form-control" name="stok" id="edit_stok"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Foto Produk</label>
                                <input type="file" class="form-control" name="foto" accept="image/*">
                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah foto</small>
                                <div class="invalid-feedback"></div>
                                <div id="current_foto" class="mt-2"></div>
                            </div>

                            <div class="form-group">
                                <label>Komposisi Bahan Baku</label>
                                <div id="edit-komposisi-container">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2"
                                    id="edit-tambah-komposisi">
                                    <i class="fas fa-plus"></i> Tambah Bahan Baku
                                </button>
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

    <div class="modal fade" id="showProdukModal" tabindex="-1" role="dialog" aria-labelledby="showProdukModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="showProdukModalLabel">Detail Produk</h4>
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
                                <label>Nama Produk</label>
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
                            <div class="form-group">
                                <label>Harga</label>
                                <p id="show_harga" class="form-control-plaintext font-weight-bold text-success"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Komposisi Bahan Baku</h5>
                        <div id="show_komposisi" class="table-responsive">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="komposisiModal" tabindex="-1" role="dialog" aria-labelledby="komposisiModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="komposisiModalLabel">Komposisi Bahan Baku - <span
                            id="komposisi_nama_produk"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Bahan Baku</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Stok Tersedia</th>
                                </tr>
                            </thead>
                            <tbody id="komposisi_list">
                            </tbody>
                        </table>
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

        .komposisi-item {
            border: 1px solid #e9ecef;
            padding: 10px;
            border-radius: 5px;
            background-color: #f8f9fa;
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

            $('.select2-bahan-baku').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Pilih Bahan Baku',
                allowClear: true
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let komposisiCounter = 1;
            let editKomposisiCounter = 0;

            $('#tambah-komposisi').click(function() {
                const newItem = `
                <div class="komposisi-item row mb-2">
                    <div class="col-md-6">
                        <select class="form-control select2-bahan-baku" name="komposisi[${komposisiCounter}][bahan_baku_id]" required>
                            <option value="">Pilih Bahan Baku</option>
                            @foreach ($bahanBaku as $bb)
                                <option value="{{ $bb->id }}" data-stok="{{ $bb->stok }}" data-satuan="{{ $bb->satuan }}">
                                    {{ $bb->nama }} (Stok: {{ $bb->stok }} {{ $bb->satuan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" name="komposisi[${komposisiCounter}][jumlah]" placeholder="Jumlah" required min="1">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-komposisi">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
                $('#komposisi-container').append(newItem);

                $('#komposisi-container .select2-bahan-baku').last().select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: 'Pilih Bahan Baku',
                    allowClear: true
                });

                $('.remove-komposisi').prop('disabled', false);

                komposisiCounter++;
            });

            $(document).on('click', '.remove-komposisi', function() {
                if ($('.komposisi-item').length > 1) {
                    $(this).closest('.komposisi-item').remove();
                }

                if ($('.komposisi-item').length === 1) {
                    $('.remove-komposisi').prop('disabled', true);
                }
            });

            $('#edit-tambah-komposisi').click(function() {
                const newItem = `
                <div class="komposisi-item row mb-2">
                    <div class="col-md-6">
                        <select class="form-control select2-bahan-baku" name="komposisi[${editKomposisiCounter}][bahan_baku_id]" required>
                            <option value="">Pilih Bahan Baku</option>
                            @foreach ($bahanBaku as $bb)
                                <option value="{{ $bb->id }}" data-stok="{{ $bb->stok }}" data-satuan="{{ $bb->satuan }}">
                                    {{ $bb->nama }} (Stok: {{ $bb->stok }} {{ $bb->satuan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" name="komposisi[${editKomposisiCounter}][jumlah]" placeholder="Jumlah" required min="1">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-edit-komposisi">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
                $('#edit-komposisi-container').append(newItem);

                $('#edit-komposisi-container .select2-bahan-baku').last().select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: 'Pilih Bahan Baku',
                    allowClear: true
                });

                editKomposisiCounter++;
            });

            $(document).on('click', '.remove-edit-komposisi', function() {
                $(this).closest('.komposisi-item').remove();
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
                    url: '{{ url('admin/produk') }}/' + id,
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        console.log('Response received:', response);

                        if (response.status === 'success') {
                            var data = response.data;

                            $('#show_nama').text(data.nama || '-');
                            $('#show_satuan').text(data.satuan || '-');
                            $('#show_harga').text(data.harga ? 'Rp ' + parseFloat(data.harga)
                                .toLocaleString('id-ID') : '-');
                            $('#show_stok').text(data.stok || '0');

                            $('#show_foto').html(data.foto ?
                                `<img src="{{ asset('storage') }}/${data.foto}" alt="Foto Produk" style="max-width: 200px; height: auto; border-radius: 5px;">` :
                                '<div style="width: 200px; height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 5px;"><i class="fas fa-image" style="font-size: 48px; color: #6c757d;"></i></div>'
                            );

                            let komposisiHtml = '';
                            if (data.komposisi && data.komposisi.length > 0) {
                                komposisiHtml = `
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Bahan Baku</th>
                                            <th>Jumlah</th>
                                            <th>Satuan</th>
                                            <th>Stok Tersedia</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;
                                data.komposisi.forEach((komp, index) => {
                                    komposisiHtml += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${komp.bahan_baku.nama}</td>
                                        <td>${komp.jumlah}</td>
                                        <td>${komp.bahan_baku.satuan}</td>
                                        <td>${komp.bahan_baku.stok}</td>
                                    </tr>
                                `;
                                });
                                komposisiHtml += '</tbody></table>';
                            } else {
                                komposisiHtml =
                                    '<p class="text-muted">Belum ada komposisi bahan baku</p>';
                            }
                            $('#show_komposisi').html(komposisiHtml);

                            $('#showProdukModal').modal('show');
                        } else {
                            console.error('Response status not success:', response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memuat data produk'
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
                                'Gagal memuat data produk'
                        });
                    }
                });
            });

            $(document).on('click', '.show-komposisi-btn', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var nama = $(this).data('nama');

                $('#komposisi_nama_produk').text(nama);

                Swal.fire({
                    title: 'Memuat...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ url('admin/produk') }}/' + id,
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            var data = response.data;
                            let komposisiHtml = '';

                            if (data.komposisi && data.komposisi.length > 0) {
                                data.komposisi.forEach((komp, index) => {
                                    komposisiHtml += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${komp.bahan_baku.nama}</td>
                                        <td>${komp.jumlah}</td>
                                        <td>${komp.bahan_baku.satuan}</td>
                                        <td>${komp.bahan_baku.stok}</td>
                                    </tr>
                                `;
                                });
                            } else {
                                komposisiHtml =
                                    '<tr><td colspan="5" class="text-center">Belum ada komposisi bahan baku</td></tr>';
                            }

                            $('#komposisi_list').html(komposisiHtml);
                            $('#komposisiModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat komposisi bahan baku'
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
                    url: '{{ url('admin/produk') }}/' + id,
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        console.log('Response received:', response);

                        if (response.status === 'success') {
                            var data = response.data;

                            $('#edit_id').val(data.id);
                            $('#edit_nama').val(data.nama);
                            $('#edit_satuan').val(data.satuan).trigger('change');
                            $('#edit_harga').val(data.harga);
                            $('#edit_stok').val(data.stok);

                            $('#current_foto').html(data.foto ?
                                `<p>Foto saat ini:</p><img src="{{ asset('storage') }}/${data.foto}" alt="Foto Produk" style="max-width: 100px; height: auto; border-radius: 5px; margin-top: 5px;">` :
                                '<p class="text-muted">Tidak ada foto</p>'
                            );

                            $('#edit-komposisi-container').empty();
                            editKomposisiCounter = 0;

                            if (data.komposisi && data.komposisi.length > 0) {
                                data.komposisi.forEach((komp, index) => {
                                    const komposisiItem = `
                                    <div class="komposisi-item row mb-2">
                                        <div class="col-md-6">
                                            <select class="form-control select2-bahan-baku" name="komposisi[${editKomposisiCounter}][bahan_baku_id]" required>
                                                <option value="">Pilih Bahan Baku</option>
                                                @foreach ($bahanBaku as $bb)
                                                    <option value="{{ $bb->id }}" data-stok="{{ $bb->stok }}" data-satuan="{{ $bb->satuan }}" ${komp.bahan_baku_id == {{ $bb->id }} ? 'selected' : ''}>
                                                        {{ $bb->nama }} (Stok: {{ $bb->stok }} {{ $bb->satuan }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" class="form-control" name="komposisi[${editKomposisiCounter}][jumlah]" value="${komp.jumlah}" placeholder="Jumlah" required min="1">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger btn-sm remove-edit-komposisi">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                `;
                                    $('#edit-komposisi-container').append(
                                        komposisiItem);

                                    $('#edit-komposisi-container .select2-bahan-baku')
                                        .last().select2({
                                            theme: 'bootstrap4',
                                            width: '100%',
                                            placeholder: 'Pilih Bahan Baku',
                                            allowClear: true
                                        }).trigger('change');

                                    editKomposisiCounter++;
                                });
                            } else {
                                $('#edit-tambah-komposisi').click();
                            }

                            $('#editProdukModal').modal('show');
                        } else {
                            console.error('Response status not success:', response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memuat data produk'
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
                                'Gagal memuat data produk'
                        });
                    }
                });
            });

            $('#tambahProdukForm').on('submit', function(e) {
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
                    url: '{{ route('admin.produk.store') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            $('#tambahProdukModal').modal('hide');
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
                            $('#tambahProdukForm').find('.invalid-feedback').text('');
                            $('#tambahProdukForm').find('.is-invalid').removeClass(
                                'is-invalid');

                            $.each(errors, function(key, value) {
                                var input = $('#tambahProdukForm [name="' + key + '"]');
                                if (input.length === 0) {
                                    if (key.startsWith('komposisi.')) {
                                        const parts = key.split('.');
                                        const index = parts[1];
                                        const field = parts[2];
                                        input = $(
                                            '#tambahProdukForm [name="komposisi[' +
                                            index + '][' + field + ']"]');
                                    }
                                }
                                if (input.length > 0) {
                                    input.addClass('is-invalid');
                                    input.siblings('.invalid-feedback').text(value[0]);
                                }
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

            $('#editProdukForm').on('submit', function(e) {
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
                    url: '{{ url('admin/produk') }}/' + id,
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
                            $('#editProdukModal').modal('hide');
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
                            $('#editProdukForm').find('.invalid-feedback').text('');
                            $('#editProdukForm').find('.is-invalid').removeClass('is-invalid');

                            $.each(errors, function(key, value) {
                                var input = $('#editProdukForm [name="' + key + '"]');
                                if (input.length === 0) {
                                    if (key.startsWith('komposisi.')) {
                                        const parts = key.split('.');
                                        const index = parts[1];
                                        const field = parts[2];
                                        input = $('#editProdukForm [name="komposisi[' +
                                            index + '][' + field + ']"]');
                                    }
                                }
                                if (input.length > 0) {
                                    input.addClass('is-invalid');
                                    input.siblings('.invalid-feedback').text(value[0]);
                                }
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
                    text: 'Data yang dihapus tidak dapat dikembalikan! Stok bahan baku akan dikembalikan.',
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
                            url: '{{ url('admin/produk') }}/' + id,
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

            $('#tambahProdukModal').on('hidden.bs.modal', function() {
                $('#tambahProdukForm')[0].reset();
                $('#tambahProdukForm').find('.invalid-feedback').text('');
                $('#tambahProdukForm').find('.is-invalid').removeClass('is-invalid');
                $('#tambahProdukForm select').val('').trigger('change');
                $('#komposisi-container').html(`
                <div class="komposisi-item row mb-2">
                    <div class="col-md-6">
                        <select class="form-control select2-bahan-baku" name="komposisi[0][bahan_baku_id]" required>
                            <option value="">Pilih Bahan Baku</option>
                            @foreach ($bahanBaku as $bb)
                                <option value="{{ $bb->id }}" data-stok="{{ $bb->stok }}" data-satuan="{{ $bb->satuan }}">
                                    {{ $bb->nama }} (Stok: {{ $bb->stok }} {{ $bb->satuan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" name="komposisi[0][jumlah]" placeholder="Jumlah" required min="1">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-komposisi" disabled>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `);
                komposisiCounter = 1;

                $('#komposisi-container .select2-bahan-baku').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: 'Pilih Bahan Baku',
                    allowClear: true
                });
            });

            $('#editProdukModal').on('hidden.bs.modal', function() {
                $('#editProdukForm').find('.invalid-feedback').text('');
                $('#editProdukForm').find('.is-invalid').removeClass('is-invalid');
                $('#current_foto').empty();
                $('#edit-komposisi-container').empty();
                editKomposisiCounter = 0;
            });

            console.log('Modals initialized:', {
                showModal: $('#showProdukModal').length,
                editModal: $('#editProdukModal').length,
                addModal: $('#tambahProdukModal').length
            });
        });
    </script>
@endpush
