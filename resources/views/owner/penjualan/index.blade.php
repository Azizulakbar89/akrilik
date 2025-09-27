@extends('layoutsAPP.deskapp')

@section('title', 'Penjualan')

@section('content')
    <div>
        <div class="pd-ltr-20">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            <h4>Penjualan</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a
                                        href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('owner.dashboard') }}">Home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Penjualan</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 col-sm-12 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#pesanPenjualanModal">
                            <i class="icon-copy dw dw-money"></i> Pesan Penjualan
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-box mb-30">
                <div class="pd-20">
                    <h4 class="text-blue h4">Data Penjualan</h4>
                </div>
                <div class="pb-20">
                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th class="table-plus">No</th>
                                <th>Kode Penjualan</th>
                                <th>Customer</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="datatable-nosort">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="table-plus">1</td>
                                <td>PJL-001</td>
                                <td>Customer A</td>
                                <td>2024-01-15</td>
                                <td>Rp 750.000</td>
                                <td><span class="badge badge-success">Selesai</span></td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                            href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                data-target="#detailPenjualanModal"><i class="dw dw-eye"></i> Detail</a>
                                            <a class="dropdown-item" href="#" onclick="confirmDelete()"><i
                                                    class="dw dw-delete-3"></i> Hapus</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="table-plus">2</td>
                                <td>PJL-002</td>
                                <td>Customer B</td>
                                <td>2024-01-16</td>
                                <td>Rp 1.200.000</td>
                                <td><span class="badge badge-warning">Proses</span></td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                            href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                data-target="#detailPenjualanModal"><i class="dw dw-eye"></i> Detail</a>
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

    <div class="modal fade" id="pesanPenjualanModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Pesan Penjualan</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form id="formPenjualan">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Customer</label>
                                    <input type="text" class="form-control" placeholder="Nama Customer">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Penjualan</label>
                                    <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Produk</label>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <select class="form-control" id="produkSelect">
                                        <option value="">Pilih Produk</option>
                                        <option value="1">Piala - Rp 15.000</option>
                                        <option value="2">Aksessoris - Rp 25.000</option>
                                        <option value="3">Piala Mini - Rp 5.000</option>
                                        <option value="4">Piala Sedang - Rp 12.000</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" id="jumlahProduk" placeholder="Jumlah">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-success btn-block" onclick="tambahProduk()">
                                        <i class="dw dw-add"></i> Tambah
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Jumlah</th>
                                        <th>Harga Satuan</th>
                                        <th>Subtotal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tableProduk">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total:</th>
                                        <th id="totalPenjualan">Rp 0</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="simpanPenjualan()">Simpan Penjualan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailPenjualanModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Detail Penjualan - PJL-001</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Kode Penjualan:</strong> PJL-001</p>
                            <p><strong>Customer:</strong> Customer A</p>
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
                                    <th>Produk</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Piala</td>
                                    <td>30</td>
                                    <td>Rp 15.000</td>
                                    <td>Rp 450.000</td>
                                </tr>
                                <tr>
                                    <td>Aksessoris</td>
                                    <td>12</td>
                                    <td>Rp 25.000</td>
                                    <td>Rp 300.000</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th>Rp 750.000</th>
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
        let produkList = [];
        let totalPenjualan = 0;

        function tambahProduk() {
            const produkSelect = document.getElementById('produkSelect');
            const jumlahInput = document.getElementById('jumlahProduk');

            if (produkSelect.value === '' || jumlahInput.value === '') {
                alert('Pilih produk dan masukkan jumlah!');
                return;
            }

            const produkText = produkSelect.options[produkSelect.selectedIndex].text;
            const produkId = produkSelect.value;
            const jumlah = parseInt(jumlahInput.value);

            const hargaMatch = produkText.match(/Rp (\d+(\.\d+)?)/);
            const harga = hargaMatch ? parseInt(hargaMatch[1].replace('.', '')) : 0;
            const subtotal = jumlah * harga;

            const produkNama = produkText.split(' - ')[0];

            produkList.push({
                id: produkId,
                nama: produkNama,
                jumlah: jumlah,
                harga: harga,
                subtotal: subtotal
            });

            updateTableProduk();
            produkSelect.value = '';
            jumlahInput.value = '';
        }

        function updateTableProduk() {
            const tableBody = document.getElementById('tableProduk');
            totalPenjualan = 0;

            tableBody.innerHTML = '';
            produkList.forEach((item, index) => {
                totalPenjualan += item.subtotal;
                tableBody.innerHTML += `
                <tr>
                    <td>${item.nama}</td>
                    <td>${item.jumlah}</td>
                    <td>Rp ${item.harga.toLocaleString('id-ID')}</td>
                    <td>Rp ${item.subtotal.toLocaleString('id-ID')}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="hapusProduk(${index})">
                            <i class="dw dw-delete-3"></i>
                        </button>
                    </td>
                </tr>
            `;
            });

            document.getElementById('totalPenjualan').textContent = `Rp ${totalPenjualan.toLocaleString('id-ID')}`;
        }

        function hapusProduk(index) {
            produkList.splice(index, 1);
            updateTableProduk();
        }

        function simpanPenjualan() {
            if (produkList.length === 0) {
                alert('Tambahkan minimal satu produk!');
                return;
            }

            alert('Penjualan berhasil disimpan!');
            $('#pesanPenjualanModal').modal('hide');
            produkList = [];
            updateTableProduk();
        }

        function confirmDelete() {
            if (confirm('Apakah Anda yakin ingin menghapus data penjualan ini?')) {
                alert('Data penjualan berhasil dihapus!');
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
