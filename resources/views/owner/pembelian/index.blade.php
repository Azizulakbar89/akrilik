@extends('layoutsAPP.deskapp')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Pembelian (Owner)</h3>
                        <div class="float-right">
                            @if ($rekomendasi->count() > 0 || $stokTidakAman->count() > 0)
                                <button class="btn btn-primary mr-2" id="btn-pembelian-cepat"
                                    title="Buat pembelian cepat untuk bahan baku yang perlu dibeli">
                                    <i class="fas fa-shopping-cart"></i> Pembelian Cepat ROP
                                </button>
                            @endif

                            <button class="btn btn-info mr-2" data-toggle="modal" data-target="#modalTambah">
                                <i class="fas fa-plus"></i> Tambah Pembelian
                            </button>

                            <button class="btn btn-warning" data-toggle="modal" data-target="#modalPrintLaporan">
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
                                                    $rop = $bahan->rop ?? 0;
                                                    // Hitung rekomendasi beli langsung
                                                    $rekomendasiBeli = 0;
                                                    if ($bahan->rop > 0) {
                                                        $rekomendasiBeli = $bahan->rop;
                                                    } elseif ($bahan->max > 0) {
                                                        $rekomendasiBeli = max(1, $bahan->max - $bahan->stok);
                                                    }

                                                    $status = '';
                                                    if ($bahan->stok <= $bahan->min) {
                                                        $status = '<span class="badge badge-danger">KRITIS</span>';
                                                    } elseif ($bahan->stok <= $bahan->safety_stock) {
                                                        $status = '<span class="badge badge-warning">TIDAK AMAN</span>';
                                                    } else {
                                                        $status = '<span class="badge badge-success">AMAN</span>';
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>{{ $bahan->nama }}</td>
                                                    <td>{{ $bahan->stok }} {{ $bahan->satuan }}</td>
                                                    <td>{{ $bahan->safety_stock }} {{ $bahan->satuan }}</td>
                                                    <td>{{ $bahan->min }} {{ $bahan->satuan }}</td>
                                                    <td class="text-primary"><strong>{{ $rop }}
                                                            {{ $bahan->satuan }}</strong></td>
                                                    <td class="text-success"><strong>{{ $rekomendasiBeli }}
                                                            {{ $bahan->satuan }}</strong></td>
                                                    <td>{!! $status !!}</td>
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
                                    <div>
                                        <span class="badge badge-primary mr-2">Total: Rp
                                            {{ number_format($totalRekomendasi, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <p>Bahan baku berikut perlu dilakukan pembelian berdasarkan sistem ROP:</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Bahan Baku</th>
                                                <th>Stok Saat Ini</th>
                                                <th>Min</th>
                                                <th>Max</th>
                                                <th>ROP</th>
                                                <th>Rekomendasi Beli</th>
                                                <th>Harga Beli</th>
                                                <th>Sub Total</th>
                                                <th>Satuan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($rekomendasi as $item)
                                                @php
                                                    $subTotal = $item['jumlah_rekomendasi'] * $item['harga_beli'];
                                                @endphp
                                                <tr>
                                                    <td>{{ $item['nama'] }}</td>
                                                    <td>{{ $item['stok_sekarang'] }}</td>
                                                    <td>{{ $item['min'] }}</td>
                                                    <td>{{ $item['max'] }}</td>
                                                    <td><span class="badge badge-info">{{ $item['rop'] }}</span></td>
                                                    <td><strong
                                                            class="text-success">{{ $item['jumlah_rekomendasi'] }}</strong>
                                                    </td>
                                                    <td>Rp {{ number_format($item['harga_beli'], 0, ',', '.') }}</td>
                                                    <td>Rp {{ number_format($subTotal, 0, ',', '.') }}</td>
                                                    <td>{{ $item['satuan'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-primary">
                                            <tr>
                                                <th colspan="7" class="text-right">Total Rekomendasi:</th>
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
                                        <th>Tanggal Pesan</th>
                                        <th>Tanggal Terima</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th width="250px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pembelian as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->kode_pembelian ?? 'PB-' . str_pad($item->id, 5, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td>{{ $item->supplier->nama }}</td>
                                            <td>
                                                <div>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</div>
                                                <small
                                                    class="text-muted">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                @if ($item->waktu_penerimaan)
                                                    <div>
                                                        {{ \Carbon\Carbon::parse($item->waktu_penerimaan)->format('d/m/Y') }}
                                                    </div>
                                                    <small
                                                        class="text-muted">{{ \Carbon\Carbon::parse($item->waktu_penerimaan)->format('H:i') }}</small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                            <td>
                                                @if ($item->status == 'menunggu_persetujuan')
                                                    <span class="badge badge-warning">Menunggu Persetujuan</span>
                                                @elseif($item->status == 'completed')
                                                    <span class="badge badge-success">Disetujui</span>
                                                @elseif($item->status == 'diterima')
                                                    <span class="badge badge-info">Diterima</span>
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

                                                    @if ($item->status == 'completed')
                                                        <button class="btn btn-primary btn-sm btn-receive"
                                                            data-id="{{ $item->id }}" title="Terima Pembelian">
                                                            <i class="fas fa-truck"></i>
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

    <!-- Modal Pembelian Cepat ROP -->
    <div class="modal fade" id="modalPembelianCepat">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title"><i class="fas fa-shopping-cart"></i> Pembelian Cepat ROP</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form id="formPembelianCepat" action="{{ route('owner.pembelian.store.pembelian-cepat') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div id="pembelian-cepat-alert"></div>

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
                            <label>Items Pembelian</label>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all-rekomendasi"
                                            checked>
                                        <label class="form-check-label" for="select-all-rekomendasi">
                                            Pilih Semua
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge badge-info" id="selected-count">0 item terpilih</span>
                                    <span class="badge badge-danger ml-2" id="total-items">Total: 0 item</span>
                                </div>
                            </div>

                            <div id="pembelian-cepat-no-data" class="alert alert-warning" style="display: none;">
                                <i class="fas fa-info-circle"></i> Tidak ada bahan baku yang perlu dibeli. Semua stok dalam
                                kondisi aman.
                            </div>

                            <div id="pembelian-cepat-table" class="table-responsive" style="display: none;">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-primary">
                                        <tr>
                                            <th width="50px">Pilih</th>
                                            <th>Bahan Baku</th>
                                            <th>Stok Sekarang</th>
                                            <th>Safety Stock</th>
                                            <th>Min</th>
                                            <th>Max</th>
                                            <th>ROP</th>
                                            <th>Jumlah Beli (ROP)</th>
                                            <th>Harga Beli</th>
                                            <th>Sub Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pembelian-cepat-items-body">
                                        <!-- Data akan dimuat via AJAX -->
                                    </tbody>
                                    <tfoot class="table-success">
                                        <tr>
                                            <th colspan="9" class="text-right">Total Pembelian:</th>
                                            <th colspan="2" id="total-pembelian-cepat">Rp 0</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong> Pembelian ini akan dibuat dengan status "menunggu_persetujuan".
                            Anda perlu menyetujui pembelian sebelum menerima barang.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-submit-pembelian-cepat" disabled>
                            <i class="fas fa-shopping-cart"></i> Buat Pembelian Cepat
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
                <form id="formTambah" action="{{ route('owner.pembelian.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-info text-white">
                        <h4 class="modal-title"><i class="fas fa-plus"></i> Tambah Pembelian Baru</h4>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
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
                                        <strong>Tip:</strong> Anda dapat menggunakan rekomendasi sistem ROP untuk mengisi
                                        items.
                                        <button type="button" class="btn btn-sm btn-outline-primary ml-2"
                                            id="btn-use-recommendation">
                                            <i class="fas fa-magic"></i> Gunakan Rekomendasi ROP
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
                                                @php
                                                    $rop = $bahan->rop ?? 0;
                                                    // Hitung rekomendasi langsung
                                                    $rekomendasiBeli = 0;
                                                    if ($bahan->rop > 0) {
                                                        $rekomendasiBeli = $bahan->rop;
                                                    } elseif ($bahan->max > 0) {
                                                        $rekomendasiBeli = max(1, $bahan->max - $bahan->stok);
                                                    }
                                                @endphp
                                                <option value="{{ $bahan->id }}"
                                                    data-harga="{{ $bahan->harga_beli }}"
                                                    data-stok="{{ $bahan->stok }}" data-min="{{ $bahan->min }}"
                                                    data-max="{{ $bahan->max }}" data-rop="{{ $rop }}"
                                                    data-rekomendasi="{{ $rekomendasiBeli }}"
                                                    data-satuan="{{ $bahan->satuan }}">
                                                    {{ $bahan->nama }}
                                                    @if ($bahan->stok <= $bahan->min)
                                                        <span class="text-danger">(Stok: {{ $bahan->stok }} - Min:
                                                            {{ $bahan->min }})</span>
                                                    @else
                                                        (Stok: {{ $bahan->stok }}, Min: {{ $bahan->min }})
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
                        <button type="submit" class="btn btn-info">Simpan Pembelian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetail">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-eye"></i> Detail Pembelian</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
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

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="formEdit" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Pembelian</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="edit-content"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Update Pembelian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi -->
    <div class="modal fade" id="modalKonfirmasi">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Konfirmasi</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p id="konfirmasi-pesan"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" id="konfirmasi-ya">Ya</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Print Laporan -->
    <div class="modal fade" id="modalPrintLaporan">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formPrintLaporan" action="{{ route('owner.pembelian.laporan') }}" method="GET"
                    target="_blank">
                    <div class="modal-header bg-warning text-white">
                        <h4 class="modal-title"><i class="fas fa-print"></i> Print Laporan Pembelian</h4>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
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
                                        <option value="diterima">Diterima</option>
                                        <option value="menunggu_persetujuan">Menunggu Persetujuan</option>
                                        <option value="ditolak">Ditolak</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Print Laporan</button>
                    </div>
                </form>
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

        .rop-badge {
            background-color: #17a2b8;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: normal;
        }

        .alert .table {
            margin-bottom: 0;
        }

        .form-control-plaintext {
            background-color: transparent;
            border: none;
            font-weight: bold;
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .stok-kritis {
            background-color: #fff3cd !important;
        }

        .stok-peringatan {
            background-color: #f8d7da !important;
        }

        .stok-aman {
            background-color: #d4edda !important;
        }

        .stok-kritis td {
            font-weight: bold;
            color: #856404 !important;
        }

        .stok-peringatan td {
            font-weight: bold;
            color: #721c24 !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let itemCounter = 1;
            let currentEditId = null;

            // Setup CSRF token untuk semua AJAX request
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ========== FUNGSI PEMBELIAN CEPAT ==========
            $('#btn-pembelian-cepat').click(function() {
                loadDataPembelianCepat();
                $('#modalPembelianCepat').modal('show');
            });

            function loadDataPembelianCepat() {
                showLoading();

                // Reset state
                $('#pembelian-cepat-no-data').hide();
                $('#pembelian-cepat-table').hide();
                $('#btn-submit-pembelian-cepat').prop('disabled', true);
                $('#pembelian-cepat-alert').html('');

                $.ajax({
                    url: '{{ route('owner.pembelian.get.pembelian-cepat-data') }}',
                    type: 'GET',
                    success: function(response) {
                        hideLoading();

                        if (response.success) {
                            if (response.all_safe || response.total_items === 0) {
                                // Tidak ada bahan baku yang perlu dibeli
                                $('#pembelian-cepat-no-data').show();
                                $('#pembelian-cepat-table').hide();
                                $('#btn-submit-pembelian-cepat').prop('disabled', true);

                                $('#pembelian-cepat-alert').html(`
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> ${response.message}
                                    </div>
                                `);
                            } else {
                                // Ada bahan baku yang perlu dibeli
                                renderDataPembelianCepat(response.data, response.total_nilai);
                                $('#pembelian-cepat-no-data').hide();
                                $('#pembelian-cepat-table').show();
                                $('#btn-submit-pembelian-cepat').prop('disabled', false);

                                $('#pembelian-cepat-alert').html(`
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> ${response.message}
                                    </div>
                                `);
                            }
                        } else {
                            $('#pembelian-cepat-alert').html(`
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i> ${response.message || 'Gagal memuat data pembelian cepat'}
                                </div>
                            `);
                        }
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errorMessage = 'Terjadi kesalahan saat memuat data pembelian cepat';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        $('#pembelian-cepat-alert').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> ${errorMessage}
                            </div>
                        `);
                    }
                });
            }

            function renderDataPembelianCepat(data, totalNilai) {
                let html = '';
                let totalAll = 0;
                let totalItems = 0;
                let itemIndex = 0;

                data.forEach(function(item) {
                    const jumlahBeli = item.jumlah_rekomendasi || 1;
                    const subTotal = jumlahBeli * item.harga_beli;
                    totalAll += subTotal;
                    totalItems++;

                    const rowClass = item.status_stok === 'Kritis' ? 'stok-kritis' :
                        item.status_stok === 'Tidak Aman' ? 'stok-peringatan' : '';

                    const statusLabel = item.status_stok === 'Kritis' ?
                        '<span class="badge badge-danger">KRITIS</span>' :
                        item.status_stok === 'Tidak Aman' ?
                        '<span class="badge badge-warning">TIDAK AMAN</span>' :
                        '<span class="badge badge-success">AMAN</span>';

                    html += `
                        <tr class="${rowClass}">
                            <td>
                                <input type="checkbox" name="items[${itemIndex}][bahan_baku_id]" 
                                       value="${item.bahan_baku_id}"
                                       class="form-check-input item-checkbox" 
                                       data-jumlah="${jumlahBeli}"
                                       data-harga="${item.harga_beli}"
                                       data-total="${subTotal}" checked>
                            </td>
                            <td>
                                <strong>${item.nama}</strong>
                                <div class="small text-muted">ID: ${item.bahan_baku_id}</div>
                                ${statusLabel}
                            </td>
                            <td class="${item.stok <= item.min ? 'text-danger font-weight-bold' : ''}">
                                ${item.stok} ${item.satuan}
                            </td>
                            <td>${item.safety_stock || 0} ${item.satuan}</td>
                            <td>${item.min} ${item.satuan}</td>
                            <td>${item.max || 0} ${item.satuan}</td>
                            <td><span class="badge badge-info">${item.rop || 0} ${item.satuan}</span></td>
                            <td class="text-success font-weight-bold">
                                <input type="number" 
                                       name="items[${itemIndex}][jumlah]" 
                                       value="${jumlahBeli}" 
                                       class="form-control form-control-sm jumlah-input"
                                       min="1" 
                                       data-bahan-id="${item.bahan_baku_id}"
                                       style="width: 80px; display: inline-block;">
                                ${item.satuan}
                            </td>
                            <td>
                                <input type="number"
                                       name="items[${itemIndex}][harga]"
                                       value="${item.harga_beli}"
                                       class="form-control form-control-sm harga-input"
                                       min="0"
                                       step="0.01"
                                       data-bahan-id="${item.bahan_baku_id}"
                                       style="width: 120px; display: inline-block;">
                            </td>
                            <td class="text-primary font-weight-bold sub-total-display" data-subtotal="${subTotal}">
                                Rp ${parseFloat(subTotal).toLocaleString('id-ID')}
                            </td>
                            <td>${item.status_stok}</td>
                        </tr>
                    `;

                    // Tambah input hidden untuk data tambahan
                    html += `
                        <input type="hidden" name="items[${itemIndex}][is_checked]" value="1">
                    `;

                    itemIndex++;
                });

                $('#pembelian-cepat-items-body').html(html);
                $('#total-pembelian-cepat').text('Rp ' + totalAll.toLocaleString('id-ID'));
                $('#selected-count').text(totalItems + ' item terpilih');
                $('#total-items').text('Total: ' + totalItems + ' item');

                $('#select-all-rekomendasi').prop('checked', true);
                attachPembelianCepatEvents();
            }

            function attachPembelianCepatEvents() {
                // Select all checkbox
                $('#select-all-rekomendasi').off('change').on('change', function() {
                    $('.item-checkbox').prop('checked', $(this).prop('checked'));
                    calculatePembelianCepatTotal();
                    updateSelectedCountPembelianCepat();
                });

                // Item checkbox
                $('.item-checkbox').off('change').on('change', function() {
                    calculatePembelianCepatTotal();
                    updateSelectedCountPembelianCepat();
                });

                // Jumlah input change
                $(document).on('input', '.jumlah-input', function() {
                    updatePembelianCepatSubTotal($(this));
                });

                // Harga input change
                $(document).on('input', '.harga-input', function() {
                    updatePembelianCepatSubTotal($(this));
                });
            }

            function updatePembelianCepatSubTotal($input) {
                const $row = $input.closest('tr');
                const jumlah = $row.find('.jumlah-input').val();
                const harga = $row.find('.harga-input').val();
                const subTotal = (parseFloat(jumlah) || 0) * (parseFloat(harga) || 0);

                // Update data attribute
                $row.find('.item-checkbox').data('total', subTotal);
                $row.find('.item-checkbox').data('jumlah', jumlah);
                $row.find('.item-checkbox').data('harga', harga);

                // Update display
                $row.find('.sub-total-display').text('Rp ' + subTotal.toLocaleString('id-ID'));
                $row.find('.sub-total-display').data('subtotal', subTotal);

                calculatePembelianCepatTotal();
            }

            function calculatePembelianCepatTotal() {
                let total = 0;
                let selectedCount = 0;

                $('.item-checkbox:checked').each(function() {
                    const $row = $(this).closest('tr');
                    const subTotalDisplay = $row.find('.sub-total-display');
                    const subTotal = parseFloat(subTotalDisplay.data('subtotal')) || 0;

                    total += subTotal;
                    selectedCount++;
                });

                $('#total-pembelian-cepat').text('Rp ' + total.toLocaleString('id-ID'));
                $('#selected-count').text(selectedCount + ' item terpilih');

                $('#btn-submit-pembelian-cepat').prop('disabled', selectedCount === 0);
            }

            function updateSelectedCountPembelianCepat() {
                const selectedCount = $('.item-checkbox:checked').length;
                const totalCount = $('.item-checkbox').length;
                $('#selected-count').text(selectedCount + ' dari ' + totalCount + ' item terpilih');
            }

            // Form pembelian cepat
            $('#formPembelianCepat').submit(function(e) {
                e.preventDefault();

                // Validasi supplier
                if (!$('select[name="supplier_id"]').val()) {
                    showAlert('error', 'Pilih supplier terlebih dahulu');
                    return;
                }

                // Validasi tanggal
                if (!$('input[name="tanggal"]').val()) {
                    showAlert('error', 'Pilih tanggal pembelian');
                    return;
                }

                // Kumpulkan data items yang dipilih
                const items = [];
                let itemIndex = 0;

                $('.item-checkbox:checked').each(function() {
                    const $row = $(this).closest('tr');
                    const bahanBakuId = $(this).val();
                    const jumlah = $row.find('.jumlah-input').val();
                    const harga = $row.find('.harga-input').val();

                    if (bahanBakuId && jumlah && harga) {
                        items.push({
                            bahan_baku_id: bahanBakuId,
                            jumlah: parseInt(jumlah) || 1,
                            harga: parseFloat(harga) || 0
                        });
                    }
                });

                if (items.length === 0) {
                    showAlert('error', 'Pilih minimal satu item untuk dibeli');
                    return;
                }

                // Buat FormData
                const formData = new FormData(this);

                // Hapus items yang lama
                formData.delete('items');

                // Tambah items yang baru
                items.forEach((item, index) => {
                    formData.append(`items[${index}][bahan_baku_id]`, item.bahan_baku_id);
                    formData.append(`items[${index}][jumlah]`, item.jumlah);
                    formData.append(`items[${index}][harga]`, item.harga);
                });

                showLoading();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        hideLoading();

                        if (response.success) {
                            $('#modalPembelianCepat').modal('hide');
                            showAlert('success', response.success);

                            if (response.total_items > 0) {
                                showAlert('info',
                                    `${response.total_items} bahan baku berhasil ditambahkan ke pembelian. Total: Rp ${parseFloat(response.total_pembelian).toLocaleString('id-ID')}`
                                );
                            }

                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            showAlert('error', response.error ||
                                'Terjadi kesalahan saat membuat pembelian cepat');
                        }
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errorMessage = 'Terjadi kesalahan saat membuat pembelian cepat';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        showAlert('error', errorMessage);
                    }
                });
            });

            // Reset modal pembelian cepat when closed
            $('#modalPembelianCepat').on('hidden.bs.modal', function() {
                $('#formPembelianCepat')[0].reset();
                $('#select-all-rekomendasi').prop('checked', false);
                $('#pembelian-cepat-items-body').empty();
                $('#pembelian-cepat-no-data').hide();
                $('#pembelian-cepat-table').hide();
                $('#pembelian-cepat-alert').html('');
                $('#btn-submit-pembelian-cepat').prop('disabled', true);
            });

            // ========== FUNGSI REKOMENDASI ROP UNTUK FORM TAMBAH ==========
            $('#btn-use-recommendation').click(function() {
                showLoading();

                $.ajax({
                    url: '{{ route('owner.pembelian.get.rekomendasi-data') }}',
                    type: 'GET',
                    success: function(response) {
                        hideLoading();

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
                                                    @php
                                                        $ropBahan = $bahan->rop ?? 0;
                                                        $rekomendasiBeli = 0;
                                                        if ($bahan->rop > 0) {
                                                            $rekomendasiBeli = $bahan->rop;
                                                        } elseif ($bahan->max > 0) {
                                                            $rekomendasiBeli = max(1, $bahan->max - $bahan->stok);
                                                        }
                                                    @endphp
                                                    <option value="{{ $bahan->id }}" 
                                                        data-harga="{{ $bahan->harga_beli }}"
                                                        data-stok="{{ $bahan->stok }}" 
                                                        data-min="{{ $bahan->min }}"
                                                        data-max="{{ $bahan->max }}" 
                                                        data-rop="{{ $ropBahan }}"
                                                        data-rekomendasi="{{ $rekomendasiBeli }}"
                                                        data-satuan="{{ $bahan->satuan }}"
                                                        ${item.bahan_baku_id == {{ $bahan->id }} ? 'selected' : ''}>
                                                        {{ $bahan->nama }}
                                                        @if ($bahan->stok <= $bahan->min)
                                                            <span class="text-danger">(Stok: {{ $bahan->stok }} - Min: {{ $bahan->min }})</span>
                                                        @else
                                                            (Stok: {{ $bahan->stok }}, Min: {{ $bahan->min }})
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
                            showAlert('success', 'Rekomendasi sistem ROP telah diterapkan!');
                        } else {
                            showAlert('info', response.message ||
                                'Tidak ada rekomendasi ROP yang tersedia');
                        }
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errorMessage = 'Gagal memuat rekomendasi ROP';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showAlert('error', errorMessage);
                    }
                });
            });

            // ========== FUNGSI AKSI PEMBELIAN ==========
            $(document).on('click', '.btn-detail', function() {
                const id = $(this).data('id');
                showDetailModal(id);
            });

            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                showEditModal(id);
            });

            $(document).on('click', '.btn-approve', function() {
                const id = $(this).data('id');
                showConfirmationModal(
                    'Apakah Anda yakin ingin menyetujui pembelian ini?',
                    function() {
                        approvePembelian(id);
                    }
                );
            });

            $(document).on('click', '.btn-reject', function() {
                const id = $(this).data('id');
                showConfirmationModal(
                    'Apakah Anda yakin ingin menolak pembelian ini?',
                    function() {
                        rejectPembelian(id);
                    }
                );
            });

            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                showConfirmationModal(
                    'Apakah Anda yakin ingin menghapus pembelian ini?<br><small>Data yang dihapus tidak dapat dikembalikan.</small>',
                    function() {
                        deletePembelian(id);
                    }
                );
            });

            $(document).on('click', '.btn-receive', function() {
                const id = $(this).data('id');
                showConfirmationModal(
                    'Apakah Anda yakin ingin menerima pembelian ini?<br><small>Stok bahan baku akan ditambahkan.</small>',
                    function() {
                        receivePembelian(id);
                    }
                );
            });

            function showDetailModal(id) {
                showLoading();

                $.ajax({
                    url: `/owner/pembelian/${id}`,
                    type: 'GET',
                    success: function(response) {
                        hideLoading();

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

                        const createdAt = new Date(response.created_at);
                        const formattedCreated = createdAt.toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        let waktuPenerimaanHtml = '-';
                        if (response.waktu_penerimaan) {
                            const waktuPenerimaan = new Date(response.waktu_penerimaan);
                            waktuPenerimaanHtml = waktuPenerimaan.toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        }

                        const detailHtml = `
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p><strong>Kode Pembelian:</strong> ${response.kode_pembelian || 'PB-' + response.id.toString().padStart(5, '0')}</p>
                                    <p><strong>Supplier:</strong> ${response.supplier.nama}</p>
                                    <p><strong>Tanggal Pesan:</strong> ${formattedCreated}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Waktu Penerimaan:</strong> ${waktuPenerimaanHtml}</p>
                                    <p><strong>Status:</strong> <span class="badge ${getStatusBadgeClass(response.status)}">${getStatusText(response.status)}</span></p>
                                    <p><strong>Total Pembelian:</strong> Rp ${parseFloat(response.total).toLocaleString('id-ID')}</p>
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
                        `;

                        $('#detail-content').html(detailHtml);
                        $('#modalDetail').modal('show');
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errorMessage = 'Gagal memuat detail pembelian';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showAlert('error', errorMessage);
                    }
                });
            }

            function showEditModal(id) {
                showLoading();

                $.ajax({
                    url: `/owner/pembelian/${id}/edit`,
                    type: 'GET',
                    success: function(response) {
                        hideLoading();

                        if (response.error) {
                            showAlert('error', response.error);
                            return;
                        }

                        currentEditId = id;
                        renderEditForm(response);

                        // Set action URL untuk form edit
                        $('#formEdit').attr('action', `/owner/pembelian/${id}`);

                        $('#modalEdit').modal('show');
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errorMessage = 'Gagal memuat data pembelian untuk edit';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        showAlert('error', errorMessage);
                    }
                });
            }

            function renderEditForm(data) {
                const pembelian = data.pembelian;
                const supplier = data.supplier;
                const bahanBaku = data.bahanBaku;

                let itemsHtml = '';
                pembelian.detail_pembelian.forEach((item, index) => {
                    const bahan = item.bahan_baku;
                    itemsHtml += `
                        <div class="item-row row mb-2 edit-item" data-index="${index}">
                            <div class="col-md-4">
                                <select name="items[${index}][bahan_baku_id]" class="form-control bahan-baku-select-edit" required>
                                    <option value="">Pilih Bahan Baku</option>
                                    ${bahanBaku.map(b => `
                                                    <option value="${b.id}" 
                                                        data-harga="${b.harga_beli}"
                                                        data-stok="${b.stok}"
                                                        ${item.bahan_baku_id == b.id ? 'selected' : ''}>
                                                        ${b.nama} (Stok: ${b.stok})
                                                    </option>
                                                `).join('')}
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="items[${index}][jumlah]" class="form-control jumlah-edit" 
                                    value="${item.jumlah}" placeholder="Jumlah" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="items[${index}][harga]" class="form-control harga-edit" 
                                    value="${item.harga}" placeholder="Harga" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control-plaintext sub-total-edit" 
                                    value="Rp ${(item.jumlah * item.harga).toLocaleString('id-ID')}" readonly>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-remove-item-edit" ${index === 0 ? 'disabled' : ''}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });

                const html = `
                    <input type="hidden" name="id" value="${pembelian.id}">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-control" required>
                                    <option value="">Pilih Supplier</option>
                                    ${supplier.map(s => `
                                                    <option value="${s.id}" ${pembelian.supplier_id == s.id ? 'selected' : ''}>
                                                        ${s.nama}
                                                    </option>
                                                `).join('')}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control" 
                                    value="${pembelian.tanggal ? pembelian.tanggal.split(' ')[0] : ''}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Items Pembelian <span class="text-danger">*</span></label>
                        <div id="edit-items-container">
                            ${itemsHtml}
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" id="btn-add-item-edit">
                            <i class="fas fa-plus"></i> Tambah Item
                        </button>
                    </div>

                    <div class="form-group">
                        <label class="h5">Total Pembelian: <span id="total-display-edit" 
                            class="text-success font-weight-bold">Rp ${parseFloat(pembelian.total).toLocaleString('id-ID')}</span></label>
                    </div>
                `;

                $('#edit-content').html(html);
                attachEditFormEvents();
            }

            function attachEditFormEvents() {
                let editItemCounter = $('.edit-item').length;

                // Event untuk menambah item
                $('#btn-add-item-edit').off('click').on('click', function() {
                    const newItem = `
                        <div class="item-row row mb-2 edit-item" data-index="${editItemCounter}">
                            <div class="col-md-4">
                                <select name="items[${editItemCounter}][bahan_baku_id]" class="form-control bahan-baku-select-edit" required>
                                    <option value="">Pilih Bahan Baku</option>
                                    @foreach ($bahanBaku as $bahan)
                                        <option value="{{ $bahan->id }}" 
                                            data-harga="{{ $bahan->harga_beli }}"
                                            data-stok="{{ $bahan->stok }}">
                                            {{ $bahan->nama }} (Stok: {{ $bahan->stok }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="items[${editItemCounter}][jumlah]" class="form-control jumlah-edit" 
                                    placeholder="Jumlah" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="items[${editItemCounter}][harga]" class="form-control harga-edit" 
                                    placeholder="Harga" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control-plaintext sub-total-edit" value="Rp 0" readonly>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-remove-item-edit">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    $('#edit-items-container').append(newItem);
                    editItemCounter++;
                    updateEditRemoveButtons();
                    attachEditItemEvents();
                });

                // Event untuk menghapus item
                $(document).on('click', '.btn-remove-item-edit', function() {
                    if ($('.edit-item').length > 1) {
                        $(this).closest('.edit-item').remove();
                        calculateEditTotal();
                        updateEditRemoveButtons();
                    }
                });

                // Event untuk perubahan select bahan baku
                $(document).on('change', '.bahan-baku-select-edit', function() {
                    const selectedOption = $(this).find('option:selected');
                    const harga = selectedOption.data('harga');

                    if (harga) {
                        $(this).closest('.edit-item').find('.harga-edit').val(harga);
                    }

                    calculateEditItemSubTotal($(this).closest('.edit-item'));
                    calculateEditTotal();
                });

                // Event untuk perubahan jumlah dan harga
                $(document).on('input', '.jumlah-edit, .harga-edit', function() {
                    calculateEditItemSubTotal($(this).closest('.edit-item'));
                    calculateEditTotal();
                });

                updateEditRemoveButtons();
                attachEditItemEvents();
            }

            function attachEditItemEvents() {
                // Event untuk perhitungan otomatis item edit
                $('.bahan-baku-select-edit').each(function() {
                    $(this).trigger('change');
                });
            }

            function updateEditRemoveButtons() {
                const itemRows = $('.edit-item');
                itemRows.each(function(index) {
                    const $removeBtn = $(this).find('.btn-remove-item-edit');
                    $removeBtn.prop('disabled', itemRows.length <= 1);
                });
            }

            function calculateEditItemSubTotal(row) {
                const jumlah = parseFloat(row.find('.jumlah-edit').val()) || 0;
                const harga = parseFloat(row.find('.harga-edit').val()) || 0;
                const subTotal = jumlah * harga;
                row.find('.sub-total-edit').val('Rp ' + subTotal.toLocaleString('id-ID'));
            }

            function calculateEditTotal() {
                let total = 0;
                $('.edit-item').each(function() {
                    const jumlah = parseFloat($(this).find('.jumlah-edit').val()) || 0;
                    const harga = parseFloat($(this).find('.harga-edit').val()) || 0;
                    total += jumlah * harga;
                });
                $('#total-display-edit').text('Rp ' + total.toLocaleString('id-ID'));
            }

            $('#formEdit').submit(function(e) {
                e.preventDefault();

                if (!currentEditId) {
                    showAlert('error', 'ID pembelian tidak valid');
                    return;
                }

                let hasValidItem = false;
                $('.bahan-baku-select-edit').each(function() {
                    if ($(this).val()) {
                        hasValidItem = true;
                    }
                });

                if (!hasValidItem) {
                    showAlert('error', 'Pilih minimal satu bahan baku');
                    return;
                }

                showLoading();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        hideLoading();
                        $('#modalEdit').modal('hide');
                        showAlert('success', response.success);
                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errorMessage = 'Terjadi kesalahan saat mengupdate pembelian';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        showAlert('error', errorMessage);
                    }
                });
            });

            function approvePembelian(id) {
                showLoading();

                $.ajax({
                    url: `/owner/pembelian/${id}/approve`,
                    type: 'POST',
                    success: function(response) {
                        hideLoading();
                        showAlert('success', response.success);
                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errorMessage = 'Terjadi kesalahan saat menyetujui pembelian';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        showAlert('error', errorMessage);
                    }
                });
            }

            function rejectPembelian(id) {
                showLoading();

                $.ajax({
                    url: `/owner/pembelian/${id}/reject`,
                    type: 'POST',
                    success: function(response) {
                        hideLoading();
                        showAlert('success', response.success);
                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errorMessage = 'Terjadi kesalahan saat menolak pembelian';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        showAlert('error', errorMessage);
                    }
                });
            }

            function deletePembelian(id) {
                showLoading();

                $.ajax({
                    url: `/owner/pembelian/${id}`,
                    type: 'DELETE',
                    success: function(response) {
                        hideLoading();
                        showAlert('success', response.success);
                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errorMessage = 'Terjadi kesalahan saat menghapus pembelian';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        showAlert('error', errorMessage);
                    }
                });
            }

            function receivePembelian(id) {
                showLoading();

                $.ajax({
                    url: `/owner/pembelian/${id}/receive`,
                    type: 'POST',
                    success: function(response) {
                        hideLoading();
                        showAlert('success', response.success);
                        setTimeout(() => location.reload(), 2000);
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errorMessage = 'Terjadi kesalahan saat menerima pembelian';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        showAlert('error', errorMessage);
                    }
                });
            }

            // ========== FUNGSI BANTU ==========
            function showConfirmationModal(message, callback) {
                $('#konfirmasi-pesan').html(message);
                $('#modalKonfirmasi').modal('show');

                $('#konfirmasi-ya').off('click').on('click', function() {
                    $('#modalKonfirmasi').modal('hide');
                    callback();
                });
            }

            function getStatusBadgeClass(status) {
                switch (status) {
                    case 'menunggu_persetujuan':
                        return 'badge-warning';
                    case 'completed':
                        return 'badge-success';
                    case 'diterima':
                        return 'badge-info';
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
                        return 'Disetujui';
                    case 'diterima':
                        return 'Diterima';
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

            function showLoading() {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            function hideLoading() {
                Swal.close();
            }

            // ========== FUNGSI FORM TAMBAH PEMBELIAN ==========
            $(document).on('change', '.bahan-baku-select', function() {
                const selectedOption = $(this).find('option:selected');
                const harga = selectedOption.data('harga');
                const stok = selectedOption.data('stok');
                const min = selectedOption.data('min');
                const rop = selectedOption.data('rop');
                const rekomendasi = selectedOption.data('rekomendasi');
                const satuan = selectedOption.data('satuan');

                if (harga) {
                    $(this).closest('.item-row').find('.harga').val(harga);
                }

                if (stok <= min && rop > 0) {
                    $(this).closest('.item-row').find('.jumlah').val(rekomendasi);

                    showAlert('info',
                        `<strong>${selectedOption.text().split('(')[0].trim()}</strong><br>` +
                        `• Stok: ${stok} ${satuan} (Min: ${min} ${satuan})<br>` +
                        `• ROP: ${rop} ${satuan}<br>` +
                        `• Rekomendasi beli: ${rekomendasi} ${satuan}`
                    );
                }

                calculateItemSubTotal($(this).closest('.item-row'));
                calculateTotal();
            });

            $('#btn-add-item').click(function() {
                const newItem = `
                    <div class="item-row row mb-2">
                        <div class="col-md-4">
                            <select name="items[${itemCounter}][bahan_baku_id]" class="form-control bahan-baku-select" required>
                                <option value="">Pilih Bahan Baku</option>
                                @foreach ($bahanBaku as $bahan)
                                    @php
                                        $rop = $bahan->rop ?? 0;
                                        $rekomendasiBeli = 0;
                                        if ($bahan->rop > 0) {
                                            $rekomendasiBeli = $bahan->rop;
                                        } elseif ($bahan->max > 0) {
                                            $rekomendasiBeli = max(1, $bahan->max - $bahan->stok);
                                        }
                                    @endphp
                                    <option value="{{ $bahan->id }}" 
                                        data-harga="{{ $bahan->harga_beli }}"
                                        data-stok="{{ $bahan->stok }}" 
                                        data-min="{{ $bahan->min }}"
                                        data-max="{{ $bahan->max }}" 
                                        data-rop="{{ $rop }}"
                                        data-rekomendasi="{{ $rekomendasiBeli }}"
                                        data-satuan="{{ $bahan->satuan }}">
                                        {{ $bahan->nama }}
                                        @if ($bahan->stok <= $bahan->min)
                                            <span class="text-danger">(Stok: {{ $bahan->stok }} - Min: {{ $bahan->min }})</span>
                                        @else
                                            (Stok: {{ $bahan->stok }}, Min: {{ $bahan->min }})
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

            $(document).on('input', '.jumlah, .harga', function() {
                calculateItemSubTotal($(this).closest('.item-row'));
                calculateTotal();
            });

            // Form tambah pembelian
            $('#formTambah').submit(function(e) {
                let hasValidItem = false;
                $('.bahan-baku-select').each(function() {
                    if ($(this).val()) {
                        hasValidItem = true;
                    }
                });

                if (!hasValidItem) {
                    e.preventDefault();
                    showAlert('error', 'Pilih minimal satu bahan baku');
                    return;
                }

                showLoading();
            });

            // Reset form ketika modal ditutup
            $('#modalTambah').on('hidden.bs.modal', function() {
                $('#formTambah')[0].reset();
                $('#items-container').html(`
                    <div class="item-row row mb-2">
                        <div class="col-md-4">
                            <select name="items[0][bahan_baku_id]" class="form-control bahan-baku-select" required>
                                <option value="">Pilih Bahan Baku</option>
                                @foreach ($bahanBaku as $bahan)
                                    @php
                                        $rop = $bahan->rop ?? 0;
                                        $rekomendasiBeli = 0;
                                        if ($bahan->rop > 0) {
                                            $rekomendasiBeli = $bahan->rop;
                                        } elseif ($bahan->max > 0) {
                                            $rekomendasiBeli = max(1, $bahan->max - $bahan->stok);
                                        }
                                    @endphp
                                    <option value="{{ $bahan->id }}" 
                                        data-harga="{{ $bahan->harga_beli }}"
                                        data-stok="{{ $bahan->stok }}" 
                                        data-min="{{ $bahan->min }}"
                                        data-max="{{ $bahan->max }}" 
                                        data-rop="{{ $rop }}"
                                        data-rekomendasi="{{ $rekomendasiBeli }}"
                                        data-satuan="{{ $bahan->satuan }}">
                                        {{ $bahan->nama }}
                                        @if ($bahan->stok <= $bahan->min)
                                            <span class="text-danger">(Stok: {{ $bahan->stok }} - Min: {{ $bahan->min }})</span>
                                        @else
                                            (Stok: {{ $bahan->stok }}, Min: {{ $bahan->min }})
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
                calculateTotal();
            });

            // Inisialisasi
            updateRemoveButtons();
            calculateTotal();
        });
    </script>
@endpush
