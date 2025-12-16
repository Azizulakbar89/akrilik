@extends('layoutsAPP.deskapp')

@section('title', 'Penjualan')

@section('content')
    <div class="pd-ltr-20">
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Penjualan</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Penjualan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card-box mb-30">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="text-blue h4">Transaksi Penjualan</h4>
                        <button type="button" class="btn btn-primary" id="addItemBtn">
                            <i class="fa fa-plus"></i> Tambah Item
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="penjualanForm">
                            @csrf
                            <div class="form-group">
                                <label for="nama_customer">Nama Customer</label>
                                <input type="text" class="form-control" id="nama_customer" name="nama_customer" required>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th>Jenis Item</th>
                                            <th>Nama Item</th>
                                            <th>Jumlah</th>
                                            <th>Harga Satuan</th>
                                            <th>Subtotal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada item</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-right"><strong>Total</strong></td>
                                            <td colspan="2"><strong id="totalAmount">Rp 0</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right"><strong>Bayar</strong></td>
                                            <td colspan="2"><strong id="bayarAmount">Rp 0</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right"><strong>Kembalian</strong></td>
                                            <td colspan="2"><strong id="kembalianAmount">Rp 0</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="total">Total</label>
                                        <input type="text" class="form-control" id="total" readonly value="Rp 0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="bayar">Bayar</label>
                                        <input type="number" class="form-control" id="bayar" name="bayar"
                                            min="0" step="1000" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="kembalian">Kembalian</label>
                                        <input type="text" class="form-control" id="kembalian" readonly value="Rp 0">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg btn-block mt-3">
                                <i class="fa fa-save"></i> Simpan Penjualan (Kasir: {{ Auth::user()->name }})
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-box mb-30">
                    <div class="card-header">
                        <h4 class="text-blue h4">Riwayat Penjualan</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="riwayatTable">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Kasir</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($penjualan as $item)
                                        <tr>
                                            <td>{{ $item->kode_penjualan }}</td>
                                            <td>{{ $item->nama_customer }}</td>
                                            <td>{{ $item->total_formatted }}</td>
                                            <td>
                                                @if ($item->admin)
                                                    <span class="badge badge-info">{{ $item->admin->name }}</span>
                                                @else
                                                    <span class="badge badge-secondary">Unknown</span>
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
                                                            <i class="dw dw-eye"></i> Lihat
                                                        </button>
                                                        <button class="dropdown-item print-penjualan"
                                                            data-id="{{ $item->id }}">
                                                            <i class="dw dw-print"></i> Print
                                                        </button>
                                                        <button class="dropdown-item delete-penjualan"
                                                            data-id="{{ $item->id }}">
                                                            <i class="dw dw-delete-3"></i> Hapus
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

    <!-- Modal Tambah Item -->
    <div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="itemForm">
                        <div class="form-group">
                            <label for="jenis_item">Jenis Item</label>
                            <select class="form-control" id="jenis_item" name="jenis_item" required>
                                <option value="">Pilih Jenis</option>
                                <option value="produk">Produk</option>
                                <option value="bahan_baku">Bahan Baku</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="item_id">Item</label>
                            <select class="form-control select2" id="item_id" name="item_id" required>
                                <option value="">Pilih Item</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="jumlah">Jumlah</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" min="1"
                                required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Stok Tersedia:</label>
                                    <div id="statusInfo" class="badge badge-info">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Satuan:</label>
                                    <div id="satuanInfo" class="badge badge-secondary">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Harga Satuan:</label>
                                    <div id="hargaInfo" class="badge badge-success">Rp 0</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Subtotal:</label>
                                    <div id="subtotalInfo" class="badge badge-primary">Rp 0</div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="saveItemBtn">Tambah ke Keranjang</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Penjualan -->
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
                    <div id="detailContent">
                    </div>
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
@endsection

