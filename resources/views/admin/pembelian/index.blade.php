@extends('layoutsAPP.deskapp')

@section('title', 'Pembelian')

@section('content')
    <div>
        <div class="pd-ltr-20">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            <h4>Pembelian</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a
                                        href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('owner.dashboard') }}">Home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Pembelian</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 col-sm-12 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#pesanPembelianModal">
                            <i class="icon-copy dw dw-shopping-bag"></i> Pesan Pembelian
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-box mb-30">
                <div class="pd-20">
                    <h4 class="text-blue h4">Data Pembelian</h4>
                </div>
                <div class="pb-20">
                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th class="table-plus">No</th>
                                <th>Kode Pembelian</th>
                                <th>Supplier</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="datatable-nosort">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="table-plus">1</td>
                                <td>PBL-001</td>
                                <td>PT Sumber Jaya</td>
                                <td>2024-01-15</td>
                                <td>Rp 1.500.000</td>
                                <td><span class="badge badge-success">Selesai</span></td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                            href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                data-target="#detailPembelianModal"><i class="dw dw-eye"></i> Detail</a>
                                            <a class="dropdown-item" href="#" onclick="confirmDelete()"><i
                                                    class="dw dw-delete-3"></i> Hapus</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="table-plus">2</td>
                                <td>PBL-002</td>
                                <td>CV Maju Terus</td>
                                <td>2024-01-16</td>
                                <td>Rp 2.300.000</td>
                                <td><span class="badge badge-warning">Proses</span></td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                            href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                data-target="#detailPembelianModal"><i class="dw dw-eye"></i> Detail</a>
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

    <div class="modal fade" id="pesanPembelianModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Pesan Pembelian</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form id="formPembelian">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Supplier</label>
                                    <select class="form-control" id="supplierSelect">
                                        <option value="">Pilih Supplier</option>
                                        <option value="1">PT Sumber Jaya</option>
                                        <option value="2">CV Maju Terus</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Pembelian</label>
                                    <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Bahan Baku</label>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <select class="form-control" id="bahanBakuSelect">
                                        <option value="">Pilih Bahan Baku</option>
                                        <option value="1">Akrilik</option>
                                        <option value="2">Aksessoris</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" id="jumlahBahan" placeholder="Jumlah">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-success btn-block" onclick="tambahBahanBaku()">
                                        <i class="dw dw-add"></i> Tambah
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Bahan Baku</th>
                                        <th>Jumlah</th>
                                        <th>Harga Satuan</th>
                                        <th>Subtotal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBahanBaku">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total:</th>
                                        <th id="totalPembelian">Rp 0</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="simpanPembelian()">Simpan Pembelian</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailPembelianModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Detail Pembelian - PBL-001</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Kode Pembelian:</strong> PBL-001</p>
                            <p><strong>Supplier:</strong> PT Sumber Jaya</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tanggal:</strong> 2024-01-15</p>
                            <p><strong>Status:</strong> <span class="badge badge-success">Selesai</span></p>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Bahan Baku</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Akrilik</td>
                                    <td>100 Cm</td>
                                    <td>Rp 12.000</td>
                                    <td>Rp 1.200.000</td>
                                </tr>
                                <tr>
                                    <td>Aksessoris</td>
                                    <td>20 Pcs</td>
                                    <td>Rp 15.000</td>
                                    <td>Rp 300.000</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th>Rp 1.500.000</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let bahanBakuList = [];
        let totalPembelian = 0;

        function tambahBahanBaku() {
            const bahanSelect = document.getElementById('bahanBakuSelect');
            const jumlahInput = document.getElementById('jumlahBahan');

            if (bahanSelect.value === '' || jumlahInput.value === '') {
                alert('Pilih bahan baku dan masukkan jumlah!');
                return;
            }

            const bahanNama = bahanSelect.options[bahanSelect.selectedIndex].text;
            const bahanId = bahanSelect.value;
            const jumlah = parseInt(jumlahInput.value);
            const harga = Math.floor(Math.random() * 20000) + 5000; // Harga random untuk demo
            const subtotal = jumlah * harga;

            bahanBakuList.push({
                id: bahanId,
                nama: bahanNama,
                jumlah: jumlah,
                harga: harga,
                subtotal: subtotal
            });

            updateTableBahanBaku();
            bahanSelect.value = '';
            jumlahInput.value = '';
        }

        function updateTableBahanBaku() {
            const tableBody = document.getElementById('tableBahanBaku');
            totalPembelian = 0;

            tableBody.innerHTML = '';
            bahanBakuList.forEach((item, index) => {
                totalPembelian += item.subtotal;
                tableBody.innerHTML += `
                <tr>
                    <td>${item.nama}</td>
                    <td>${item.jumlah}</td>
                    <td>Rp ${item.harga.toLocaleString('id-ID')}</td>
                    <td>Rp ${item.subtotal.toLocaleString('id-ID')}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="hapusBahanBaku(${index})">
                            <i class="dw dw-delete-3"></i>
                        </button>
                    </td>
                </tr>
            `;
            });

            document.getElementById('totalPembelian').textContent = `Rp ${totalPembelian.toLocaleString('id-ID')}`;
        }

        function hapusBahanBaku(index) {
            bahanBakuList.splice(index, 1);
            updateTableBahanBaku();
        }

        function simpanPembelian() {
            if (bahanBakuList.length === 0) {
                alert('Tambahkan minimal satu bahan baku!');
                return;
            }

            alert('Pembelian berhasil disimpan!');
            $('#pesanPembelianModal').modal('hide');
            bahanBakuList = [];
            updateTableBahanBaku();
        }

        function confirmDelete() {
            if (confirm('Apakah Anda yakin ingin menghapus data pembelian ini?')) {
                alert('Data pembelian berhasil dihapus!');
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
