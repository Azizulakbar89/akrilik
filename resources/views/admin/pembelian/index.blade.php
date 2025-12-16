@extends('layoutsAPP.deskapp')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Pembelian</h3>
                        <div class="float-right">
                            @if ($rekomendasi->count() > 0)
                                <button class="btn btn-success mr-2" data-toggle="modal" data-target="#modalPembelianCepat"
                                    id="btnPembelianCepat">
                                    <i class="fas fa-bolt"></i> Pembelian Cepat (ROP)
                                </button>
                            @endif
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah"
                                id="btnTambahPembelian">
                                <i class="fas fa-plus"></i> Tambah Pembelian
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($stokTidakAman->count() > 0)
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle"></i> Peringatan Stok Tidak Aman</h5>
                                <p>Bahan baku berikut memiliki stok yang tidak aman dan perlu segera dilakukan pembelian:
                                </p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Bahan Baku</th>
                                                <th>Stok Saat Ini</th>
                                                <th>Safety Stock</th>
                                                <th>Min</th>
                                                <th>ROP</th>
                                                <th>Rekomendasi Beli</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($stokTidakAman as $bahan)
                                                @php
                                                    $rekomendasiBeli = $bahan->rop;
                                                @endphp
                                                <tr>
                                                    <td>{{ $bahan->nama }}</td>
                                                    <td>{{ $bahan->stok }} {{ $bahan->satuan }}</td>
                                                    <td>{{ $bahan->safety_stock }} {{ $bahan->satuan }}</td>
                                                    <td>{{ $bahan->min }} {{ $bahan->satuan }}</td>
                                                    <td>{{ $bahan->rop }} {{ $bahan->satuan }}</td>
                                                    <td class="text-success">
                                                        <strong>{{ $rekomendasiBeli }} {{ $bahan->satuan }}</strong>
                                                    </td>
                                                    <td>{!! $bahan->status_stok !!}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if ($rekomendasi->count() > 0)
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5><i class="fas fa-lightbulb"></i> Rekomendasi Pembelian (Sistem ROP)</h5>
                                    <span class="badge badge-primary">Total: Rp
                                        {{ number_format($totalRekomendasi, 0, ',', '.') }}</span>
                                </div>
                                <p>Bahan baku berikut perlu dilakukan pembelian berdasarkan sistem ROP (Reorder Point):</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Bahan Baku</th>
                                                <th>Stok Saat Ini</th>
                                                <th>Min</th>
                                                <th>Max</th>
                                                <th>ROP (Max-Min)</th>
                                                <th>Harga Beli</th>
                                                <th>Jumlah Beli</th>
                                                <th>Sub Total</th>
                                                <th>Satuan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($rekomendasi as $item)
                                                @php
                                                    $subTotal = $item['jumlah_rekomendasi'] * $item['harga_beli'];
                                                @endphp
                                                <tr id="rekomendasi-row-{{ $item['bahan_baku_id'] }}">
                                                    <td>{{ $item['nama'] }}</td>
                                                    <td>{{ $item['stok_sekarang'] }}</td>
                                                    <td>{{ $item['min'] }}</td>
                                                    <td>{{ $item['max'] }}</td>
                                                    <td><strong class="text-primary">{{ $item['rop'] }}</strong></td>
                                                    <td>Rp {{ number_format($item['harga_beli'], 0, ',', '.') }}</td>
                                                    <td><strong
                                                            class="text-success">{{ $item['jumlah_rekomendasi'] }}</strong>
                                                    </td>
                                                    <td>Rp {{ number_format($subTotal, 0, ',', '.') }}</td>
                                                    <td>{{ $item['satuan'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary">
                                                <th colspan="6" class="text-right">Total Rekomendasi:</th>
                                                <th colspan="3">Rp {{ number_format($totalRekomendasi, 0, ',', '.') }}
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle"></i> Stok Aman</h5>
                                <p>Semua bahan baku dalam kondisi aman. Tidak ada rekomendasi pembelian saat ini.</p>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="table-pembelian">
                                <thead>
                                    <tr>
                                        <th>Kode Pembelian</th>
                                        <th>Supplier</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pembelian as $item)
                                        <tr>
                                            <td>{{ $item->kode_pembelian }}</td>
                                            <td>{{ $item->supplier->nama }}</td>
                                            <td>{{ $item->tanggal_formatted }}</td>
                                            <td>{{ $item->total_formatted }}</td>
                                            <td>{!! $item->status_label !!}</td>
                                            <td>
                                                <button class="btn btn-info btn-sm btn-detail"
                                                    data-id="{{ $item->id }}" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                @if ($item->isMenungguPersetujuan())
                                                    <button class="btn btn-warning btn-sm btn-edit"
                                                        data-id="{{ $item->id }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <button class="btn btn-danger btn-sm btn-delete"
                                                        data-id="{{ $item->id }}" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
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

    <!-- Modal Pembelian Cepat -->
    <div class="modal fade" id="modalPembelianCepat">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="formPembelianCepat">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">Pembelian Cepat dari Rekomendasi Sistem ROP</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Supplier <span class="text-danger">*</span></label>
                                    <select name="supplier_id" class="form-control" required>
                                        <option value="">Pilih Supplier</option>
                                        @foreach ($supplier as $sup)
                                            <option value="{{ $sup->id }}">{{ $sup->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Items Rekomendasi Pembelian (ROP)</label>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all">
                                        <label class="form-check-label" for="select-all">
                                            Pilih Semua
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge badge-info" id="selected-count">{{ $rekomendasi->count() }} item
                                        terpilih</span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-primary">
                                        <tr>
                                            <th width="50px">Pilih</th>
                                            <th>Bahan Baku</th>
                                            <th>Stok Sekarang</th>
                                            <th>Min</th>
                                            <th>Max</th>
                                            <th>ROP</th>
                                            <th>Harga Beli</th>
                                            <th>Jumlah Beli</th>
                                            <th>Sub Total</th>
                                            <th width="70px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rekomendasi-items">
                                        @foreach ($rekomendasi as $item)
                                            @php
                                                $subTotal = $item['jumlah_rekomendasi'] * $item['harga_beli'];
                                            @endphp
                                            <tr id="rekomendasi-row-{{ $item['bahan_baku_id'] }}">
                                                <td class="text-center">
                                                    <input type="checkbox" name="items[]"
                                                        value="{{ $item['bahan_baku_id'] }}"
                                                        class="form-check-input item-checkbox"
                                                        data-id="{{ $item['bahan_baku_id'] }}"
                                                        data-jumlah="{{ $item['jumlah_rekomendasi'] }}"
                                                        data-harga="{{ $item['harga_beli'] }}"
                                                        data-total="{{ $subTotal }}" checked>
                                                </td>
                                                <td>{{ $item['nama'] }}</td>
                                                <td>{{ $item['stok_sekarang'] }} {{ $item['satuan'] }}</td>
                                                <td>{{ $item['min'] }}</td>
                                                <td>{{ $item['max'] }}</td>
                                                <td class="text-primary font-weight-bold">
                                                    {{ $item['rop'] }} {{ $item['satuan'] }}
                                                </td>
                                                <td>Rp {{ number_format($item['harga_beli'], 0, ',', '.') }}</td>
                                                <td class="text-success font-weight-bold">
                                                    {{ $item['jumlah_rekomendasi'] }} {{ $item['satuan'] }}
                                                </td>
                                                <td class="text-primary font-weight-bold">Rp
                                                    {{ number_format($subTotal, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm btn-remove-rekomendasi"
                                                        data-id="{{ $item['bahan_baku_id'] }}" title="Hapus dari daftar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-success">
                                        <tr>
                                            <th colspan="8" class="text-right">Total Pembelian:</th>
                                            <th id="total-rekomendasi">Rp
                                                {{ number_format($totalRekomendasi, 0, ',', '.') }}</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                <strong>Sistem ROP (Reorder Point):</strong> Pembelian direkomendasikan ketika stok ≤ Min.
                                Jumlah pembelian = <strong>ROP (Max - Min)</strong>. ROP sudah dihitung otomatis berdasarkan
                                penggunaan historis.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            id="btnBatalPembelianCepat">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitPembelianCepat">
                            <i class="fas fa-bolt"></i> Buat Pembelian Cepat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Pembelian -->
    <div class="modal fade" id="modalTambah">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formTambah">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">Tambah Pembelian Baru</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Supplier <span class="text-danger">*</span></label>
                                    <select name="supplier_id" class="form-control" required>
                                        <option value="">Pilih Supplier</option>
                                        @foreach ($supplier as $sup)
                                            <option value="{{ $sup->id }}">{{ $sup->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Items Pembelian <span class="text-danger">*</span></label>
                            <div class="alert alert-info">
                                <small>
                                    <i class="fas fa-info-circle"></i> Tambah item pembelian dengan mengisi form di bawah.
                                    Untuk bahan baku dengan stok ≤ Min, sistem akan menampilkan rekomendasi pembelian
                                    berdasarkan ROP.
                                </small>
                            </div>

                            <div id="items-container">
                            </div>

                            <div class="d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-secondary" id="btn-add-item">
                                    <i class="fas fa-plus"></i> Tambah Item Baru
                                </button>

                                <button type="button" class="btn btn-info" id="btn-use-recommendation">
                                    <i class="fas fa-magic"></i> Gunakan Rekomendasi ROP
                                </button>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <div class="alert alert-success">
                                <label class="h5 mb-0">Total Pembelian:
                                    <span id="total-display" class="font-weight-bold">Rp 0</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            id="btnBatalTambah">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitTambah">
                            <i class="fas fa-save"></i> Simpan Pembelian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formEdit">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Pembelian</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="edit-content">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update Pembelian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetail">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Detail Pembelian</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="detail-content">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .item-row {
            padding-bottom: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .item-row:hover {
            background-color: #f8f9fa;
        }

        .badge {
            font-size: 12px;
            padding: 5px 10px;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.5rem;
            vertical-align: middle;
        }

        .alert .table {
            margin-bottom: 0;
        }

        .form-check-input {
            margin-top: 0;
            cursor: pointer;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-primary {
            color: #007bff !important;
        }

        .table-primary th {
            background-color: #007bff;
            color: white;
        }

        .table-success th {
            background-color: #28a745;
            color: white;
        }

        .loading {
            opacity: 0.5;
            pointer-events: none;
        }

        .btn-remove-item {
            padding: 6px 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-remove-item:hover {
            background-color: #dc3545;
            color: white;
        }

        .btn-remove-rekomendasi {
            padding: 3px 8px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-remove-rekomendasi:hover {
            background-color: #dc3545;
            color: white;
        }

        #items-container {
            min-height: 150px;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .empty-items {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }

        .item-row-removing {
            opacity: 0.5;
            transform: translateX(-20px);
        }

        .modal-content {
            border-radius: 10px;
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn {
            transition: all 0.3s ease;
        }

        .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            console.log('Script pembelian loaded');

            let itemCounter = 0;
            let rekomendasiData = [];
            let currentEditId = null;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            initializeTambahModal();

            function initializeTambahModal() {
                console.log('Inisialisasi modal tambah');
                $('#items-container').empty();
                itemCounter = 0;

                addEmptyItemRow();
                calculateTotal();
                updateRemoveButtons();
            }

            function addEmptyItemRow() {
                const newItem = `
                    <div class="item-row row mb-3 border-bottom pb-3" data-row-index="${itemCounter}">
                        <div class="col-md-5">
                            <select name="items[${itemCounter}][bahan_baku_id]" class="form-control bahan-baku-select" required>
                                <option value="">Pilih Bahan Baku</option>
                                @foreach ($bahanBaku as $bahan)
                                <option value="{{ $bahan->id }}" 
                                    data-harga="{{ $bahan->harga_beli }}"
                                    data-stok="{{ $bahan->stok }}"
                                    data-min="{{ $bahan->min }}"
                                    data-max="{{ $bahan->max }}"
                                    data-rop="{{ $bahan->rop }}"
                                    data-satuan="{{ $bahan->satuan }}">
                                    {{ $bahan->nama }} 
                                    @if ($bahan->isPerluPembelian())
                                        <span class="text-danger">(Stok: {{ $bahan->stok }}/Min: {{ $bahan->min }} - ROP: {{ $bahan->rop }})</span>
                                    @else
                                        (Stok: {{ $bahan->stok }}/Min: {{ $bahan->min }})
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[${itemCounter}][jumlah]" class="form-control jumlah" placeholder="Jumlah" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="items[${itemCounter}][harga]" class="form-control harga" placeholder="Harga" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2 text-center">
                            <button type="button" class="btn btn-danger btn-sm btn-remove-item" ${itemCounter === 0 ? 'disabled' : ''}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;

                $('#items-container').append(newItem);
                itemCounter++;
            }

            $('#btn-add-item').click(function() {
                console.log('Tombol tambah item diklik');
                addEmptyItemRow();
                updateRemoveButtons();
            });

            $(document).on('click', '.btn-remove-item', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('Tombol hapus item diklik');

                const $itemRow = $(this).closest('.item-row');
                console.log('Baris ditemukan:', $itemRow.length > 0);
                console.log('Jumlah total baris:', $('.item-row').length);

                if ($('.item-row').length <= 1) {
                    alert('Minimal harus ada satu item pembelian');
                    return;
                }

                if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
                    $itemRow.addClass('item-row-removing');

                    setTimeout(() => {
                        $itemRow.remove();
                        console.log('Baris dihapus');

                        reindexItemRows();
                        calculateTotal();
                        updateRemoveButtons();
                    }, 300);
                }
            });

            $(document).on('click', '.btn-remove-rekomendasi', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('Tombol hapus rekomendasi diklik');

                const bahanBakuId = $(this).data('id');
                console.log('ID bahan baku:', bahanBakuId);

                if (confirm('Apakah Anda yakin ingin menghapus item ini dari daftar rekomendasi?')) {
                    const row = $(`#rekomendasi-row-${bahanBakuId}`);
                    console.log('Baris ditemukan:', row.length > 0);

                    if (row.length > 0) {
                        row.find('.item-checkbox').prop('checked', false);

                        row.fadeOut(300, function() {
                            $(this).remove();
                            calculateRekomendasiTotal();
                            updateSelectedCount();
                            console.log('Baris rekomendasi dihapus');
                        });
                    }
                }
            });

            function updateRemoveButtons() {
                console.log('Memperbarui tombol hapus');
                const itemRows = $('.item-row');
                console.log('Jumlah baris:', itemRows.length);

                itemRows.each(function(index) {
                    const $removeBtn = $(this).find('.btn-remove-item');
                    const isDisabled = itemRows.length <= 1;
                    $removeBtn.prop('disabled', isDisabled);
                    console.log(`Baris ${index}: tombol ${isDisabled ? 'disabled' : 'enabled'}`);
                });
            }

            function reindexItemRows() {
                console.log('Memulai reindex item rows');
                itemCounter = 0;
                $('.item-row').each(function(index) {
                    $(this).attr('data-row-index', index);

                    $(this).find('.bahan-baku-select').attr('name', `items[${index}][bahan_baku_id]`);
                    $(this).find('.jumlah').attr('name', `items[${index}][jumlah]`);
                    $(this).find('.harga').attr('name', `items[${index}][harga]`);

                    itemCounter = index + 1;
                });
                console.log('Reindex selesai, itemCounter:', itemCounter);
            }

            $('#select-all').change(function() {
                $('.item-checkbox').prop('checked', $(this).prop('checked'));
                calculateRekomendasiTotal();
                updateSelectedCount();
            });

            $(document).on('change', '.item-checkbox', function() {
                calculateRekomendasiTotal();
                updateSelectedCount();

                const totalItems = $('.item-checkbox').length;
                const checkedItems = $('.item-checkbox:checked').length;
                $('#select-all').prop('checked', totalItems === checkedItems);
            });

            function updateSelectedCount() {
                const selectedCount = $('.item-checkbox:checked').length;
                $('#selected-count').text(selectedCount + ' item terpilih');
            }

            function calculateRekomendasiTotal() {
                let total = 0;
                let selectedCount = 0;

                $('.item-checkbox:checked').each(function() {
                    const totalValue = $(this).data('total') || 0;
                    total += parseFloat(totalValue) || 0;
                    selectedCount++;
                });

                $('#total-rekomendasi').text('Rp ' + total.toLocaleString('id-ID'));
                $('#selected-count').text(selectedCount + ' item terpilih');
            }

            $(document).on('change', '.bahan-baku-select', function() {
                const selectedOption = $(this).find('option:selected');
                const harga = selectedOption.data('harga');
                const stok = selectedOption.data('stok');
                const min = selectedOption.data('min');
                const rop = selectedOption.data('rop');
                const satuan = selectedOption.data('satuan');

                if (harga) {
                    $(this).closest('.item-row').find('.harga').val(harga);
                }

                if (stok <= min && rop > 0) {
                    const jumlahInput = $(this).closest('.item-row').find('.jumlah');
                    jumlahInput.val(rop);

                    alert(
                        `⚠️ Stok ${stok} ${satuan} ≤ Min ${min} ${satuan}\nRekomendasi beli (ROP): ${rop} ${satuan}`
                    );
                }

                calculateTotal();
            });

            $(document).on('input', '.jumlah, .harga', function() {
                calculateTotal();
            });

            function calculateTotal() {
                let total = 0;
                $('.item-row').each(function() {
                    const jumlah = parseFloat($(this).find('.jumlah').val()) || 0;
                    const harga = parseFloat($(this).find('.harga').val()) || 0;
                    total += jumlah * harga;
                });
                $('#total-display').text('Rp ' + total.toLocaleString('id-ID'));
            }

            function calculateEditTotal() {
                let total = 0;
                $('#edit-items-container .item-row').each(function() {
                    const jumlah = parseFloat($(this).find('.jumlah').val()) || 0;
                    const harga = parseFloat($(this).find('.harga').val()) || 0;
                    total += jumlah * harga;
                });
                $('#edit-total-display').text('Rp ' + total.toLocaleString('id-ID'));
            }

            $('#formPembelianCepat').submit(function(e) {
                e.preventDefault();

                const selectedItems = $('.item-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedItems.length === 0) {
                    alert('Pilih minimal satu item untuk dibeli');
                    return;
                }

                if (!$('select[name="supplier_id"]').val()) {
                    alert('Pilih supplier terlebih dahulu');
                    return;
                }

                const formData = {
                    supplier_id: $('select[name="supplier_id"]').val(),
                    tanggal: $('input[name="tanggal"]').val(),
                    items: selectedItems,
                    _token: '{{ csrf_token() }}'
                };

                console.log('Submit pembelian cepat:', formData);

                $.ajax({
                    url: '{{ route('admin.pembelian.store.from.rekomendasi') }}',
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        $('#btnSubmitPembelianCepat').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                    },
                    success: function(response) {
                        console.log('Success:', response);
                        $('#modalPembelianCepat').modal('hide');
                        alert(response.success);

                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        let errorMessage = 'Terjadi kesalahan';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseText) {
                            errorMessage = xhr.responseText;
                        }
                        alert(errorMessage);
                    },
                    complete: function() {
                        $('#btnSubmitPembelianCepat').prop('disabled', false)
                            .html('<i class="fas fa-bolt"></i> Buat Pembelian Cepat');
                    }
                });
            });

            $('#formTambah').submit(function(e) {
                e.preventDefault();

                let valid = false;
                $('.bahan-baku-select').each(function() {
                    if ($(this).val()) {
                        valid = true;
                    }
                });

                if (!valid) {
                    alert('Pilih minimal satu bahan baku');
                    return;
                }

                let allValid = true;
                $('.item-row').each(function() {
                    const bahanBakuId = $(this).find('.bahan-baku-select').val();
                    const jumlah = $(this).find('.jumlah').val();
                    const harga = $(this).find('.harga').val();

                    if (bahanBakuId && (!jumlah || !harga)) {
                        allValid = false;
                    }
                });

                if (!allValid) {
                    alert('Semua item yang dipilih harus memiliki jumlah dan harga');
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.pembelian.store') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('#btnSubmitTambah').prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                    },
                    success: function(response) {
                        console.log('Success:', response);
                        $('#modalTambah').modal('hide');
                        alert(response.success);

                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        let errorMessage = 'Terjadi kesalahan';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        alert(errorMessage);
                    },
                    complete: function() {
                        $('#btnSubmitTambah').prop('disabled', false).html(
                            '<i class="fas fa-save"></i> Simpan Pembelian');
                    }
                });
            });

            $('#btnBatalTambah').click(function() {
                $('#modalTambah').modal('hide');
                setTimeout(() => {
                    $('#formTambah')[0].reset();
                    initializeTambahModal();
                }, 300);
            });

            $('#btnBatalPembelianCepat').click(function() {
                $('#modalPembelianCepat').modal('hide');
                setTimeout(() => {
                    $('#formPembelianCepat')[0].reset();
                    $('.item-checkbox').prop('checked', true);
                    calculateRekomendasiTotal();
                    updateSelectedCount();
                }, 300);
            });

            $('#modalTambah').on('show.bs.modal', function() {
                console.log('Modal tambah dibuka');
                initializeTambahModal();
                loadRekomendasiData();
            });

            $('#modalPembelianCepat').on('show.bs.modal', function() {
                console.log('Modal pembelian cepat dibuka');
                $('#select-all').prop('checked', true);
                $('.item-checkbox').prop('checked', true);
                calculateRekomendasiTotal();
                updateSelectedCount();
            });

            function loadRekomendasiData() {
                console.log('Loading rekomendasi data...');
                $.ajax({
                    url: '{{ route('admin.pembelian.rekomendasi.form') }}',
                    type: 'GET',
                    beforeSend: function() {
                        $('#btn-use-recommendation').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin"></i> Memuat...');
                    },
                    success: function(response) {
                        console.log('Rekomendasi data loaded:', response);
                        if (response.rekomendasi) {
                            rekomendasiData = response.rekomendasi;
                            console.log('Data rekomendasi dimuat:', rekomendasiData);
                        } else {
                            console.error('Invalid response format:', response);
                            rekomendasiData = [];
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading rekomendasi data:', xhr);
                        alert('Gagal memuat data rekomendasi');
                        rekomendasiData = [];
                    },
                    complete: function() {
                        $('#btn-use-recommendation').prop('disabled', false)
                            .html('<i class="fas fa-magic"></i> Gunakan Rekomendasi ROP');
                    }
                });
            }

            $('#btn-use-recommendation').click(function() {
                console.log('Tombol rekomendasi diklik');
                console.log('Data available:', rekomendasiData);

                if (!rekomendasiData || rekomendasiData.length === 0) {
                    alert(
                        'Tidak ada data rekomendasi yang tersedia. Pastikan ada bahan baku yang perlu pembelian.'
                    );
                    return;
                }

                $('#items-container').empty();
                itemCounter = 0;

                rekomendasiData.forEach((item, index) => {
                    if (item && item.jumlah_rekomendasi > 0) {
                        addRekomendasiRow(item);
                    }
                });

                calculateTotal();
                updateRemoveButtons();

                const itemCount = $('#items-container .item-row').length;
                alert(`Rekomendasi sistem ROP telah diterapkan untuk ${itemCount} bahan baku!`);
            });

            function addRekomendasiRow(item) {
                const newItem = `
                    <div class="item-row row mb-3 border-bottom pb-3" data-row-index="${itemCounter}">
                        <div class="col-md-5">
                            <select name="items[${itemCounter}][bahan_baku_id]" class="form-control bahan-baku-select" required>
                                <option value="">Pilih Bahan Baku</option>
                                @foreach ($bahanBaku as $bahan)
                                <option value="{{ $bahan->id }}" 
                                    data-harga="{{ $bahan->harga_beli }}"
                                    data-stok="{{ $bahan->stok }}"
                                    data-min="{{ $bahan->min }}"
                                    data-max="{{ $bahan->max }}"
                                    data-rop="{{ $bahan->rop }}"
                                    data-satuan="{{ $bahan->satuan }}"
                                    ${item.bahan_baku_id == {{ $bahan->id }} ? 'selected' : ''}>
                                    {{ $bahan->nama }} 
                                    @if ($bahan->isPerluPembelian())
                                        <span class="text-danger">(Stok: {{ $bahan->stok }}/Min: {{ $bahan->min }} - ROP: {{ $bahan->rop }})</span>
                                    @else
                                        (Stok: {{ $bahan->stok }}/Min: {{ $bahan->min }})
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[${itemCounter}][jumlah]" class="form-control jumlah" 
                                value="${item.jumlah_rekomendasi}" placeholder="Jumlah" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="items[${itemCounter}][harga]" class="form-control harga" 
                                value="${item.harga_beli}" placeholder="Harga" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2 text-center">
                            <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                $('#items-container').append(newItem);
                itemCounter++;
            }

            $(document).on('click', '.btn-detail', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `{{ url('admin/pembelian') }}/${id}`,
                    type: 'GET',
                    beforeSend: function() {
                        $('#detail-content').html(
                            '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Memuat data...</p></div>'
                        );
                    },
                    success: function(response) {
                        let itemsHtml = '';
                        response.detail_pembelian.forEach(item => {
                            itemsHtml += `
                                <tr>
                                    <td>${item.bahan_baku.nama}</td>
                                    <td>${item.jumlah} ${item.bahan_baku.satuan}</td>
                                    <td>Rp ${item.harga.toLocaleString('id-ID')}</td>
                                    <td>Rp ${item.sub_total.toLocaleString('id-ID')}</td>
                                </tr>
                            `;
                        });

                        $('#detail-content').html(`
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Kode Pembelian:</strong> ${response.kode_pembelian}</p>
                                    <p><strong>Supplier:</strong> ${response.supplier.nama}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Tanggal:</strong> ${new Date(response.tanggal).toLocaleDateString('id-ID')}</p>
                                    <p><strong>Status:</strong> ${response.status}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h5>Detail Items:</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Bahan Baku</th>
                                            <th>Jumlah</th>
                                            <th>Harga</th>
                                            <th>Sub Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${itemsHtml}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-right">Total:</th>
                                            <th>Rp ${parseFloat(response.total).toLocaleString('id-ID')}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        `);
                        $('#modalDetail').modal('show');
                    },
                    error: function(xhr) {
                        alert('Terjadi kesalahan saat memuat detail');
                    }
                });
            });

            $(document).on('click', '.btn-edit', function() {
                currentEditId = $(this).data('id');

                $.ajax({
                    url: `{{ url('admin/pembelian') }}/${currentEditId}/edit`,
                    type: 'GET',
                    beforeSend: function() {
                        $('#edit-content').html(
                            '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Memuat data...</p></div>'
                        );
                    },
                    success: function(response) {
                        if (response.error) {
                            alert(response.error);
                            return;
                        }

                        const pembelian = response.pembelian;
                        let itemsHtml = '';

                        pembelian.detail_pembelian.forEach((item, index) => {
                            const rop = item.bahan_baku.rop;
                            itemsHtml += `
                                <div class="item-row row mb-3 border-bottom pb-3" data-row-index="${index}">
                                    <div class="col-md-5">
                                        <select name="items[${index}][bahan_baku_id]" class="form-control bahan-baku-select" required>
                                            <option value="">Pilih Bahan Baku</option>
                                            ${response.bahanBaku.map(bahan => `
                                                                                                            <option value="${bahan.id}" 
                                                                                                                data-harga="${bahan.harga_beli}"
                                                                                                                data-stok="${bahan.stok}"
                                                                                                                data-min="${bahan.min}"
                                                                                                                data-max="${bahan.max}"
                                                                                                                data-rop="${bahan.rop}"
                                                                                                                data-satuan="${bahan.satuan}"
                                                                                                                ${item.bahan_baku_id == bahan.id ? 'selected' : ''}>
                                                                                                                ${bahan.nama} 
                                                                                                                ${bahan.stok <= bahan.min ? 
                                                                                                                    `<span class="text-danger">(Stok: ${bahan.stok}/Min: ${bahan.min} - ROP: ${bahan.rop})</span>` : 
                                                                                                                    `(Stok: ${bahan.stok}/Min: ${bahan.min})`}
                                                                                                            </option>
                                                                                                        `).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="items[${index}][jumlah]" class="form-control jumlah" 
                                            value="${item.jumlah}" placeholder="Jumlah" min="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="items[${index}][harga]" class="form-control harga" 
                                            value="${item.harga}" placeholder="Harga" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        });

                        $('#edit-content').html(`
                            <input type="hidden" name="_method" value="PUT">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Supplier</label>
                                        <select name="supplier_id" class="form-control" required>
                                            <option value="">Pilih Supplier</option>
                                            ${response.supplier.map(sup => `
                                                                                                            <option value="${sup.id}" ${pembelian.supplier_id == sup.id ? 'selected' : ''}>
                                                                                                                ${sup.nama}
                                                                                                            </option>
                                                                                                        `).join('')}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal</label>
                                        <input type="date" name="tanggal" class="form-control" value="${pembelian.tanggal}" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Items Pembelian</label>
                                <div id="edit-items-container">
                                    ${itemsHtml}
                                </div>
                                <button type="button" class="btn btn-secondary mt-2" id="btn-add-edit-item">
                                    <i class="fas fa-plus"></i> Tambah Item
                                </button>
                            </div>
                            
                            <div class="form-group mt-4">
                                <div class="alert alert-success">
                                    <label class="h5 mb-0">Total Pembelian: 
                                        <span id="edit-total-display" class="font-weight-bold">Rp ${parseFloat(pembelian.total).toLocaleString('id-ID')}</span>
                                    </label>
                                </div>
                            </div>
                        `);

                        $('#modalEdit').modal('show');
                        updateRemoveButtons();

                        calculateEditTotal();
                    },
                    error: function(xhr) {
                        alert('Terjadi kesalahan saat memuat data edit');
                    }
                });
            });

            $(document).on('click', '#btn-add-edit-item', function() {
                const index = $('#edit-items-container .item-row').length;
                const newItem = `
                    <div class="item-row row mb-3 border-bottom pb-3" data-row-index="${index}">
                        <div class="col-md-5">
                            <select name="items[${index}][bahan_baku_id]" class="form-control bahan-baku-select" required>
                                <option value="">Pilih Bahan Baku</option>
                                @foreach ($bahanBaku as $bahan)
                                <option value="{{ $bahan->id }}" 
                                    data-harga="{{ $bahan->harga_beli }}"
                                    data-stok="{{ $bahan->stok }}"
                                    data-min="{{ $bahan->min }}"
                                    data-max="{{ $bahan->max }}"
                                    data-rop="{{ $bahan->rop }}"
                                    data-satuan="{{ $bahan->satuan }}">
                                    {{ $bahan->nama }} 
                                    @if ($bahan->isPerluPembelian())
                                        <span class="text-danger">(Stok: {{ $bahan->stok }}/Min: {{ $bahan->min }} - ROP: {{ $bahan->rop }})</span>
                                    @else
                                        (Stok: {{ $bahan->stok }}/Min: {{ $bahan->min }})
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[${index}][jumlah]" class="form-control jumlah" placeholder="Jumlah" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="items[${index}][harga]" class="form-control harga" placeholder="Harga" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2 text-center">
                            <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                $('#edit-items-container').append(newItem);
                updateRemoveButtons();
            });

            $(document).on('submit', '#formEdit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: `{{ url('admin.pembelian') }}/${currentEditId}`,
                    type: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('#formEdit button[type="submit"]').prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin"></i> Mengupdate...');
                    },
                    success: function(response) {
                        console.log('Success:', response);
                        $('#modalEdit').modal('hide');
                        alert(response.success);
                        location.reload();
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        let errorMessage = 'Terjadi kesalahan';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        alert(errorMessage);
                    },
                    complete: function() {
                        $('#formEdit button[type="submit"]').prop('disabled', false).html(
                            'Update Pembelian');
                    }
                });
            });

            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus pembelian ini?')) {
                    $.ajax({
                        url: `{{ url('admin/pembelian') }}/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            console.log('Success:', response);
                            alert(response.success);
                            location.reload();
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr);
                            let errorMessage = 'Terjadi kesalahan';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }
                            alert(errorMessage);
                        }
                    });
                }
            });

            $(document).on('change input',
                '#edit-items-container .bahan-baku-select, #edit-items-container .jumlah, #edit-items-container .harga',
                function() {
                    calculateEditTotal();
                });

            console.log('Inisialisasi awal...');
            updateRemoveButtons();
            calculateTotal();
            updateSelectedCount();
        });
    </script>
@endpush
