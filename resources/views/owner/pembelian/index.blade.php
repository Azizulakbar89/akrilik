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
                                <button class="btn btn-success mr-2" data-toggle="modal" data-target="#modalPembelianCepat">
                                    <i class="fas fa-bolt"></i> Pembelian Cepat
                                </button>
                            @endif
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
                                <i class="fas fa-plus"></i> Tambah Pembelian
                            </button>
                            <button class="btn btn-info ml-2" data-toggle="modal" data-target="#modalPrintLaporan">
                                <i class="fas fa-print"></i> Print Laporan
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Alert Stok Tidak Aman -->
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
                                                <th>Stok Minimum</th>
                                                <th>Stok Maksimum</th>
                                                <th>Kekurangan</th>
                                                <th>Rekomendasi Beli</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($stokTidakAman as $bahan)
                                                <tr>
                                                    <td>{{ $bahan->nama }}</td>
                                                    <td>{{ $bahan->stok }} {{ $bahan->satuan }}</td>
                                                    <td>{{ $bahan->min }} {{ $bahan->satuan }}</td>
                                                    <td>{{ $bahan->max }} {{ $bahan->satuan }}</td>
                                                    <td class="text-danger"><strong>{{ $bahan->min - $bahan->stok }}
                                                            {{ $bahan->satuan }}</strong></td>
                                                    <td class="text-success">
                                                        <strong>{{ $bahan->jumlahPemesananRekomendasi() }}
                                                            {{ $bahan->satuan }}</strong>
                                                    </td>
                                                    <td>{!! $bahan->status_stok !!}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Rekomendasi Pembelian -->
                        @if ($rekomendasi->count() > 0)
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5><i class="fas fa-lightbulb"></i> Rekomendasi Pembelian (Sistem Min-Max)</h5>
                                    <span class="badge badge-primary">Total: Rp
                                        {{ number_format($totalRekomendasi, 0, ',', '.') }}</span>
                                </div>
                                <p>Bahan baku berikut perlu dilakukan pembelian berdasarkan sistem Min-Max:</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Bahan Baku</th>
                                                <th>Stok Saat Ini</th>
                                                <th>Min</th>
                                                <th>Max</th>
                                                <th>Rekomendasi Beli</th>
                                                <th>Harga Beli</th>
                                                <th>Sub Total</th>
                                                <th>Satuan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($rekomendasi as $item)
                                                <tr>
                                                    <td>{{ $item['nama'] }}</td>
                                                    <td>{{ $item['stok_sekarang'] }}</td>
                                                    <td>{{ $item['min'] }}</td>
                                                    <td>{{ $item['max'] }}</td>
                                                    <td><strong
                                                            class="text-success">{{ $item['jumlah_rekomendasi'] }}</strong>
                                                    </td>
                                                    <td>Rp {{ number_format($item['harga_beli'], 0, ',', '.') }}</td>
                                                    <td>Rp {{ number_format($item['total_nilai'], 0, ',', '.') }}</td>
                                                    <td>{{ $item['satuan'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary">
                                                <th colspan="6" class="text-right">Total Rekomendasi:</th>
                                                <th colspan="2">Rp {{ number_format($totalRekomendasi, 0, ',', '.') }}
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

                        <!-- Data Pembelian -->
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
                                            <td>{{ $item->kode_pembelian ?? 'PB-' . $item->id }}</td>
                                            <td>{{ $item->supplier->nama }}</td>
                                            <td>{{ $item->tanggal_formatted ?? $item->tanggal }}</td>
                                            <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                            <td>
                                                @if ($item->status == 'menunggu_persetujuan')
                                                    <span class="badge badge-warning">Menunggu Persetujuan</span>
                                                @elseif($item->status == 'completed')
                                                    <span class="badge badge-success">Selesai</span>
                                                @elseif($item->status == 'ditolak')
                                                    <span class="badge badge-danger">Ditolak</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $item->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-info btn-sm btn-detail"
                                                    data-id="{{ $item->id }}" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                @if ($item->status == 'menunggu_persetujuan')
                                                    <button class="btn btn-warning btn-sm btn-edit"
                                                        data-id="{{ $item->id }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <button class="btn btn-success btn-sm btn-approve"
                                                        data-id="{{ $item->id }}" title="Setujui">
                                                        <i class="fas fa-check"></i>
                                                    </button>

                                                    <button class="btn btn-danger btn-sm btn-reject"
                                                        data-id="{{ $item->id }}" title="Tolak">
                                                        <i class="fas fa-times"></i>
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

    <div class="modal fade" id="modalPembelianCepat">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="formPembelianCepat">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">Pembelian Cepat dari Rekomendasi Sistem Min-Max</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
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
                            <label>Items Rekomendasi Pembelian</label>
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
                                            <th>Jumlah Beli</th>
                                            <th>Harga Beli</th>
                                            <th>Sub Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rekomendasi-items">
                                        @foreach ($rekomendasi as $item)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="items[]"
                                                        value="{{ $item['bahan_baku_id'] }}"
                                                        class="form-check-input item-checkbox"
                                                        data-jumlah="{{ $item['jumlah_rekomendasi'] }}"
                                                        data-harga="{{ $item['harga_beli'] }}"
                                                        data-total="{{ $item['total_nilai'] }}" checked>
                                                </td>
                                                <td>{{ $item['nama'] }}</td>
                                                <td>{{ $item['stok_sekarang'] }} {{ $item['satuan'] }}</td>
                                                <td>{{ $item['min'] }}</td>
                                                <td>{{ $item['max'] }}</td>
                                                <td class="text-success font-weight-bold">
                                                    {{ $item['jumlah_rekomendasi'] }} {{ $item['satuan'] }}</td>
                                                <td>Rp {{ number_format($item['harga_beli'], 0, ',', '.') }}</td>
                                                <td class="text-primary font-weight-bold">Rp
                                                    {{ number_format($item['total_nilai'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-success">
                                        <tr>
                                            <th colspan="7" class="text-right">Total Pembelian:</th>
                                            <th id="total-rekomendasi">Rp
                                                {{ number_format($totalRekomendasi, 0, ',', '.') }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                <strong>Sistem Min-Max:</strong> Pembelian direkomendasikan ketika stok ≤ Min.
                                Jumlah pembelian = Max - Stok Saat Ini.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-bolt"></i> Buat Pembelian Cepat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambah">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formTambah">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">Tambah Pembelian Baru</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
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

                            @if ($rekomendasi->count() > 0)
                                <div class="alert alert-info py-2">
                                    <small>
                                        <i class="fas fa-lightbulb"></i>
                                        <strong>Tip:</strong> Gunakan rekomendasi sistem untuk bahan baku yang stoknya tidak
                                        aman.
                                        <button type="button" class="btn btn-sm btn-outline-primary ml-2"
                                            id="btn-use-recommendation">
                                            <i class="fas fa-magic"></i> Gunakan Rekomendasi
                                        </button>
                                    </small>
                                </div>
                            @endif

                            <div id="items-container">
                                <div class="item-row row mb-2">
                                    <div class="col-md-5">
                                        <select name="items[0][bahan_baku_id]" class="form-control bahan-baku-select"
                                            required>
                                            <option value="">Pilih Bahan Baku</option>
                                            @foreach ($bahanBaku as $bahan)
                                                <option value="{{ $bahan->id }}"
                                                    data-harga="{{ $bahan->harga_beli }}"
                                                    data-stok="{{ $bahan->stok }}" data-min="{{ $bahan->min }}"
                                                    data-max="{{ $bahan->max }}" data-satuan="{{ $bahan->satuan }}">
                                                    {{ $bahan->nama }}
                                                    @if ($bahan->stok <= $bahan->min)
                                                        <span class="text-danger">(Stok: {{ $bahan->stok }}
                                                            {{ $bahan->satuan }} - PERLU BELI!)</span>
                                                    @else
                                                        (Stok: {{ $bahan->stok }} {{ $bahan->satuan }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="items[0][jumlah]" class="form-control jumlah"
                                            placeholder="Jumlah" min="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="items[0][harga]" class="form-control harga"
                                            placeholder="Harga" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-remove-item" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" id="btn-add-item">
                                <i class="fas fa-plus"></i> Tambah Item
                            </button>
                        </div>

                        <div class="form-group">
                            <label class="h5">Total: <span id="total-display" class="text-success">Rp
                                    0</span></label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

    <div class="modal fade" id="modalPrintLaporan">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('owner.pembelian.laporan') }}" method="GET" target="_blank">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">Print Laporan Pembelian Berhasil</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Tanggal Awal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_awal" class="form-control" value="{{ date('Y-m-01') }}"
                                required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Akhir <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_akhir" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKonfirmasi" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p id="konfirmasi-pesan"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="konfirmasi-ya">Ya</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .item-row {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .badge {
            font-size: 12px;
            padding: 5px 10px;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.5rem;
        }

        .alert .table {
            margin-bottom: 0;
        }

        .form-check-input {
            margin-top: 0;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .table-primary th {
            background-color: #007bff;
            color: white;
        }

        .table-success th {
            background-color: #28a745;
            color: white;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        #total-display,
        #edit-total-display {
            font-weight: bold;
            font-size: 1.2rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let itemCounter = 1;
            let currentEditId = null;

            // Inisialisasi CSRF token untuk AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Form Pembelian Cepat
            $('#formPembelianCepat').submit(function(e) {
                e.preventDefault();

                const selectedItems = $('.item-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedItems.length === 0) {
                    showAlert('error', 'Pilih minimal satu item untuk dibeli');
                    return;
                }

                if (!$('select[name="supplier_id"]').val()) {
                    showAlert('error', 'Pilih supplier terlebih dahulu');
                    return;
                }

                const formData = {
                    supplier_id: $('select[name="supplier_id"]').val(),
                    tanggal: $('input[name="tanggal"]').val(),
                    items: selectedItems,
                    _token: '{{ csrf_token() }}'
                };

                // PERBAIKAN: Gunakan route yang benar untuk owner
                $.ajax({
                    url: '{{ route('owner.pembelian.store.from.rekomendasi') }}', // Route yang sudah diperbaiki
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        $('#formPembelianCepat button[type="submit"]').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                    },
                    success: function(response) {
                        $('#modalPembelianCepat').modal('hide');
                        showAlert('success', response.success);
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1500);
                        } else {
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseText) {
                            errorMessage = xhr.responseText;
                        }
                        showAlert('error', errorMessage);
                    },
                    complete: function() {
                        $('#formPembelianCepat button[type="submit"]').prop('disabled', false)
                            .html('<i class="fas fa-bolt"></i> Buat Pembelian Cepat');
                    }
                });
            });

            // Select All Checkbox
            $('#select-all').change(function() {
                $('.item-checkbox').prop('checked', $(this).prop('checked'));
                calculateRekomendasiTotal();
                updateSelectedCount();
            });

            $(document).on('change', '.item-checkbox', function() {
                calculateRekomendasiTotal();
                updateSelectedCount();
            });

            function updateSelectedCount() {
                const selectedCount = $('.item-checkbox:checked').length;
                $('#selected-count').text(selectedCount + ' item terpilih');
            }

            function calculateRekomendasiTotal() {
                let total = 0;
                let selectedCount = 0;

                $('.item-checkbox:checked').each(function() {
                    const totalValue = $(this).data('total');
                    total += parseFloat(totalValue) || 0;
                    selectedCount++;
                });

                $('#total-rekomendasi').text('Rp ' + total.toLocaleString('id-ID'));
                $('#selected-count').text(selectedCount + ' item terpilih');
            }

            // Tambah Item Baru
            $('#btn-add-item').click(function() {
                const newItem = `
                <div class="item-row row mb-2">
                    <div class="col-md-5">
                        <select name="items[${itemCounter}][bahan_baku_id]" class="form-control bahan-baku-select" required>
                            <option value="">Pilih Bahan Baku</option>
                            @foreach ($bahanBaku as $bahan)
                            <option value="{{ $bahan->id }}" 
                                data-harga="{{ $bahan->harga_beli }}"
                                data-stok="{{ $bahan->stok }}"
                                data-min="{{ $bahan->min }}"
                                data-max="{{ $bahan->max }}"
                                data-satuan="{{ $bahan->satuan }}">
                                {{ $bahan->nama }} 
                                @if ($bahan->stok <= $bahan->min)
                                    <span class="text-danger">(Stok: {{ $bahan->stok }} {{ $bahan->satuan }} - PERLU BELI!)</span>
                                @else
                                    (Stok: {{ $bahan->stok }} {{ $bahan->satuan }})
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
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-remove-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
                $('#items-container').append(newItem);
                itemCounter++;
                updateRemoveButtons();
            });

            // Gunakan Rekomendasi
            $('#btn-use-recommendation').click(function() {
                @if ($rekomendasi->count() > 0)
                    // Kosongkan container terlebih dahulu
                    $('#items-container').empty();
                    itemCounter = 0;

                    @foreach ($rekomendasi as $item)
                        const newItem = `
                        <div class="item-row row mb-2">
                            <div class="col-md-5">
                                <select name="items[${itemCounter}][bahan_baku_id]" class="form-control bahan-baku-select" required>
                                    <option value="">Pilih Bahan Baku</option>
                                    @foreach ($bahanBaku as $bahan)
                                    <option value="{{ $bahan->id }}" 
                                        data-harga="{{ $bahan->harga_beli }}"
                                        data-stok="{{ $bahan->stok }}"
                                        data-min="{{ $bahan->min }}"
                                        data-max="{{ $bahan->max }}"
                                        data-satuan="{{ $bahan->satuan }}"
                                        {{ $item['bahan_baku_id'] == $bahan->id ? 'selected' : '' }}>
                                        {{ $bahan->nama }} 
                                        @if ($bahan->stok <= $bahan->min)
                                            <span class="text-danger">(Stok: {{ $bahan->stok }} {{ $bahan->satuan }} - PERLU BELI!)</span>
                                        @else
                                            (Stok: {{ $bahan->stok }} {{ $bahan->satuan }})
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="items[${itemCounter}][jumlah]" class="form-control jumlah" 
                                    value="{{ $item['jumlah_rekomendasi'] }}" placeholder="Jumlah" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="items[${itemCounter}][harga]" class="form-control harga" 
                                    value="{{ $item['harga_beli'] }}" placeholder="Harga" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-remove-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                        $('#items-container').append(newItem);
                        itemCounter++;
                    @endforeach

                    updateRemoveButtons();
                    calculateTotal();
                    showAlert('success',
                        'Rekomendasi sistem telah diterapkan untuk {{ $rekomendasi->count() }} bahan baku!'
                    );
                @endif
            });

            function updateRemoveButtons() {
                const itemRows = $('.item-row');
                itemRows.each(function(index) {
                    const $removeBtn = $(this).find('.btn-remove-item');
                    $removeBtn.prop('disabled', itemRows.length <= 1);
                });
            }

            $(document).on('click', '.btn-remove-item', function() {
                if ($('.item-row').length > 1) {
                    $(this).closest('.item-row').remove();
                    calculateTotal();
                    updateRemoveButtons();
                }
            });

            $(document).on('change', '.bahan-baku-select', function() {
                const selectedOption = $(this).find('option:selected');
                const harga = selectedOption.data('harga');
                const stok = selectedOption.data('stok');
                const min = selectedOption.data('min');
                const max = selectedOption.data('max');
                const satuan = selectedOption.data('satuan');

                if (harga) {
                    $(this).closest('.item-row').find('.harga').val(harga);
                }

                if (stok <= min) {
                    const rekomendasi = max - stok;
                    $(this).closest('.item-row').find('.jumlah').val(rekomendasi);

                    // Tampilkan alert hanya jika jumlah belum diisi
                    const jumlahInput = $(this).closest('.item-row').find('.jumlah');
                    if (!jumlahInput.val()) {
                        showAlert('warning',
                            `⚠️ Stok ${stok} ${satuan} (MIN: ${min} ${satuan})\nRekomendasi beli: ${rekomendasi} ${satuan}`
                        );
                    }
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

            // Form Tambah Pembelian
            $('#formTambah').submit(function(e) {
                e.preventDefault();

                // Validasi minimal satu item
                let hasValidItem = false;
                $('.bahan-baku-select').each(function() {
                    if ($(this).val()) {
                        hasValidItem = true;
                    }
                });

                if (!hasValidItem) {
                    showAlert('error', 'Pilih minimal satu bahan baku');
                    return;
                }

                // Validasi semua item yang dipilih harus lengkap
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
                    showAlert('error', 'Semua item yang dipilih harus memiliki jumlah dan harga');
                    return;
                }

                $.ajax({
                    url: '{{ route('owner.pembelian.store') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('#formTambah button[type="submit"]').prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                    },
                    success: function(response) {
                        $('#modalTambah').modal('hide');
                        showAlert('success', response.success);
                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        showAlert('error', errorMessage);
                    },
                    complete: function() {
                        $('#formTambah button[type="submit"]').prop('disabled', false).html(
                            'Simpan Pembelian');
                    }
                });
            });

            // Detail Pembelian
            $(document).on('click', '.btn-detail', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `{{ url('owner/pembelian') }}/${id}`,
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
                                <p><strong>Kode Pembelian:</strong> ${response.kode_pembelian ?? 'PB-' + response.id}</p>
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
                        showAlert('error', 'Terjadi kesalahan saat memuat detail');
                    }
                });
            });

            // Edit Pembelian
            $(document).on('click', '.btn-edit', function() {
                currentEditId = $(this).data('id');

                $.ajax({
                    url: `{{ url('owner/pembelian') }}/${currentEditId}/edit`,
                    type: 'GET',
                    beforeSend: function() {
                        $('#edit-content').html(
                            '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Memuat data...</p></div>'
                        );
                    },
                    success: function(response) {
                        if (response.error) {
                            showAlert('error', response.error);
                            return;
                        }

                        const pembelian = response.pembelian;
                        let itemsHtml = '';
                        let editItemCounter = 0;

                        pembelian.detail_pembelian.forEach((item, index) => {
                            itemsHtml += `
                            <div class="item-row row mb-2" data-row-index="${index}">
                                <div class="col-md-5">
                                    <select name="items[${index}][bahan_baku_id]" class="form-control bahan-baku-select" required>
                                        <option value="">Pilih Bahan Baku</option>
                                        ${response.bahanBaku.map(bahan => `
                                                            <option value="${bahan.id}" 
                                                                data-harga="${bahan.harga_beli}"
                                                                data-stok="${bahan.stok}"
                                                                data-min="${bahan.min}"
                                                                data-max="${bahan.max}"
                                                                data-satuan="${bahan.satuan}"
                                                                ${item.bahan_baku_id == bahan.id ? 'selected' : ''}>
                                                                ${bahan.nama} 
                                                                ${bahan.stok <= bahan.min ? 
                                                                    `<span class="text-danger">(Stok: ${bahan.stok} ${bahan.satuan} - PERLU BELI!)</span>` : 
                                                                    `(Stok: ${bahan.stok} ${bahan.satuan})`}
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
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                            editItemCounter = index + 1;
                        });

                        $('#edit-content').html(`
                        <input type="hidden" id="edit-id" value="${currentEditId}">
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
                        
                        <div class="form-group">
                            <label class="h5">Total: <span id="edit-total-display" class="text-success">Rp ${parseFloat(pembelian.total).toLocaleString('id-ID')}</span></label>
                        </div>
                    `);

                        // Event untuk menambah item di edit form
                        $('#btn-add-edit-item').off('click').on('click', function() {
                            const newItem = `
                            <div class="item-row row mb-2" data-row-index="${editItemCounter}">
                                <div class="col-md-5">
                                    <select name="items[${editItemCounter}][bahan_baku_id]" class="form-control bahan-baku-select" required>
                                        <option value="">Pilih Bahan Baku</option>
                                        ${response.bahanBaku.map(bahan => `
                                                            <option value="${bahan.id}" 
                                                                data-harga="${bahan.harga_beli}"
                                                                data-stok="${bahan.stok}"
                                                                data-min="${bahan.min}"
                                                                data-max="${bahan.max}"
                                                                data-satuan="${bahan.satuan}">
                                                                ${bahan.nama} 
                                                                ${bahan.stok <= bahan.min ? 
                                                                    `<span class="text-danger">(Stok: ${bahan.stok} ${bahan.satuan} - PERLU BELI!)</span>` : 
                                                                    `(Stok: ${bahan.stok} ${bahan.satuan})`}
                                                            </option>
                                                        `).join('')}
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="items[${editItemCounter}][jumlah]" class="form-control jumlah" placeholder="Jumlah" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="items[${editItemCounter}][harga]" class="form-control harga" placeholder="Harga" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                            $('#edit-items-container').append(newItem);
                            editItemCounter++;
                            updateEditRemoveButtons();
                            attachEditEventListeners();
                        });

                        function updateEditRemoveButtons() {
                            const itemRows = $('#edit-items-container .item-row');
                            itemRows.each(function(index) {
                                const $removeBtn = $(this).find('.btn-remove-item');
                                $removeBtn.prop('disabled', itemRows.length <= 1);
                            });
                        }

                        function attachEditEventListeners() {
                            // Hapus item
                            $(document).off('click', '#edit-items-container .btn-remove-item')
                                .on('click', '#edit-items-container .btn-remove-item',
                                    function() {
                                        if ($('#edit-items-container .item-row').length > 1) {
                                            $(this).closest('.item-row').remove();
                                            calculateEditTotal();
                                            updateEditRemoveButtons();
                                        }
                                    });

                            // Input jumlah/harga
                            $(document).off('input',
                                '#edit-items-container .jumlah, #edit-items-container .harga'
                            ).on('input',
                                '#edit-items-container .jumlah, #edit-items-container .harga',
                                function() {
                                    calculateEditTotal();
                                });

                            // Change bahan baku
                            $(document).off('change',
                                '#edit-items-container .bahan-baku-select').on('change',
                                '#edit-items-container .bahan-baku-select',
                                function() {
                                    const selectedOption = $(this).find('option:selected');
                                    const harga = selectedOption.data('harga');
                                    if (harga) {
                                        $(this).closest('.item-row').find('.harga').val(
                                            harga);
                                    }
                                    calculateEditTotal();
                                });
                        }

                        function calculateEditTotal() {
                            let total = 0;
                            $('#edit-items-container .item-row').each(function() {
                                const jumlah = parseFloat($(this).find('.jumlah')
                                    .val()) || 0;
                                const harga = parseFloat($(this).find('.harga')
                                    .val()) || 0;
                                total += jumlah * harga;
                            });
                            $('#edit-total-display').text('Rp ' + total.toLocaleString(
                                'id-ID'));
                        }

                        updateEditRemoveButtons();
                        attachEditEventListeners();
                        calculateEditTotal();
                        $('#modalEdit').modal('show');
                    },
                    error: function(xhr) {
                        showAlert('error', 'Terjadi kesalahan saat memuat data edit');
                    }
                });
            });

            // Update Pembelian
            $('#formEdit').submit(function(e) {
                e.preventDefault();
                const id = $('#edit-id').val();

                $.ajax({
                    url: `{{ url('owner/pembelian') }}/${id}`,
                    type: 'POST',
                    data: $(this).serialize() + '&_method=PUT',
                    beforeSend: function() {
                        $('#formEdit button[type="submit"]').prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin"></i> Mengupdate...');
                    },
                    success: function(response) {
                        $('#modalEdit').modal('hide');
                        showAlert('success', response.success);
                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        showAlert('error', errorMessage);
                    },
                    complete: function() {
                        $('#formEdit button[type="submit"]').prop('disabled', false).html(
                            'Update Pembelian');
                    }
                });
            });

            // Modal Konfirmasi
            function showConfirmationModal(message, callback) {
                $('#konfirmasi-pesan').text(message);
                $('#modalKonfirmasi').modal('show');

                $('#konfirmasi-ya').off('click').on('click', function() {
                    $('#modalKonfirmasi').modal('hide');
                    callback();
                });
            }

            // Approve Pembelian
            $(document).on('click', '.btn-approve', function() {
                const id = $(this).data('id');
                showConfirmationModal('Apakah Anda yakin ingin menyetujui pembelian ini?', function() {
                    $.ajax({
                        url: `{{ url('owner/pembelian') }}/${id}/approve`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function() {
                            showAlert('info', 'Memproses persetujuan...');
                        },
                        success: function(response) {
                            showAlert('success', response.success);
                            setTimeout(() => location.reload(), 1500);
                        },
                        error: function(xhr) {
                            let errorMessage = 'Terjadi kesalahan';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }
                            showAlert('error', errorMessage);
                        }
                    });
                });
            });

            // Reject Pembelian
            $(document).on('click', '.btn-reject', function() {
                const id = $(this).data('id');
                showConfirmationModal('Apakah Anda yakin ingin menolak pembelian ini?', function() {
                    $.ajax({
                        url: `{{ url('owner/pembelian') }}/${id}/reject`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function() {
                            showAlert('info', 'Memproses penolakan...');
                        },
                        success: function(response) {
                            showAlert('success', response.success);
                            setTimeout(() => location.reload(), 1500);
                        },
                        error: function(xhr) {
                            let errorMessage = 'Terjadi kesalahan';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }
                            showAlert('error', errorMessage);
                        }
                    });
                });
            });

            // Delete Pembelian
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                showConfirmationModal('Apakah Anda yakin ingin menghapus pembelian ini?', function() {
                    $.ajax({
                        url: `{{ url('owner/pembelian') }}/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function() {
                            showAlert('info', 'Memproses penghapusan...');
                        },
                        success: function(response) {
                            showAlert('success', response.success);
                            setTimeout(() => location.reload(), 1500);
                        },
                        error: function(xhr) {
                            let errorMessage = 'Terjadi kesalahan';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }
                            showAlert('error', errorMessage);
                        }
                    });
                });
            });

            // Fungsi untuk menampilkan alert
            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'alert-success' :
                    type === 'error' ? 'alert-danger' :
                    type === 'warning' ? 'alert-warning' : 'alert-info';

                const icon = type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

                const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${icon} mr-2"></i>
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            `;

                // Hapus alert sebelumnya
                $('.alert-dismissible').alert('close');

                // Tambah alert baru
                $('.card').before(alertHtml);

                // Auto close setelah 5 detik
                setTimeout(() => {
                    $('.alert-dismissible').alert('close');
                }, 5000);
            }

            // Inisialisasi awal
            updateRemoveButtons();
            calculateTotal();
            calculateRekomendasiTotal();
            updateSelectedCount();

            // Reset modal saat ditutup
            $('#modalTambah').on('hidden.bs.modal', function() {
                $('#formTambah')[0].reset();
                $('#items-container').html(`
                    <div class="item-row row mb-2">
                        <div class="col-md-5">
                            <select name="items[0][bahan_baku_id]" class="form-control bahan-baku-select" required>
                                <option value="">Pilih Bahan Baku</option>
                                @foreach ($bahanBaku as $bahan)
                                    <option value="{{ $bahan->id }}"
                                        data-harga="{{ $bahan->harga_beli }}"
                                        data-stok="{{ $bahan->stok }}" data-min="{{ $bahan->min }}"
                                        data-max="{{ $bahan->max }}" data-satuan="{{ $bahan->satuan }}">
                                        {{ $bahan->nama }}
                                        @if ($bahan->stok <= $bahan->min)
                                            <span class="text-danger">(Stok: {{ $bahan->stok }}
                                                {{ $bahan->satuan }} - PERLU BELI!)</span>
                                        @else
                                            (Stok: {{ $bahan->stok }} {{ $bahan->satuan }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[0][jumlah]" class="form-control jumlah"
                                placeholder="Jumlah" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="items[0][harga]" class="form-control harga"
                                placeholder="Harga" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-remove-item" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `);
                itemCounter = 1;
                updateRemoveButtons();
                calculateTotal();
            });
        });
    </script>
@endpush
