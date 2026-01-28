@extends('layoutsAPP.deskapp')

@section('title', 'Pembelian Bahan Baku - Owner')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Pembelian (Owner)</h3>
                        <div class="float-right">
                            <!-- Form Search dengan Dropdown -->
                            <form action="{{ route('owner.pembelian.index') }}" method="GET"
                                class="form-inline float-right mr-3">
                                <div class="form-group mr-2">
                                    <label for="search_bahan_baku" class="mr-2">Cari Bahan Baku:</label>
                                    <select name="search_bahan_baku" class="form-control select2" style="min-width: 250px;">
                                        <option value="">-- Semua Bahan Baku --</option>
                                        @foreach ($bahanBakuList as $bahan)
                                            <option value="{{ $bahan->nama }}"
                                                {{ $searchBahanBaku == $bahan->nama ? 'selected' : '' }}>
                                                {{ $bahan->nama }}
                                                @if ($bahan->stok <= $bahan->min)
                                                    <span class="text-danger">(Stok: {{ $bahan->stok }}/Min:
                                                        {{ $bahan->min }})</span>
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('owner.pembelian.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-refresh"></i> Reset
                                </a>
                            </form>

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
                        <!-- Informasi Filter Aktif -->
                        @if ($searchBahanBaku)
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-filter"></i> Filter Aktif: Menampilkan pembelian yang mengandung bahan baku
                                <strong>"{{ $searchBahanBaku }}"</strong>
                                <a href="{{ route('owner.pembelian.index') }}" class="float-right">
                                    <i class="fas fa-times"></i> Hapus Filter
                                </a>
                            </div>
                        @endif

                        <!-- Statistik Lead Time -->
                        @if ($leadTimeStats['count'] > 0)
                            <div class="alert alert-primary">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-clock"></i> Statistik Lead Time</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Rata-rata Lead Time</small>
                                                <div><strong>{{ $leadTimeStats['average'] }} hari</strong></div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Lead Time Maksimum</small>
                                                <div><strong>{{ $leadTimeStats['max'] }} hari</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted mb-1">Distribusi Lead Time:</div>
                                        <div class="progress" style="height: 10px;">
                                            @php
                                                $totalBahan = count($leadTimeStats['lead_times']);
                                                $groups = [
                                                    '1-2 hari' => 0,
                                                    '3-5 hari' => 0,
                                                    '6+ hari' => 0,
                                                ];

                                                foreach ($leadTimeStats['lead_times'] as $lt) {
                                                    if ($lt <= 2) {
                                                        $groups['1-2 hari']++;
                                                    } elseif ($lt <= 5) {
                                                        $groups['3-5 hari']++;
                                                    } else {
                                                        $groups['6+ hari']++;
                                                    }
                                                }
                                            @endphp

                                            <div class="progress-bar bg-success"
                                                style="width: {{ $totalBahan > 0 ? ($groups['1-2 hari'] / $totalBahan) * 100 : 0 }}%"
                                                data-toggle="tooltip"
                                                title="{{ $groups['1-2 hari'] }} bahan baku (1-2 hari)">
                                            </div>
                                            <div class="progress-bar bg-warning"
                                                style="width: {{ $totalBahan > 0 ? ($groups['3-5 hari'] / $totalBahan) * 100 : 0 }}%"
                                                data-toggle="tooltip"
                                                title="{{ $groups['3-5 hari'] }} bahan baku (3-5 hari)">
                                            </div>
                                            <div class="progress-bar bg-danger"
                                                style="width: {{ $totalBahan > 0 ? ($groups['6+ hari'] / $totalBahan) * 100 : 0 }}%"
                                                data-toggle="tooltip"
                                                title="{{ $groups['6+ hari'] }} bahan baku (6+ hari)">
                                            </div>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            Parameter stok otomatis dihitung ulang saat penerimaan bahan baku
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

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
                                                <th>Lead Time</th>
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
                                                    <td>{{ $bahan->lead_time }} hari</td>
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
                                        <button class="btn btn-sm btn-outline-light" id="btn-detail-perhitungan">
                                            <i class="fas fa-calculator"></i> Detail Perhitungan
                                        </button>
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
                                                <th>Safety Stock</th>
                                                <th>Rekomendasi Beli</th>
                                                <th>Harga Beli</th>
                                                <th>Sub Total</th>
                                                <th>Satuan</th>
                                                <th>Lead Time</th>
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
                                                    <td><span
                                                            class="badge badge-secondary">{{ $item['safety_stock'] }}</span>
                                                    </td>
                                                    <td><strong
                                                            class="text-success">{{ $item['jumlah_rekomendasi'] }}</strong>
                                                    </td>
                                                    <td>Rp {{ number_format($item['harga_beli'], 0, ',', '.') }}</td>
                                                    <td>Rp {{ number_format($subTotal, 0, ',', '.') }}</td>
                                                    <td>{{ $item['satuan'] }}</td>
                                                    <td>{{ $item['lead_time'] }} hari</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-primary">
                                            <tr>
                                                <th colspan="8" class="text-right">Total Rekomendasi:</th>
                                                <th colspan="3">Rp {{ number_format($totalRekomendasi, 0, ',', '.') }}
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div id="detail-perhitungan" style="display: none;">
                                    <hr>
                                    <h6><i class="fas fa-calculator"></i> Rumus Perhitungan Parameter Stok</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title">Safety Stock (SS)</h6>
                                                    <p class="card-text small">
                                                        <strong>Rumus:</strong> (Permintaan Maksimal Harian × Lead Time
                                                        Maksimum) - (Permintaan Harian Rata-rata × Lead Time Rata-rata)
                                                    </p>
                                                    <p class="card-text small">
                                                        <strong>Fungsi:</strong> Stok pengaman untuk menghadapi fluktuasi
                                                        permintaan dan ketidakpastian lead time
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title">Minimum Stock (Min)</h6>
                                                    <p class="card-text small">
                                                        <strong>Rumus:</strong> (Permintaan Harian Rata-rata × Lead Time
                                                        Rata-rata) + Safety Stock
                                                    </p>
                                                    <p class="card-text small">
                                                        <strong>Fungsi:</strong> Titik minimal stok sebelum melakukan
                                                        pemesanan ulang
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title">Maximum Stock (Max)</h6>
                                                    <p class="card-text small">
                                                        <strong>Rumus:</strong> 2 × (Permintaan Rata-rata × Lead Time
                                                        Rata-rata) + Safety Stock
                                                    </p>
                                                    <p class="card-text small">
                                                        <strong>Fungsi:</strong> Batas maksimal stok untuk mengontrol biaya
                                                        penyimpanan
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title">Reorder Point (ROP)</h6>
                                                    <p class="card-text small">
                                                        <strong>Rumus:</strong> Max - Min
                                                    </p>
                                                    <p class="card-text small">
                                                        <strong>Fungsi:</strong> Jumlah yang harus dipesan saat stok
                                                        mencapai titik minimum
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-info-circle"></i> <strong>Catatan:</strong> Parameter stok
                                        otomatis
                                        dihitung ulang setiap kali bahan baku diterima, berdasarkan lead time actual dan
                                        data penggunaan terbaru.
                                    </div>
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
                                        <th>Lead Time</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th width="250px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pembelian as $index => $item)
                                        @php
                                            // Hitung lead time actual jika sudah diterima
                                            $leadTime = '-';
                                            if ($item->waktu_penerimaan) {
                                                $tanggalPesan = \Carbon\Carbon::parse($item->created_at);
                                                $tanggalTerima = \Carbon\Carbon::parse($item->waktu_penerimaan);
                                                $selisihJam = $tanggalPesan->diffInHours($tanggalTerima);
                                                $leadTimeDays = ceil($selisihJam / 24);
                                                $leadTime = max(1, $leadTimeDays) . ' hari';
                                            }
                                        @endphp
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
                                            <td>{{ $leadTime }}</td>
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
                <form id="formPembelianCepat" action="{{ route('owner.pembelian.pembelian-cepat.store') }}"
                    method="POST">
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
                                            <th>Lead Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pembelian-cepat-items-body">
                                    </tbody>
                                    <tfoot class="table-success">
                                        <tr>
                                            <th colspan="10" class="text-right">Total Pembelian:</th>
                                            <th colspan="2" id="total-pembelian-cepat">Rp 0</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong>
                            <ul class="mb-0 pl-3">
                                <li>Pembelian ini akan dibuat dengan status "menunggu_persetujuan".</li>
                                <li>Anda perlu menyetujui pembelian sebelum menerima barang.</li>
                                <li>Saat menerima pembelian, parameter stok (safety stock, min, max, rop) akan otomatis
                                    dihitung ulang berdasarkan lead time actual.</li>
                            </ul>
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
                                                    data-satuan="{{ $bahan->satuan }}"
                                                    data-leadtime="{{ $bahan->lead_time }}">
                                                    {{ $bahan->nama }}
                                                    @if ($bahan->stok <= $bahan->min)
                                                        <span class="text-danger">(Stok: {{ $bahan->stok }} - Min:
                                                            {{ $bahan->min }})</span>
                                                    @else
                                                        (Stok: {{ $bahan->stok }}, Min: {{ $bahan->min }}, LT:
                                                        {{ $bahan->lead_time }} hari)
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
                <form id="formPrintLaporan" action="{{ route('owner.pembelian.print-laporan') }}" method="POST"
                    target="_blank">
                    @csrf
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
                                        <option value="diterima">Diterima</option>
                                        <option value="menunggu_persetujuan">Menunggu Persetujuan</option>
                                        <option value="ditolak">Ditolak</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Format Laporan</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="format" id="formatHTML"
                                            value="html" checked>
                                        <label class="form-check-label" for="formatHTML">
                                            <i class="fas fa-file-alt"></i> HTML (Preview di Browser)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="format" id="formatPDF"
                                            value="pdf">
                                        <label class="form-check-label" for="formatPDF">
                                            <i class="fas fa-file-pdf"></i> PDF (Download File)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning" id="btnPrintLaporan">
                            <i class="fas fa-print"></i> Print Laporan
                        </button>
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
            background-color: #ffe6e6 !important;
        }

        .stok-peringatan {
            background-color: #fff3cd !important;
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

        .progress {
            border-radius: 5px;
        }

        .progress-bar {
            border-radius: 5px;
        }

        .card .card-title {
            font-size: 0.9rem;
            font-weight: bold;
            color: #333;
        }

        .card .card-text {
            font-size: 0.85rem;
            color: #666;
        }

        /* Tambahan styling untuk pembelian cepat */
        .item-checkbox {
            width: 20px;
            height: 20px;
        }

        .stok-kritis {
            background-color: #ffe6e6 !important;
        }

        .stok-peringatan {
            background-color: #fff3cd !important;
        }

        .jumlah-input,
        .harga-input {
            min-width: 80px;
        }

        .harga-input {
            min-width: 120px;
        }

        #pembelian-cepat-table table {
            font-size: 0.9rem;
        }

        #pembelian-cepat-table th {
            white-space: nowrap;
        }

        #btn-submit-pembelian-cepat:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Search form styling */
        .form-inline .form-group {
            margin-bottom: 0;
        }

        .select2-container {
            min-width: 250px !important;
        }

        /* Loading overlay */
        .swal2-container {
            z-index: 10000 !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2
            $('.select2').select2({
                placeholder: 'Pilih Bahan Baku',
                allowClear: true,
                width: '100%'
            });

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
                showLoading('Memuat data pembelian cepat...');

                // Reset state
                $('#pembelian-cepat-no-data').hide();
                $('#pembelian-cepat-table').hide();
                $('#btn-submit-pembelian-cepat').prop('disabled', true);
                $('#pembelian-cepat-alert').html('');

                // PERBAIKAN: Gunakan route name yang benar
                $.ajax({
                    url: '{{ route('owner.pembelian.pembelian-cepat.data') }}',
                    type: 'GET',
                    dataType: 'json',
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
                    error: function(xhr, status, error) {
                        hideLoading();
                        let errorMessage = 'Terjadi kesalahan saat memuat data pembelian cepat';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 0) {
                            errorMessage =
                                'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
                        }
                        $('#pembelian-cepat-alert').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> ${errorMessage}
                            </div>
                        `);
                        console.error('Error loading pembelian cepat:', error);
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
                                <input type="checkbox" 
                                       class="form-check-input item-checkbox" 
                                       data-index="${itemIndex}"
                                       data-bahan-id="${item.bahan_baku_id}"
                                       data-jumlah="${jumlahBeli}"
                                       data-harga="${item.harga_beli}"
                                       data-total="${subTotal}" 
                                       checked>
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
                                       data-index="${itemIndex}"
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
                                       data-index="${itemIndex}"
                                       data-bahan-id="${item.bahan_baku_id}"
                                       style="width: 120px; display: inline-block;">
                            </td>
                            <td class="text-primary font-weight-bold sub-total-display" 
                                data-index="${itemIndex}"
                                data-subtotal="${subTotal}">
                                Rp ${parseFloat(subTotal).toLocaleString('id-ID')}
                            </td>
                            <td>${item.lead_time || '1'} hari</td>
                            <td>${item.status_stok}</td>
                        </tr>
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
                $(document).off('input', '.jumlah-input').on('input', '.jumlah-input', function() {
                    updatePembelianCepatSubTotal($(this));
                });

                // Harga input change
                $(document).off('input', '.harga-input').on('input', '.harga-input', function() {
                    updatePembelianCepatSubTotal($(this));
                });
            }

            function updatePembelianCepatSubTotal($input) {
                const index = $input.data('index');
                const jumlah = $input.val();
                const harga = $(`.harga-input[data-index="${index}"]`).val();
                const subTotal = (parseFloat(jumlah) || 0) * (parseFloat(harga) || 0);

                // Update data attribute di checkbox
                $(`.item-checkbox[data-index="${index}"]`)
                    .data('jumlah', jumlah)
                    .data('harga', harga)
                    .data('total', subTotal);

                // Update display
                $(`.sub-total-display[data-index="${index}"]`)
                    .text('Rp ' + subTotal.toLocaleString('id-ID'))
                    .data('subtotal', subTotal);

                calculatePembelianCepatTotal();
            }

            function calculatePembelianCepatTotal() {
                let total = 0;
                let selectedCount = 0;

                $('.item-checkbox:checked').each(function() {
                    const subTotal = parseFloat($(this).data('total')) || 0;
                    total += subTotal;
                    selectedCount++;
                });

                $('#total-pembelian-cepat').text('Rp ' + total.toLocaleString('id-ID'));
                $('#selected-count').text(selectedCount + ' item terpilih');

                // Enable/disable submit button
                $('#btn-submit-pembelian-cepat').prop('disabled', selectedCount === 0);
            }

            function updateSelectedCountPembelianCepat() {
                const selectedCount = $('.item-checkbox:checked').length;
                const totalCount = $('.item-checkbox').length;
                $('#selected-count').text(selectedCount + ' dari ' + totalCount + ' item terpilih');
            }

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
                let hasValidItem = false;

                $('.item-checkbox:checked').each(function() {
                    const bahanBakuId = $(this).data('bahan-id');
                    const jumlah = $(this).data('jumlah');
                    const harga = $(this).data('harga');

                    if (bahanBakuId && jumlah && harga && parseFloat(jumlah) > 0 && parseFloat(
                            harga) > 0) {
                        items.push({
                            bahan_baku_id: bahanBakuId,
                            jumlah: parseInt(jumlah) || 1,
                            harga: parseFloat(harga) || 0
                        });
                        hasValidItem = true;
                        itemIndex++;
                    }
                });

                if (!hasValidItem || items.length === 0) {
                    showAlert('error',
                        'Pilih minimal satu item untuk dibeli dengan jumlah dan harga yang valid');
                    return;
                }

                // Siapkan data untuk dikirim
                const formData = {
                    supplier_id: $('select[name="supplier_id"]').val(),
                    tanggal: $('input[name="tanggal"]').val(),
                    items: items,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                showLoading('Membuat pembelian cepat...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(response) {
                        hideLoading();

                        if (response.success) {
                            $('#modalPembelianCepat').modal('hide');

                            // Tampilkan SweetAlert sukses
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: `
                                    <div class="text-center">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <h4>${response.success}</h4>
                                        ${response.message ? `<p class="mt-2">${response.message}</p>` : ''}
                                        ${response.total_items ? `<p><strong>Total Items:</strong> ${response.total_items}</p>` : ''}
                                        ${response.total_pembelian ? `<p><strong>Total Pembelian:</strong> Rp ${parseFloat(response.total_pembelian).toLocaleString('id-ID')}</p>` : ''}
                                        <hr>
                                        <p class="text-muted">Anda akan diarahkan ke halaman pembelian...</p>
                                    </div>
                                `,
                                showConfirmButton: true,
                                timer: 3000
                            });

                            // Redirect ke index pembelian setelah 3 detik
                            setTimeout(() => {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    window.location.href =
                                        '{{ route('owner.pembelian.index') }}';
                                }
                            }, 3000);
                        } else {
                            showAlert('error', response.error ||
                                'Terjadi kesalahan saat membuat pembelian cepat');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading();
                        let errorMessage = 'Terjadi kesalahan saat membuat pembelian cepat';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.status === 422) {
                            errorMessage =
                                'Data yang dimasukkan tidak valid. Periksa kembali input Anda.';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMessage,
                            showConfirmButton: true
                        });
                        console.error('Error creating pembelian cepat:', error);
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

            function showLoading(message = 'Memproses...') {
                Swal.fire({
                    title: message,
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

            // ========== FUNGSI LAINNYA ==========
            // Toggle detail perhitungan
            $('#btn-detail-perhitungan').click(function() {
                $('#detail-perhitungan').slideToggle();
                if ($('#detail-perhitungan').is(':visible')) {
                    $(this).html('<i class="fas fa-times"></i> Sembunyikan Detail');
                } else {
                    $(this).html('<i class="fas fa-calculator"></i> Detail Perhitungan');
                }
            });

            // ========== FUNGSI PRINT LAPORAN ==========
            $('#formPrintLaporan').submit(function(e) {
                e.preventDefault();

                const form = $(this);
                const format = $('input[name="format"]:checked').val();
                const tanggalAwal = $('input[name="tanggal_awal"]').val();
                const tanggalAkhir = $('input[name="tanggal_akhir"]').val();
                const status = $('select[name="status"]').val();

                if (!tanggalAwal || !tanggalAkhir) {
                    showAlert('error', 'Harap isi tanggal awal dan tanggal akhir');
                    return;
                }

                if (format === 'pdf') {
                    // PERBAIKAN: Gunakan route name yang benar untuk export PDF
                    window.open(
                        `{{ route('owner.pembelian.export-pdf') }}?tanggal_awal=${tanggalAwal}&tanggal_akhir=${tanggalAkhir}&status=${status}`,
                        '_blank');
                } else {
                    // Untuk HTML, submit form normal ke target blank
                    // Data akan dikirim via POST ke route print-laporan
                    form.off('submit').submit();
                }
            });

            // ========== FUNGSI REKOMENDASI ROP UNTUK FORM TAMBAH ==========
            $('#btn-use-recommendation').click(function() {
                showLoading('Memuat rekomendasi...');

                // PERBAIKAN: Gunakan route name yang benar
                $.ajax({
                    url: '{{ route('owner.pembelian.rekomendasi.data') }}',
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
                                                        data-leadtime="{{ $bahan->lead_time }}"
                                                        ${item.bahan_baku_id == {{ $bahan->id }} ? 'selected' : ''}>
                                                        {{ $bahan->nama }}
                                                        @if ($bahan->stok <= $bahan->min)
                                                            <span class="text-danger">(Stok: {{ $bahan->stok }} - Min: {{ $bahan->min }})</span>
                                                        @else
                                                            (Stok: {{ $bahan->stok }}, Min: {{ $bahan->min }}, LT: {{ $bahan->lead_time }} hari)
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
                    'Apakah Anda yakin ingin menerima pembelian ini?<br><small>Stok bahan baku akan ditambahkan dan parameter stok (safety stock, min, max, rop) akan otomatis dihitung ulang berdasarkan lead time actual.</small>',
                    function() {
                        receivePembelian(id);
                    }
                );
            });

            function showDetailModal(id) {
                showLoading('Memuat detail...');

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
                        let leadTimeHtml = '';
                        if (response.waktu_penerimaan) {
                            const waktuPenerimaan = new Date(response.waktu_penerimaan);
                            waktuPenerimaanHtml = waktuPenerimaan.toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            // Hitung lead time
                            const tanggalPesan = new Date(response.created_at);
                            const tanggalTerima = new Date(response.waktu_penerimaan);
                            const selisihJam = (tanggalTerima - tanggalPesan) / (1000 * 60 * 60);
                            const leadTimeDays = Math.ceil(selisihJam / 24);
                            leadTimeHtml =
                                `<p><strong>Lead Time Actual:</strong> ${Math.max(1, leadTimeDays)} hari</p>`;
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
                                    ${leadTimeHtml}
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
                showLoading('Memuat data...');

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
                                                                                                                                                    data-leadtime="${b.lead_time}"
                                                                                                                                                    ${item.bahan_baku_id == b.id ? 'selected' : ''}>
                                                                                                                                                    ${b.nama} (Stok: ${b.stok}, LT: ${b.lead_time} hari)
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
                                            data-stok="{{ $bahan->stok }}"
                                            data-leadtime="{{ $bahan->lead_time }}">
                                            {{ $bahan->nama }} (Stok: {{ $bahan->stok }}, LT: {{ $bahan->lead_time }} hari)
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

                showLoading('Mengupdate pembelian...');

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
                showLoading('Menyetujui pembelian...');

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
                showLoading('Menolak pembelian...');

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
                showLoading('Menghapus pembelian...');

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
                showLoading('Menerima pembelian...');

                $.ajax({
                    url: `/owner/pembelian/${id}/receive`,
                    type: 'POST',
                    success: function(response) {
                        hideLoading();

                        // Tampilkan detail perhitungan yang terjadi
                        let detailHtml = `
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle"></i> ${response.success}</h5>
                                <p><strong>Lead Time Actual:</strong> ${response.lead_time_formatted}</p>
                                <hr>
                                <h6>Detail Perubahan Parameter:</h6>
                        `;

                        if (response.updates && response.updates.length > 0) {
                            response.updates.forEach(update => {
                                detailHtml += `
                                    <div class="card mb-2">
                                        <div class="card-header bg-light py-2">
                                            <strong>${update.bahan_baku}</strong>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Stok:</strong> ${update.stok_sebelum} → ${update.stok_sesudah} (+${update.jumlah_ditambahkan})</p>
                                                    <p><strong>Safety Stock:</strong> ${update.safety_stock_sebelum} → ${update.safety_stock_sesudah}</p>
                                                    <p><strong>Min:</strong> ${update.min_sebelum} → ${update.min_sesudah}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Max:</strong> ${update.max_sebelum} → ${update.max_sesudah}</p>
                                                    <p><strong>ROP:</strong> ${update.rop_sebelum} → ${update.rop_sesudah}</p>
                                                    <p><strong>Lead Time:</strong> ${update.lead_time_sebelum} → ${update.lead_time_sesudah}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        }

                        detailHtml += `
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle"></i> <strong>Sistem telah otomatis menghitung:</strong>
                                    <ul class="mb-0 pl-3">
                                        <li>Safety Stock berdasarkan lead time actual dan data penggunaan</li>
                                        <li>Min, Max, dan ROP berdasarkan rumus sistem</li>
                                        <li>Lead time rata-rata dan maksimum diperbarui</li>
                                    </ul>
                                </div>
                            </div>
                        `;

                        Swal.fire({
                            title: 'Pembelian Diterima',
                            html: detailHtml,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            width: '800px'
                        });

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

            // ========== FUNGSI BANTU LAINNYA ==========
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

            // ========== FUNGSI FORM TAMBAH PEMBELIAN ==========
            $(document).on('change', '.bahan-baku-select', function() {
                const selectedOption = $(this).find('option:selected');
                const harga = selectedOption.data('harga');
                const stok = selectedOption.data('stok');
                const min = selectedOption.data('min');
                const rop = selectedOption.data('rop');
                const rekomendasi = selectedOption.data('rekomendasi');
                const satuan = selectedOption.data('satuan');
                const leadTime = selectedOption.data('leadtime');

                if (harga) {
                    $(this).closest('.item-row').find('.harga').val(harga);
                }

                if (stok <= min && rop > 0) {
                    $(this).closest('.item-row').find('.jumlah').val(rekomendasi);

                    showAlert('info',
                        `<strong>${selectedOption.text().split('(')[0].trim()}</strong><br>` +
                        `• Stok: ${stok} ${satuan} (Min: ${min} ${satuan})<br>` +
                        `• ROP: ${rop} ${satuan}<br>` +
                        `• Lead Time: ${leadTime} hari<br>` +
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
                                        data-satuan="{{ $bahan->satuan }}"
                                        data-leadtime="{{ $bahan->lead_time }}">
                                        {{ $bahan->nama }}
                                        @if ($bahan->stok <= $bahan->min)
                                            <span class="text-danger">(Stok: {{ $bahan->stok }} - Min: {{ $bahan->min }})</span>
                                        @else
                                            (Stok: {{ $bahan->stok }}, Min: {{ $bahan->min }}, LT: {{ $bahan->lead_time }} hari)
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

                showLoading('Menyimpan pembelian...');
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
                                        data-satuan="{{ $bahan->satuan }}"
                                        data-leadtime="{{ $bahan->lead_time }}">
                                        {{ $bahan->nama }}
                                        @if ($bahan->stok <= $bahan->min)
                                            <span class="text-danger">(Stok: {{ $bahan->stok }} - Min: {{ $bahan->min }})</span>
                                        @else
                                            (Stok: {{ $bahan->stok }}, Min: {{ $bahan->min }}, LT: {{ $bahan->lead_time }} hari)
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
