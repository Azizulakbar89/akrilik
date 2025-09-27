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
                    <div class="table-responsive">
                        <table class="data-table table stripe hover nowrap" style="width:100%">
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
                                    <td><span class="badge badge-success">Disetujui</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                href="#" role="button" data-toggle="dropdown">
                                                <i class="dw dw-more"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#detailPembelianModal"><i class="dw dw-eye"></i> Lihat
                                                    Detail</a>
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#editPembelianModal"><i class="dw dw-edit2"></i> Ubah
                                                    Detail</a>
                                                <a class="dropdown-item" href="#" onclick="setujuiPembelian(1)"><i
                                                        class="dw dw-check"></i> Setujui</a>
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
                                    <td><span class="badge badge-warning">Menunggu</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                href="#" role="button" data-toggle="dropdown">
                                                <i class="dw dw-more"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#detailPembelianModal"><i class="dw dw-eye"></i> Lihat
                                                    Detail</a>
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#editPembelianModal"><i class="dw dw-edit2"></i> Ubah
                                                    Detail</a>
                                                <a class="dropdown-item" href="#" onclick="setujuiPembelian(2)"><i
                                                        class="dw dw-check"></i> Setujui</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-plus">3</td>
                                    <td>PBL-003</td>
                                    <td>PT Bahan Prima</td>
                                    <td>2024-01-17</td>
                                    <td>Rp 3.750.000</td>
                                    <td><span class="badge badge-danger">Ditolak</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                href="#" role="button" data-toggle="dropdown">
                                                <i class="dw dw-more"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#detailPembelianModal"><i class="dw dw-eye"></i> Lihat
                                                    Detail</a>
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#editPembelianModal"><i class="dw dw-edit2"></i> Ubah
                                                    Detail</a>
                                                <a class="dropdown-item" href="#" onclick="setujuiPembelian(3)"><i
                                                        class="dw dw-check"></i> Setujui</a>
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
    </div>

    <!-- Modal Pesan Pembelian -->
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
                                        <option value="3">PT Bahan Prima</option>
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
                                <div class="col-md-5">
                                    <select class="form-control" id="bahanBakuSelect">
                                        <option value="">Pilih Bahan Baku</option>
                                        <option value="1">Akrilik</option>
                                        <option value="2">Aksesoris</option>
                                        <option value="3">Kain</option>
                                        <option value="4">Benang</option>
                                        <option value="5">Paku</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" id="jumlahBahan" placeholder="Jumlah"
                                        min="1">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" id="satuanBahan">
                                        <option value="cm">Cm</option>
                                        <option value="pcs">Pcs</option>
                                        <option value="meter">Meter</option>
                                        <option value="kg">Kg</option>
                                        <option value="liter">Liter</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
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
                                        <th>Satuan</th>
                                        <th>Harga Satuan</th>
                                        <th>Subtotal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBahanBaku">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">Total:</th>
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

    <!-- Modal Detail Pembelian -->
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
                            <p><strong>Alamat Supplier:</strong> Jl. Merdeka No. 123, Jakarta</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tanggal:</strong> 15 Januari 2024</p>
                            <p><strong>Status:</strong> <span class="badge badge-success">Disetujui</span></p>
                            <p><strong>Disetujui Oleh:</strong> Admin</p>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Bahan Baku</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Akrilik</td>
                                    <td>100</td>
                                    <td>Cm</td>
                                    <td>Rp 12.000</td>
                                    <td>Rp 1.200.000</td>
                                </tr>
                                <tr>
                                    <td>Aksesoris</td>
                                    <td>20</td>
                                    <td>Pcs</td>
                                    <td>Rp 15.000</td>
                                    <td>Rp 300.000</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total Pembelian:</th>
                                    <th>Rp 1.500.000</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-3">
                        <h6>Catatan:</h6>
                        <p class="text-muted">Pembelian bahan baku untuk produksi bulan Januari 2024. Bahan dalam kondisi
                            baik dan sesuai spesifikasi.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-info" onclick="cetakDetail()"><i class="dw dw-print"></i>
                        Cetak</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Pembelian -->
    <div class="modal fade" id="editPembelianModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Pembelian - PBL-001</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form id="formEditPembelian">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Supplier</label>
                                    <select class="form-control">
                                        <option value="1" selected>PT Sumber Jaya</option>
                                        <option value="2">CV Maju Terus</option>
                                        <option value="3">PT Bahan Prima</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Pembelian</label>
                                    <input type="date" class="form-control" value="2024-01-15">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Status Pembelian</label>
                            <select class="form-control">
                                <option value="menunggu">Menunggu</option>
                                <option value="disetujui" selected>Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Bahan Baku</label>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Bahan Baku</th>
                                            <th>Jumlah</th>
                                            <th>Satuan</th>
                                            <th>Harga Satuan</th>
                                            <th>Subtotal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select class="form-control">
                                                    <option value="1" selected>Akrilik</option>
                                                    <option value="2">Aksesoris</option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control" value="100"
                                                    min="1"></td>
                                            <td>
                                                <select class="form-control">
                                                    <option value="cm" selected>Cm</option>
                                                    <option value="pcs">Pcs</option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control" value="12000"
                                                    min="0"></td>
                                            <td>Rp 1.200.000</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger">
                                                    <i class="dw dw-delete-3"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <select class="form-control">
                                                    <option value="1">Akrilik</option>
                                                    <option value="2" selected>Aksesoris</option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control" value="20"
                                                    min="1"></td>
                                            <td>
                                                <select class="form-control">
                                                    <option value="cm">Cm</option>
                                                    <option value="pcs" selected>Pcs</option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control" value="15000"
                                                    min="0"></td>
                                            <td>Rp 300.000</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger">
                                                    <i class="dw dw-delete-3"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                            <td colspan="2"><strong>Rp 1.500.000</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea class="form-control" rows="3" placeholder="Masukkan catatan pembelian">Pembelian bahan baku untuk produksi bulan Januari 2024. Bahan dalam kondisi baik dan sesuai spesifikasi.</textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="updatePembelian()">Update Pembelian</button>
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
            const satuanSelect = document.getElementById('satuanBahan');

            if (bahanSelect.value === '' || jumlahInput.value === '' || jumlahInput.value <= 0) {
                alert('Pilih bahan baku dan masukkan jumlah yang valid!');
                return;
            }

            const bahanNama = bahanSelect.options[bahanSelect.selectedIndex].text;
            const bahanId = bahanSelect.value;
            const jumlah = parseInt(jumlahInput.value);
            const satuan = satuanSelect.value;
            const harga = Math.floor(Math.random() * 20000) + 5000;
            const subtotal = jumlah * harga;

            bahanBakuList.push({
                id: bahanId,
                nama: bahanNama,
                jumlah: jumlah,
                satuan: satuan,
                harga: harga,
                subtotal: subtotal
            });

            updateTableBahanBaku();
            bahanSelect.value = '';
            jumlahInput.value = '';
            satuanSelect.value = 'cm';
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
                        <td>${item.satuan}</td>
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

            const supplierSelect = document.getElementById('supplierSelect');
            if (supplierSelect.value === '') {
                alert('Pilih supplier terlebih dahulu!');
                return;
            }

            alert('Pembelian berhasil disimpan! Menunggu persetujuan.');
            $('#pesanPembelianModal').modal('hide');
            bahanBakuList = [];
            updateTableBahanBaku();
        }

        function setujuiPembelian(id) {
            if (confirm('Apakah Anda yakin ingin menyetujui pembelian ini?')) {
                alert(`Pembelian PBL-00${id} berhasil disetujui!`);
                // Di sini bisa tambahkan logika untuk update status di database
            }
        }

        function updatePembelian() {
            if (confirm('Apakah Anda yakin ingin mengupdate data pembelian ini?')) {
                alert('Data pembelian berhasil diupdate!');
                $('#editPembelianModal').modal('hide');
            }
        }

        function cetakDetail() {
            alert('Fitur cetak detail pembelian akan dibuka di jendela baru.');
            // window.open('url-cetak-pembelian', '_blank');
        }

        $(document).ready(function() {
            $('.data-table').DataTable({
                scrollCollapse: true,
                autoWidth: true,
                responsive: true,
                scrollX: true,
                fixedHeader: true,
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
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "paginate": {
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    },
                    "emptyTable": "Tidak ada data yang tersedia",
                    "zeroRecords": "Tidak ada data yang cocok"
                },
                "dom": 'lBfrtip',
                "buttons": [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
        });
    </script>

    <style>
        .data-table {
            width: 100% !important;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .badge {
            font-size: 12px;
            padding: 5px 10px;
        }

        .modal-lg {
            max-width: 900px;
        }
    </style>
@endsection
