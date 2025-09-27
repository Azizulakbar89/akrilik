@extends('layoutsAPP.deskapp')

@section('title', 'Komposisi Produk')

@section('content')
    <div>
        <div class="pd-ltr-20">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            <h4>Komposisi Produk</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a
                                        href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('owner.dashboard') }}">Home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Komposisi Produk</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 col-sm-12 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#tambahKomposisiModal">
                            <i class="icon-copy dw dw-add"></i> Tambah Komposisi
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-box mb-30">
                <div class="pd-20">
                    <h4 class="text-blue h4">Data Komposisi Produk</h4>
                </div>
                <div class="pb-20">
                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th class="table-plus">No</th>
                                <th>Nama Produk</th>
                                <th>Bahan Baku</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th class="datatable-nosort">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="table-plus">1</td>
                                <td>Piala</td>
                                <td>Akrilik</td>
                                <td>500</td>
                                <td>Cm</td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                            href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                data-target="#editKomposisiModal"><i class="dw dw-edit2"></i> Edit</a>
                                            <a class="dropdown-item" href="#" onclick="confirmDelete()"><i
                                                    class="dw dw-delete-3"></i> Hapus</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="table-plus">2</td>
                                <td>Akesesoris</td>
                                <td>Akesesoris</td>
                                <td>250</td>
                                <td>Pcs</td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                            href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                data-target="#editKomposisiModal"><i class="dw dw-edit2"></i> Edit</a>
                                            <a class="dropdown-item" href="#" onclick="confirmDelete()"><i
                                                    class="dw dw-delete-3"></i> Hapus</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tambahKomposisiModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Komposisi Produk</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label>Produk</label>
                            <select class="form-control">
                                <option value="">Pilih Produk</option>
                                <option value="1">Piala</option>
                                <option value="2">Akesesoris</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Bahan Baku</label>
                            <select class="form-control">
                                <option value="">Pilih Bahan Baku</option>
                                <option value="1">Akrilik</option>
                                <option value="2">Akesesoris</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jumlah</label>
                            <input type="number" class="form-control" placeholder="Masukkan jumlah">
                        </div>
                        <div class="form-group">
                            <label>Satuan</label>
                            <input type="text" class="form-control" placeholder="Masukkan satuan">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editKomposisiModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Komposisi Produk</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label>Produk</label>
                            <select class="form-control">
                                <option value="1" selected>Piala</option>
                                <option value="2">Akesesoris</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Bahan Baku</label>
                            <select class="form-control">
                                <option value="1" selected>Akrilik</option>
                                <option value="2">Akesesoris</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jumlah</label>
                            <input type="number" class="form-control" value="500">
                        </div>
                        <div class="form-group">
                            <label>Satuan</label>
                            <input type="text" class="form-control" value="gram">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary">Update</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function confirmDelete() {
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                alert('Data berhasil dihapus!');
            }
        }

        $('.data-table').DataTable({
            scrollCollapse: true,
            autoWidth: false,
            responsive: true,
            columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            "language": {
                "info": "_START_-_END_ dari _TOTAL_ data",
                "search": "Cari:",
                "paginate": {
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                },
            }
        });
    </script>
@endsection
