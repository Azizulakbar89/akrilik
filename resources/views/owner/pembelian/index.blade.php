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
                                <button class="btn btn-success mr-2" id="btn-pembelian-cepat"
                                    title="Buat pembelian dari rekomendasi sistem">
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

                        @if ($rekomendasi->count() > 0)
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5><i class="fas fa-lightbulb"></i> Rekomendasi Pembelian (Sistem Min-Max)</h5>
                                    <div>
                                        <span class="badge badge-primary mr-2">Total: Rp
                                            {{ number_format($totalRekomendasi, 0, ',', '.') }}</span>
                                        <button class="btn btn-sm btn-success" id="btn-gunakan-rekomendasi">
                                            <i class="fas fa-check-circle"></i> Gunakan Rekomendasi
                                        </button>
                                    </div>
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

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="table-pembelian">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Pembelian</th>
                                        <th>Supplier</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th width="180px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pembelian as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->kode_pembelian ?? 'PB-' . str_pad($item->id, 5, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td>{{ $item->supplier->nama }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
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
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-info btn-sm btn-detail"
                                                        data-id="{{ $item->id }}" title="Lihat Detail">
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

    <div class="modal fade" id="modalPrintLaporan">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formPrintLaporan" action="{{ route('owner.pembelian.laporan') }}" method="GET" target="_blank">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="fas fa-print"></i> Print Laporan Pembelian</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Awal <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_awal" class="form-control"
                                        value="{{ date('Y-m-01') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Akhir <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_akhir" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Status Pembelian</label>
                                    <select name="status" class="form-control">
                                        <option value="semua">Semua Status</option>
                                        <option value="completed">Selesai/Disetujui</option>
                                        <option value="menunggu_persetujuan">Menunggu Persetujuan</option>
                                        <option value="ditolak">Ditolak</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong> Laporan akan dicetak dalam format PDF dengan detail transaksi
                            pembelian berdasarkan periode yang dipilih.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-print"></i> Print Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPembelianCepat">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Pembelian Cepat dari Rekomendasi Sistem</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="formPembelianCepat">
                    @csrf
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
                            <label>Items Rekomendasi Pembelian</label>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all-rekomendasi">
                                        <label class="form-check-label" for="select-all-rekomendasi">
                                            Pilih Semua
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge badge-info" id="selected-count">0 item terpilih</span>
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
                                    <tbody id="rekomendasi-items-body">
                                    </tbody>
                                    <tfoot class="table-success">
                                        <tr>
                                            <th colspan="7" class="text-right">Total Pembelian:</th>
                                            <th id="total-rekomendasi">Rp 0</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
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
                                        <strong>Tip:</strong> Anda dapat menggunakan rekomendasi sistem untuk mengisi items.
                                        <button type="button" class="btn btn-sm btn-outline-primary ml-2"
                                            id="btn-use-recommendation">
                                            <i class="fas fa-magic"></i> Gunakan Rekomendasi
                                        </button>
                                    </small>
                                </div>
                            @endif

                            <div id="items-container">
                                <div class="item-row row mb-2">
                                    <div class="col-md-4">
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
                                        <input type="text" class="form-control-plaintext sub-total" value="Rp 0"
                                            readonly>
                                    </div>
                                    <div class="col-md-1">
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
                            <label class="h5">Total Pembelian: <span id="total-display"
                                    class="text-success font-weight-bold">Rp 0</span></label>
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

    <div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pembelian</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="detail-content"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="formEdit">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Pembelian</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="edit-content"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update Pembelian</button>
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
        .btn-group {
            display: flex;
            flex-wrap: nowrap;
        }

        .btn-group .btn-sm {
            margin: 0 2px;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .item-row {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
            align-items: center;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .sub-total {
            font-weight: bold;
            color: #28a745;
        }

        .table-primary th {
            background-color: #007bff;
            color: white;
        }

        .table-success th {
            background-color: #28a745;
            color: white;
        }

        .badge {
            font-size: 12px;
            padding: 5px 10px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let itemCounter = 1;
            let editItemCounter = 0;
            let currentEditId = null;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#btn-pembelian-cepat').click(function() {
                loadRekomendasiData();
                $('#modalPembelianCepat').modal('show');
            });

            function loadRekomendasiData() {
                $.ajax({
                    url: '{{ route('owner.pembelian.get.rekomendasi.data') }}',
                    type: 'GET',
                    beforeSend: function() {
                        $('#rekomendasi-items-body').html(
                            '<tr><td colspan="8" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>'
                        );
                    },
                    success: function(response) {
                        if (response.success) {
                            let html = '';
                            let totalAll = 0;

                            response.rekomendasi.forEach(function(item, index) {
                                totalAll += item.total_nilai;
                                html += `
                            <tr>
                                <td>
                                    <input type="checkbox" name="items[]" value="${item.bahan_baku_id}" 
                                           class="form-check-input item-checkbox" 
                                           data-jumlah="${item.jumlah_rekomendasi}"
                                           data-harga="${item.harga_beli}"
                                           data-total="${item.total_nilai}" checked>
                                </td>
                                <td>${item.nama}</td>
                                <td>${item.stok_sekarang} ${item.satuan}</td>
                                <td>${item.min}</td>
                                <td>${item.max}</td>
                                <td class="text-success font-weight-bold">${item.jumlah_rekomendasi} ${item.satuan}</td>
                                <td>Rp ${parseFloat(item.harga_beli).toLocaleString('id-ID')}</td>
                                <td class="text-primary font-weight-bold">Rp ${parseFloat(item.total_nilai).toLocaleString('id-ID')}</td>
                            </tr>
                        `;
                            });

                            $('#rekomendasi-items-body').html(html);
                            $('#total-rekomendasi').text('Rp ' + totalAll.toLocaleString('id-ID'));
                            $('#selected-count').text(response.rekomendasi.length + ' item terpilih');

                            $('#select-all-rekomendasi').prop('checked', true);

                            attachRekomendasiEvents();
                        }
                    },
                    error: function(xhr) {
                        showAlert('error', 'Gagal memuat data rekomendasi');
                    }
                });
            }

            function attachRekomendasiEvents() {
                $('#select-all-rekomendasi').off('change').on('change', function() {
                    $('.item-checkbox').prop('checked', $(this).prop('checked'));
                    calculateRekomendasiTotal();
                    updateSelectedCount();
                });

                $('.item-checkbox').off('change').on('change', function() {
                    calculateRekomendasiTotal();
                    updateSelectedCount();
                });
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

            function updateSelectedCount() {
                const selectedCount = $('.item-checkbox:checked').length;
                const totalCount = $('.item-checkbox').length;
                $('#selected-count').text(selectedCount + ' dari ' + totalCount + ' item terpilih');
            }

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

                $.ajax({
                    url: '{{ route('owner.pembelian.store.rekomendasi') }}',
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
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        showAlert('error', errorMessage);
                    },
                    complete: function() {
                        $('#formPembelianCepat button[type="submit"]').prop('disabled', false)
                            .html('<i class="fas fa-bolt"></i> Buat Pembelian Cepat');
                    }
                });
            });

            $('#btn-gunakan-rekomendasi').click(function() {
                loadRekomendasiData();
                $('#modalPembelianCepat').modal('show');
            });

            $('#btn-add-item').click(function() {
                const newItem = `
            <div class="item-row row mb-2">
                <div class="col-md-4">
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
                    <input type="text" class="form-control-plaintext sub-total" value="Rp 0" readonly>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
                $('#items-container').append(newItem);
                itemCounter++;
                updateRemoveButtons();
                attachItemEvents();
            });

            $(document).on('click', '.btn-remove-item', function() {
                if ($('.item-row').length > 1) {
                    $(this).closest('.item-row').remove();
                    calculateTotal();
                    updateRemoveButtons();
                }
            });

            function updateRemoveButtons() {
                const itemRows = $('.item-row');
                itemRows.each(function(index) {
                    const $removeBtn = $(this).find('.btn-remove-item');
                    $removeBtn.prop('disabled', itemRows.length <= 1);
                });
            }

            function attachItemEvents() {
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
                    }

                    calculateItemSubTotal($(this).closest('.item-row'));
                    calculateTotal();
                });

                $(document).on('input', '.jumlah, .harga', function() {
                    calculateItemSubTotal($(this).closest('.item-row'));
                    calculateTotal();
                });
            }

            function calculateItemSubTotal(row) {
                const jumlah = parseFloat(row.find('.jumlah').val()) || 0;
                const harga = parseFloat(row.find('.harga').val()) || 0;
                const subTotal = jumlah * harga;
                row.find('.sub-total').val('Rp ' + subTotal.toLocaleString('id-ID'));
            }

            function calculateTotal() {
                let total = 0;
                $('.item-row').each(function() {
                    const jumlah = parseFloat($(this).find('.jumlah').val()) || 0;
                    const harga = parseFloat($(this).find('.harga').val()) || 0;
                    total += jumlah * harga;
                });
                $('#total-display').text('Rp ' + total.toLocaleString('id-ID'));
            }

            $('#btn-use-recommendation').click(function() {
                $.ajax({
                    url: '{{ route('owner.pembelian.get.rekomendasi.data') }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.rekomendasi.length > 0) {
                            $('#items-container').empty();
                            itemCounter = 0;

                            response.rekomendasi.forEach(function(item) {
                                const newItem = `
                            <div class="item-row row mb-2">
                                <div class="col-md-4">
                                    <select name="items[${itemCounter}][bahan_baku_id]" class="form-control bahan-baku-select" required>
                                        <option value="">Pilih Bahan Baku</option>
                                        @foreach ($bahanBaku as $bahan)
                                        <option value="{{ $bahan->id }}" 
                                            data-harga="{{ $bahan->harga_beli }}"
                                            data-stok="{{ $bahan->stok }}" 
                                            data-min="{{ $bahan->min }}"
                                            data-max="{{ $bahan->max }}" 
                                            data-satuan="{{ $bahan->satuan }}"
                                            ${item.bahan_baku_id == {{ $bahan->id }} ? 'selected' : ''}>
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
                                        value="${item.jumlah_rekomendasi}" placeholder="Jumlah" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="items[${itemCounter}][harga]" class="form-control harga" 
                                        value="${item.harga_beli}" placeholder="Harga" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control-plaintext sub-total" 
                                        value="Rp ${parseFloat(item.total_nilai).toLocaleString('id-ID')}" readonly>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                                $('#items-container').append(newItem);
                                itemCounter++;
                            });

                            updateRemoveButtons();
                            calculateTotal();
                            showAlert('success', 'Rekomendasi sistem telah diterapkan!');
                        }
                    },
                    error: function() {
                        showAlert('error', 'Gagal memuat rekomendasi');
                    }
                });
            });

            $('#formTambah').submit(function(e) {
                e.preventDefault();

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
                            <td>Rp ${parseFloat(item.harga).toLocaleString('id-ID')}</td>
                            <td>Rp ${parseFloat(item.sub_total).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                        });

                        const tanggal = new Date(response.tanggal);
                        const formattedDate = tanggal.toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric'
                        });

                        $('#detail-content').html(`
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Kode Pembelian:</strong> ${response.kode_pembelian || 'PB-' + response.id.toString().padStart(5, '0')}</p>
                            <p><strong>Supplier:</strong> ${response.supplier.nama}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tanggal:</strong> ${formattedDate}</p>
                            <p><strong>Status:</strong> <span class="badge ${getStatusBadgeClass(response.status)}">${getStatusText(response.status)}</span></p>
                        </div>
                    </div>
                    <div class="table-responsive">
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
                    error: function() {
                        showAlert('error', 'Gagal memuat detail pembelian');
                    }
                });
            });

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
                        editItemCounter = 0;

                        pembelian.detail_pembelian.forEach((item, index) => {
                            itemsHtml += `
                        <div class="item-row row mb-2" data-row-index="${index}">
                            <div class="col-md-4">
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
                                <input type="text" class="form-control-plaintext sub-total" 
                                    value="Rp ${parseFloat(item.sub_total).toLocaleString('id-ID')}" readonly>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-remove-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                            editItemCounter = index + 1;
                        });

                        $('#edit-content').html(`
                    <input type="hidden" name="_method" value="PUT">
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
                        <label class="h5">Total: <span id="edit-total-display" class="text-success font-weight-bold">Rp ${parseFloat(pembelian.total).toLocaleString('id-ID')}</span></label>
                    </div>
                `);

                        setupEditFormEvents();
                        updateEditRemoveButtons();
                        $('#modalEdit').modal('show');
                    },
                    error: function() {
                        showAlert('error', 'Gagal memuat data edit');
                    }
                });
            });

            function setupEditFormEvents() {
                $('#btn-add-edit-item').off('click').on('click', function() {
                    const newItem = `
                <div class="item-row row mb-2" data-row-index="${editItemCounter}">
                    <div class="col-md-4">
                        <select name="items[${editItemCounter}][bahan_baku_id]" class="form-control bahan-baku-select" required>
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
                        <input type="number" name="items[${editItemCounter}][jumlah]" class="form-control jumlah" placeholder="Jumlah" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="items[${editItemCounter}][harga]" class="form-control harga" placeholder="Harga" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control-plaintext sub-total" value="Rp 0" readonly>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-remove-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
                    $('#edit-items-container').append(newItem);
                    editItemCounter++;
                    updateEditRemoveButtons();
                });

                $(document).off('click', '#edit-items-container .btn-remove-item').on('click',
                    '#edit-items-container .btn-remove-item',
                    function() {
                        if ($('#edit-items-container .item-row').length > 1) {
                            $(this).closest('.item-row').remove();
                            calculateEditTotal();
                            updateEditRemoveButtons();
                        }
                    });

                $(document).off('change', '#edit-items-container .bahan-baku-select').on('change',
                    '#edit-items-container .bahan-baku-select',
                    function() {
                        const selectedOption = $(this).find('option:selected');
                        const harga = selectedOption.data('harga');
                        if (harga) {
                            $(this).closest('.item-row').find('.harga').val(harga);
                        }
                        calculateEditItemSubTotal($(this).closest('.item-row'));
                        calculateEditTotal();
                    });

                $(document).off('input', '#edit-items-container .jumlah, #edit-items-container .harga').on('input',
                    '#edit-items-container .jumlah, #edit-items-container .harga',
                    function() {
                        calculateEditItemSubTotal($(this).closest('.item-row'));
                        calculateEditTotal();
                    });

                $('#formEdit').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    const id = $('#edit-id').val();

                    $.ajax({
                        url: `{{ url('owner/pembelian') }}/${id}`,
                        type: 'POST',
                        data: $(this).serialize(),
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
            }

            function updateEditRemoveButtons() {
                const itemRows = $('#edit-items-container .item-row');
                itemRows.each(function() {
                    const $removeBtn = $(this).find('.btn-remove-item');
                    $removeBtn.prop('disabled', itemRows.length <= 1);
                });
            }

            function calculateEditItemSubTotal(row) {
                const jumlah = parseFloat(row.find('.jumlah').val()) || 0;
                const harga = parseFloat(row.find('.harga').val()) || 0;
                const subTotal = jumlah * harga;
                row.find('.sub-total').val('Rp ' + subTotal.toLocaleString('id-ID'));
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

            function showConfirmationModal(message, callback) {
                $('#konfirmasi-pesan').text(message);
                $('#modalKonfirmasi').modal('show');

                $('#konfirmasi-ya').off('click').on('click', function() {
                    $('#modalKonfirmasi').modal('hide');
                    callback();
                });
            }

            $(document).on('click', '.btn-approve', function() {
                const id = $(this).data('id');
                showConfirmationModal('Apakah Anda yakin ingin menyetujui pembelian ini?', function() {
                    $.ajax({
                        url: `{{ url('owner/pembelian') }}/${id}/approve`,
                        type: 'POST',
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

            $(document).on('click', '.btn-reject', function() {
                const id = $(this).data('id');
                showConfirmationModal('Apakah Anda yakin ingin menolak pembelian ini?', function() {
                    $.ajax({
                        url: `{{ url('owner/pembelian') }}/${id}/reject`,
                        type: 'POST',
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

            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                showConfirmationModal('Apakah Anda yakin ingin menghapus pembelian ini?', function() {
                    $.ajax({
                        url: `{{ url('owner/pembelian') }}/${id}`,
                        type: 'DELETE',
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

            function getStatusBadgeClass(status) {
                switch (status) {
                    case 'menunggu_persetujuan':
                        return 'badge-warning';
                    case 'completed':
                        return 'badge-success';
                    case 'ditolak':
                        return 'badge-danger';
                    default:
                        return 'badge-secondary';
                }
            }

            function getStatusText(status) {
                switch (status) {
                    case 'menunggu_persetujuan':
                        return 'Menunggu Persetujuan';
                    case 'completed':
                        return 'Selesai';
                    case 'ditolak':
                        return 'Ditolak';
                    default:
                        return status;
                }
            }

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

                $('.alert-dismissible').alert('close');

                $('.card').before(alertHtml);

                setTimeout(() => {
                    $('.alert-dismissible').alert('close');
                }, 5000);
            }

            updateRemoveButtons();
            attachItemEvents();
            calculateTotal();

            $('#modalTambah').on('hidden.bs.modal', function() {
                $('#formTambah')[0].reset();
                $('#items-container').html(`
            <div class="item-row row mb-2">
                <div class="col-md-4">
                    <select name="items[0][bahan_baku_id]" class="form-control bahan-baku-select" required>
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
                    <input type="number" name="items[0][jumlah]" class="form-control jumlah" placeholder="Jumlah" min="1" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="items[0][harga]" class="form-control harga" placeholder="Harga" step="0.01" min="0" required>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control-plaintext sub-total" value="Rp 0" readonly>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-remove-item" disabled>
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `);
                itemCounter = 1;
                updateRemoveButtons();
                attachItemEvents();
                calculateTotal();
            });

            $('#modalPembelianCepat').on('hidden.bs.modal', function() {
                $('#formPembelianCepat')[0].reset();
                $('#select-all-rekomendasi').prop('checked', false);
            });
        });
    </script>
@endpush