@push('styles')
    <style>
        .table th,
        .table td {
            vertical-align: middle;
        }

        .select2-container {
            width: 100% !important;
        }

        .badge {
            font-size: 12px;
            padding: 5px 10px;
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

        .item-detail {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .item-detail:last-child {
            border-bottom: none;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let itemCounter = 0;
            let currentItems = [];
            let currentPenjualanId = null;

            $('#item_id').select2({
                placeholder: "Pilih Item",
                allowClear: true
            });

            $('#addItemModal').on('hidden.bs.modal', function() {
                $('#itemForm')[0].reset();
                $('#statusInfo').text('-').removeClass('badge-danger badge-warning badge-success').addClass(
                    'badge-info');
                $('#hargaInfo').text('Rp 0').removeClass('badge-success').addClass('badge-success');
                $('#subtotalInfo').text('Rp 0').removeClass('badge-primary').addClass('badge-primary');
                $('#satuanInfo').text('-').removeClass('badge-secondary').addClass('badge-secondary');
                $('#item_id').empty().append('<option value="">Pilih Item</option>');
                $('#jenis_item').val('');
            });

            $('#addItemBtn').click(function() {
                $('#addItemModal').modal('show');
            });

            $('#jenis_item').change(function() {
                const jenis = $(this).val();
                $('#item_id').empty().append('<option value="">Pilih Item</option>');

                if (jenis === 'produk') {
                    @foreach ($produk as $item)
                        $('#item_id').append(
                            '<option value="{{ $item->id }}" data-harga="{{ $item->harga }}" data-satuan="{{ $item->satuan }}">{{ $item->nama }}</option>'
                        );
                    @endforeach
                } else if (jenis === 'bahan_baku') {
                    @foreach ($bahanBaku as $item)
                        $('#item_id').append(
                            '<option value="{{ $item->id }}" data-stok="{{ $item->stok }}" data-harga="{{ $item->harga_jual }}" data-satuan="{{ $item->satuan }}">{{ $item->nama }}</option>'
                        );
                    @endforeach
                }

                $('#statusInfo').text('-').removeClass('badge-danger badge-warning badge-success').addClass(
                    'badge-info');
                $('#hargaInfo').text('Rp 0').removeClass('badge-success').addClass('badge-success');
                $('#subtotalInfo').text('Rp 0').removeClass('badge-primary').addClass('badge-primary');
                $('#satuanInfo').text('-').removeClass('badge-secondary').addClass('badge-secondary');

                $('#item_id').trigger('change.select2');
            });

            $('#item_id').change(function() {
                const selectedOption = $(this).find('option:selected');
                const jenis = $('#jenis_item').val();
                const harga = selectedOption.data('harga');
                const satuan = selectedOption.data('satuan');

                if (harga !== undefined) {
                    if (jenis === 'produk') {
                        $('#statusInfo').text('Produk - Bahan baku akan diproses saat penjualan')
                            .removeClass('badge-danger badge-warning badge-success')
                            .addClass('badge-info');
                    } else {
                        const stok = selectedOption.data('stok');
                        $('#statusInfo').text(stok);
                        if (stok <= 0) {
                            $('#statusInfo').removeClass('badge-info badge-warning badge-success').addClass(
                                'badge-danger');
                        } else if (stok <= 10) {
                            $('#statusInfo').removeClass('badge-info badge-danger badge-success').addClass(
                                'badge-warning');
                        } else {
                            $('#statusInfo').removeClass('badge-info badge-danger badge-warning').addClass(
                                'badge-success');
                        }
                    }

                    $('#hargaInfo').text('Rp ' + numberFormat(harga));
                    $('#satuanInfo').text(satuan);
                    calculateSubtotal();
                } else {
                    $('#statusInfo').text('-').removeClass('badge-danger badge-warning badge-success')
                        .addClass('badge-info');
                    $('#hargaInfo').text('Rp 0').removeClass('badge-success').addClass('badge-success');
                    $('#subtotalInfo').text('Rp 0').removeClass('badge-primary').addClass('badge-primary');
                    $('#satuanInfo').text('-').removeClass('badge-secondary').addClass('badge-secondary');
                }
            });

            $('#jumlah').on('input', function() {
                calculateSubtotal();
            });

            function calculateSubtotal() {
                const jumlah = parseInt($('#jumlah').val()) || 0;
                const hargaText = $('#hargaInfo').text();
                const harga = parseFloat(hargaText.replace('Rp ', '').replace(/\./g, '')) || 0;
                const subtotal = jumlah * harga;

                $('#subtotalInfo').text('Rp ' + numberFormat(subtotal));
            }

            $('#saveItemBtn').click(function() {
                const jenisItem = $('#jenis_item').val();
                const itemId = $('#item_id').val();
                const jumlah = $('#jumlah').val();
                const selectedOption = $('#item_id').find('option:selected');

                if (!jenisItem || !itemId || !jumlah) {
                    Swal.fire('Peringatan', 'Harap lengkapi semua field', 'warning');
                    return;
                }

                if (jenisItem === 'bahan_baku') {
                    const stok = selectedOption.data('stok');
                    if (jumlah > stok) {
                        Swal.fire('Error', 'Jumlah melebihi stok tersedia. Stok tersedia: ' + stok,
                            'error');
                        return;
                    }
                }

                if (jumlah <= 0) {
                    Swal.fire('Error', 'Jumlah harus lebih dari 0', 'error');
                    return;
                }

                const harga = parseFloat(selectedOption.data('harga'));
                const subtotal = harga * jumlah;

                const item = {
                    id: itemCounter++,
                    jenis_item: jenisItem,
                    item_id: itemId,
                    nama: $('#item_id option:selected').text(),
                    jumlah: parseInt(jumlah),
                    harga: harga,
                    subtotal: subtotal,
                    satuan: selectedOption.data('satuan')
                };

                currentItems.push(item);
                updateItemsTable();
                updateTotal();
                $('#addItemModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Item berhasil ditambahkan ke keranjang',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            function updateItemsTable() {
                const tbody = $('#itemsBody');
                tbody.empty();

                if (currentItems.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center text-muted">Belum ada item</td></tr>');
                } else {
                    currentItems.forEach((item, index) => {
                        const row = `
                    <tr>
                        <td>${item.jenis_item === 'produk' ? 'Produk' : 'Bahan Baku'}</td>
                        <td>${item.nama}</td>
                        <td>${item.jumlah} ${item.satuan}</td>
                        <td>Rp ${numberFormat(item.harga)}</td>
                        <td>Rp ${numberFormat(item.subtotal)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-item" data-index="${index}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                        tbody.append(row);
                    });
                }
            }

            $(document).on('click', '.remove-item', function() {
                const index = $(this).data('index');
                currentItems.splice(index, 1);
                updateItemsTable();
                updateTotal();
            });

            function updateTotal() {
                const total = currentItems.reduce((sum, item) => sum + item.subtotal, 0);
                $('#totalAmount').text('Rp ' + numberFormat(total));
                $('#total').val('Rp ' + numberFormat(total));
                calculateKembalian();
            }

            $('#bayar').on('input', function() {
                calculateKembalian();
            });

            function calculateKembalian() {
                const total = parseFloat($('#totalAmount').text().replace('Rp ', '').replace(/\./g, '')) || 0;
                const bayar = parseFloat($('#bayar').val()) || 0;
                const kembalian = bayar - total;

                $('#kembalian').val('Rp ' + numberFormat(kembalian > 0 ? kembalian : 0));
                $('#bayarAmount').text('Rp ' + numberFormat(bayar));
                $('#kembalianAmount').text('Rp ' + numberFormat(kembalian > 0 ? kembalian : 0));
            }

            $('#penjualanForm').submit(function(e) {
                e.preventDefault();

                if (currentItems.length === 0) {
                    Swal.fire('Peringatan', 'Harap tambahkan minimal satu item', 'warning');
                    return;
                }

                const bayar = parseFloat($('#bayar').val()) || 0;
                const total = parseFloat($('#totalAmount').text().replace('Rp ', '').replace(/\./g, '')) ||
                    0;

                if (bayar < total) {
                    Swal.fire('Error', 'Jumlah pembayaran kurang dari total', 'error');
                    return;
                }

                const formData = {
                    nama_customer: $('#nama_customer').val(),
                    bayar: bayar,
                    items: currentItems.map(item => ({
                        jenis_item: item.jenis_item,
                        item_id: item.item_id,
                        jumlah: item.jumlah
                    }))
                };

                $.ajax({
                    url: '{{ route('admin.penjualan.store') }}',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                html: `
                                <div class="text-left">
                                    <p><strong>Penjualan berhasil disimpan!</strong></p>
                                    <p>Kode: ${response.data.kode_penjualan}</p>
                                    <p>Total: Rp ${numberFormat(response.data.total)}</p>
                                    <p>Bayar: Rp ${numberFormat(response.data.bayar)}</p>
                                    <p>Kembalian: Rp ${numberFormat(response.data.kembalian)}</p>
                                    <p>Kasir: ${response.data.admin_name}</p>
                                </div>
                            `,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $('#penjualanForm')[0].reset();
                                currentItems = [];
                                updateItemsTable();
                                updateTotal();
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        let errorMessage = 'Terjadi kesalahan';

                        if (response && response.errors) {
                            errorMessage = Object.values(response.errors).join('<br>');
                        } else if (response && response.message) {
                            errorMessage = response.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: errorMessage
                        });
                    }
                });
            });

            $('.view-penjualan').click(function() {
                const id = $(this).data('id');
                currentPenjualanId = id;

                $.get('{{ url('admin/penjualan') }}/' + id, function(response) {
                    if (response.status === 'success') {
                        const penjualan = response.data;
                        let detailHtml = `
                        <div class="detail-summary">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Kode Penjualan:</strong> ${penjualan.kode_penjualan}</p>
                                    <p><strong>Customer:</strong> ${penjualan.nama_customer}</p>
                                    <p><strong>Tanggal:</strong> ${penjualan.tanggal_formatted}</p>
                                    <p><strong>Kasir:</strong> <span class="badge badge-info">${penjualan.admin ? penjualan.admin.name : 'Unknown'}</span></p>
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
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                        penjualan.detail_penjualan.forEach(detail => {
                            detailHtml += `
                            <tr>
                                <td>${detail.jenis_item === 'produk' ? 'Produk' : 'Bahan Baku'}</td>
                                <td>${detail.nama_produk}</td>
                                <td>${detail.jumlah}</td>
                                <td>${detail.harga_sat_formatted}</td>
                                <td>${detail.sub_total_formatted}</td>
                            </tr>
                        `;
                        });

                        detailHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;

                        $('#detailContent').html(detailHtml);
                        $('#detailPenjualanModal').modal('show');
                    }
                });
            });

            $('.print-penjualan').click(function() {
                const id = $(this).data('id');
                window.open('{{ url('admin/penjualan') }}/' + id + '/print', '_blank');
            });

            $('#printNotaBtn').click(function() {
                if (currentPenjualanId) {
                    window.open('{{ url('admin/penjualan') }}/' + currentPenjualanId + '/print', '_blank');
                }
            });

            $('.delete-penjualan').click(function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data penjualan akan dihapus dan stok bahan baku akan dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url('admin/penjualan') }}/' + id,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: response.message
                                    }).then(() => {
                                        location.reload();
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Terjadi kesalahan saat menghapus data'
                                });
                            }
                        });
                    }
                });
            });

            function numberFormat(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            updateItemsTable();
        });
    </script>
@endpush
