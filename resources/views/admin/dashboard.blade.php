@extends('layoutsAPP.deskapp')

@section('title', 'Admin Dashboard')

@section('content')
    @if (isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="icon-copy dw dw-warning"></i> <strong>Error:</strong> {{ $error }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card-box pd-20 height-100-p mb-30">
        <h4 class="font-20 weight-500 mb-10 text-capitalize">
            Welcome back <span class="weight-600 font-30 text-blue">{{ Auth::user()->name }}</span>
        </h4>
        <p class="font-18 max-width-600">Dashboard Ringkasan Penjualan dan Manajemen Bahan Baku.</p>
    </div>

    <div class="row">
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-30">
            <div class="card-box height-100-p widget-style1">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="widget-data">
                        <div class="h4 mb-0 text-blue">
                            Rp {{ number_format($statistik['penjualan_hari_ini'] ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="weight-600 font-14">Penjualan Hari Ini</div>
                    </div>
                    <div class="widget-icon">
                        <div class="icon" data-color="#00eccf">
                            <i class="icon-copy dw dw-money"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-30">
            <div class="card-box height-100-p widget-style1">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="widget-data">
                        <div class="h4 mb-0 text-success">
                            Rp {{ number_format($statistik['penjualan_bulan_ini'] ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="weight-600 font-14">Penjualan Bulan Ini</div>
                    </div>
                    <div class="widget-icon">
                        <div class="icon" data-color="#ff5b5b">
                            <i class="icon-copy dw dw-calendar1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-30">
            <div class="card-box height-100-p widget-style1">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="widget-data">
                        <div class="h4 mb-0 text-warning">{{ $statistik['total_transaksi_hari_ini'] ?? 0 }}</div>
                        <div class="weight-600 font-14">Transaksi Hari Ini</div>
                    </div>
                    <div class="widget-icon">
                        <div class="icon" data-color="#09cc06">
                            <i class="icon-copy dw dw-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-30">
            <div class="card-box height-100-p widget-style1">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="widget-data">
                        <div class="h4 mb-0 text-danger">{{ $statistik['total_bahan_baku_perlu_beli'] ?? 0 }}</div>
                        <div class="weight-600 font-14">Bahan Baku Perlu Beli</div>
                    </div>
                    <div class="widget-icon">
                        <div class="icon" data-color="#1b00ff">
                            <i class="icon-copy dw dw-alert"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12 mb-30">
            <div class="card-box height-100-p pd-20">
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-0">
                    <div class="h5 mb-0">Grafik Penjualan 12 Bulan Terakhir</div>
                    <div class="dropdown">
                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" data-color="#1b3133"
                            href="#" role="button" data-toggle="dropdown">
                            <i class="dw dw-more"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                            <a class="dropdown-item" href="{{ route('admin.penjualan.index') }}">
                                <i class="dw dw-eye"></i> Lihat Detail Penjualan
                            </a>
                        </div>
                    </div>
                </div>
                <div id="penjualan-chart" class="chart-container" style="min-height: 400px;">
                    @if (!empty($penjualanPerBulan))
                        <div class="chart-loading">
                            <i class="icon-copy dw dw-arrow-circle"></i>
                            <div class="mt-2">Memuat grafik penjualan...</div>
                        </div>
                    @else
                        <div class="no-data">
                            <i class="icon-copy dw dw-chart-1" style="font-size: 40px; color: #ccc;"></i>
                            <h5 class="mt-3">Tidak ada data penjualan</h5>
                            <p>Belum ada transaksi penjualan dalam 12 bulan terakhir.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 mb-30">
            <div class="card-box height-100-p pd-20">
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-0">
                    <div class="h5 mb-0">Statistik Bahan Baku</div>
                </div>
                <div class="mt-4">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-blue mr-3">
                                    <i class="icon-copy dw dw-box"></i>
                                </div>
                                <div>
                                    <div class="h4 mb-0">{{ $statistik['total_bahan_baku'] ?? 0 }}</div>
                                    <div class="font-12">Total Bahan Baku</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-danger mr-3">
                                    <i class="icon-copy dw dw-alert"></i>
                                </div>
                                <div>
                                    <div class="h4 mb-0">{{ $statistik['total_bahan_baku_perlu_beli'] ?? 0 }}</div>
                                    <div class="font-12">Perlu Pembelian</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="bahan-baku-chart" class="chart-container" style="min-height: 250px;">
                        @if (($statistik['total_bahan_baku'] ?? 0) > 0)
                            <div class="chart-loading">
                                <i class="icon-copy dw dw-arrow-circle"></i>
                                <div class="mt-2">Memuat grafik...</div>
                            </div>
                        @else
                            <div class="no-data">
                                <i class="icon-copy dw dw-box-1" style="font-size: 40px; color: #ccc;"></i>
                                <h5 class="mt-3">Tidak ada bahan baku</h5>
                                <p>Belum ada data bahan baku yang terdaftar.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-30">
            <div class="card-box height-100-p pd-20">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div class="h5 mb-0">Bahan Baku yang Perlu Dibeli</div>
                    <div class="dropdown">
                        <a class="btn btn-primary btn-sm" href="{{ route('admin.bahan-baku.index') }}">
                            <i class="icon-copy dw dw-list"></i> Lihat Semua Bahan Baku
                        </a>
                    </div>
                </div>

                @if (isset($bahanBakuPerluBeli) && $bahanBakuPerluBeli->count() > 0)
                    <div class="table-responsive">
                        <table class="data-table table stripe hover nowrap">
                            <thead>
                                <tr>
                                    <th class="table-plus">Nama Bahan Baku</th>
                                    <th>Satuan</th>
                                    <th>Stok Saat Ini</th>
                                    <th>Minimal Stok</th>
                                    <th>Safety Stock</th>
                                    <th>ROP</th>
                                    <th>Rekomendasi Beli</th>
                                    <th>Total Nilai</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bahanBakuPerluBeli as $bahan)
                                    @php
                                        $rekomendasi = null;
                                        try {
                                            $rekomendasi = $bahan->getRekomendasiPembelianRopAttribute();
                                        } catch (\Exception $e) {
                                            \Log::error('Error rekomendasi bahan baku: ' . $e->getMessage());
                                        }
                                    @endphp
                                    <tr>
                                        <td class="table-plus">
                                            <div class="d-flex align-items-center">
                                                @if ($bahan->foto)
                                                    <img src="{{ $bahan->foto_url ?? asset('images/default.png') }}"
                                                        class="img-fluid rounded-circle mr-2" width="30"
                                                        height="30" alt="{{ $bahan->nama }}">
                                                @endif
                                                <span>{{ $bahan->nama }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $bahan->satuan ?? '-' }}</td>
                                        <td class="text-center">
                                            <span
                                                class="badge badge-{{ ($bahan->stok ?? 0) <= ($bahan->safety_stock ?? 0) ? 'warning' : (($bahan->stok ?? 0) <= ($bahan->min ?? 0) ? 'danger' : 'success') }}">
                                                {{ $bahan->stok ?? 0 }}
                                            </span>
                                        </td>
                                        <td>{{ $bahan->min ?? 0 }}</td>
                                        <td>{{ $bahan->safety_stock ?? 0 }}</td>
                                        <td>{{ $bahan->rop ?? 0 }}</td>
                                        <td class="text-center">
                                            @if ($rekomendasi && isset($rekomendasi['jumlah_rekomendasi']) && $rekomendasi['jumlah_rekomendasi'] > 0)
                                                <span class="badge badge-primary">{{ $rekomendasi['jumlah_rekomendasi'] }}
                                                    {{ $bahan->satuan }}</span>
                                            @else
                                                <span class="badge badge-secondary">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($rekomendasi && isset($rekomendasi['total_nilai']) && $rekomendasi['total_nilai'] > 0)
                                                Rp {{ number_format($rekomendasi['total_nilai'], 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{!! $bahan->status_stok ?? '' !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="no-data text-center py-5">
                        <i class="icon-copy dw dw-check" style="font-size: 40px; color: #28a745;"></i>
                        <h5 class="mt-3">Semua stok bahan baku dalam kondisi aman</h5>
                        <p>Tidak ada bahan baku yang perlu dibeli saat ini.</p>
                        {{-- <a href="{{ route('admin.bahan-baku.create') }}" class="btn btn-primary mt-3">
                            <i class="icon-copy dw dw-add"></i> Tambah Bahan Baku Baru
                        </a> --}}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if (isset($penggunaanBahanBaku) && ($penggunaanBahanBaku['total_bahan_baku'] ?? 0) > 0)
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-30">
                <div class="card-box height-100-p pd-20">
                    <div class="d-flex flex-wrap justify-content-between align-items-center pb-0">
                        <div class="h5 mb-0">Grafik Penggunaan Bahan Baku 12 Bulan Terakhir</div>
                        <div class="dropdown">
                            <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                data-color="#1b3133" href="#" role="button" data-toggle="dropdown">
                                <i class="dw dw-more"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                <a class="dropdown-item" href="{{ route('admin.bahan-baku.index') }}">
                                    <i class="dw dw-eye"></i> Lihat Semua Bahan Baku
                                </a>
                                <a class="dropdown-item" href="{{ route('admin.penjualan.index') }}">
                                    <i class="dw dw-money"></i> Lihat Penjualan
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                        <i class="icon-copy dw dw-info"></i>
                        <strong>Informasi:</strong> Grafik ini menampilkan penggunaan bahan baku dari semua sumber, termasuk
                        penjualan bahan baku langsung dan penggunaan melalui produk yang terjual.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="penggunaan-bahan-baku-chart" class="chart-container" style="min-height: 400px;">
                        <div class="chart-loading">
                            <i class="icon-copy dw dw-arrow-circle"></i>
                            <div class="mt-2">Memuat grafik penggunaan bahan baku...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-30">
                <div class="card-box height-100-p pd-20">
                    <div class="d-flex flex-wrap justify-content-between align-items-center pb-0">
                        <div class="h5 mb-0">Grafik Penggunaan Bahan Baku 12 Bulan Terakhir</div>
                    </div>
                    <div class="no-data text-center py-5">
                        <i class="icon-copy dw dw-chart" style="font-size: 40px; color: #ccc;"></i>
                        <h5 class="mt-3">Belum ada data penggunaan bahan baku</h5>
                        <p>Data akan muncul setelah ada penjualan bahan baku langsung atau produk yang menggunakan bahan
                            baku.</p>
                        <a href="{{ route('admin.penjualan.create') }}" class="btn btn-primary mt-3">
                            <i class="icon-copy dw dw-cart"></i> Buat Penjualan Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard script dimulai...');

            if (typeof ApexCharts === 'undefined') {
                console.error('ApexCharts tidak ter-load!');
                document.querySelectorAll('.chart-container').forEach(el => {
                    el.innerHTML =
                        '<div class="error-message">Error: Library ApexCharts gagal dimuat. Periksa koneksi internet atau coba refresh halaman.</div>';
                });
                return;
            }

            console.log('ApexCharts berhasil dimuat:', ApexCharts);

            const penjualanData = @json($penjualanPerBulan ?? [], JSON_HEX_TAG);
            const bahanBakuStats = {
                aman: {{ ($statistik['total_bahan_baku'] ?? 0) - ($statistik['total_bahan_baku_perlu_beli'] ?? 0) }},
                perlu: {{ $statistik['total_bahan_baku_perlu_beli'] ?? 0 }}
            };
            const penggunaanData = @json($penggunaanBahanBaku ?? [], JSON_HEX_TAG);

            console.log('Data penjualan:', penjualanData);
            console.log('Stats bahan baku:', bahanBakuStats);
            console.log('Data penggunaan bahan baku:', penggunaanData);

            function renderPenjualanChart() {
                const penjualanChartEl = document.getElementById('penjualan-chart');
                if (!penjualanChartEl) return;

                if (penjualanData && penjualanData.length > 0) {
                    const categories = penjualanData.map(item => item.bulan || '');
                    const seriesData = penjualanData.map(item => parseFloat(item.total_penjualan) || 0);

                    if (seriesData.some(v => v > 0)) {
                        try {
                            penjualanChartEl.innerHTML = '';

                            const chart = new ApexCharts(penjualanChartEl, {
                                chart: {
                                    type: 'area',
                                    height: 400,
                                    toolbar: {
                                        show: true,
                                        tools: {
                                            download: true,
                                            selection: true,
                                            zoom: true,
                                            zoomin: true,
                                            zoomout: true,
                                            pan: true,
                                            reset: true
                                        }
                                    }
                                },
                                series: [{
                                    name: 'Penjualan (Rp)',
                                    data: seriesData
                                }],
                                stroke: {
                                    curve: 'smooth',
                                    width: 3
                                },
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        shadeIntensity: 1,
                                        opacityFrom: 0.7,
                                        opacityTo: 0.2
                                    }
                                },
                                colors: ['#1b00ff'],
                                xaxis: {
                                    categories: categories,
                                    labels: {
                                        rotate: -45,
                                        style: {
                                            fontSize: '12px'
                                        }
                                    }
                                },
                                yaxis: {
                                    labels: {
                                        formatter: function(val) {
                                            return 'Rp ' + (val / 1000000).toFixed(1) + ' jt';
                                        }
                                    },
                                    title: {
                                        text: 'Total Penjualan (Rp)',
                                        style: {
                                            fontSize: '12px'
                                        }
                                    }
                                },
                                tooltip: {
                                    y: {
                                        formatter: function(val) {
                                            return 'Rp ' + val.toLocaleString('id-ID');
                                        }
                                    }
                                },
                                dataLabels: {
                                    enabled: false
                                },
                                grid: {
                                    borderColor: '#f1f1f1',
                                }
                            });

                            chart.render();
                            console.log('Chart penjualan berhasil dirender');
                        } catch (err) {
                            console.error('Error render penjualan chart:', err);
                            penjualanChartEl.innerHTML =
                                '<div class="no-data">Error: Gagal memuat grafik penjualan</div>';
                        }
                    } else {
                        penjualanChartEl.innerHTML =
                            '<div class="no-data">Tidak ada data penjualan untuk ditampilkan</div>';
                    }
                } else {
                    penjualanChartEl.innerHTML = '<div class="no-data">Data penjualan tidak tersedia</div>';
                }
            }

            function renderBahanBakuChart() {
                const bahanBakuChartEl = document.getElementById('bahan-baku-chart');
                if (!bahanBakuChartEl) return;

                const totalBahan = bahanBakuStats.aman + bahanBakuStats.perlu;

                if (totalBahan > 0) {
                    try {
                        bahanBakuChartEl.innerHTML = '';

                        const chart = new ApexCharts(bahanBakuChartEl, {
                            chart: {
                                type: 'donut',
                                height: 250
                            },
                            series: [bahanBakuStats.aman, bahanBakuStats.perlu],
                            labels: ['Stok Aman', 'Perlu Pembelian'],
                            colors: ['#28a745', '#dc3545'],
                            legend: {
                                position: 'bottom',
                                horizontalAlign: 'center'
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '65%',
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                label: 'Total Bahan',
                                                color: '#333',
                                                fontSize: '16px'
                                            }
                                        }
                                    }
                                }
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val + ' bahan baku';
                                    }
                                }
                            },
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        height: 200
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }]
                        });

                        chart.render();
                        console.log('Chart bahan baku berhasil dirender');
                    } catch (err) {
                        console.error('Error render bahan baku chart:', err);
                        bahanBakuChartEl.innerHTML =
                            '<div class="no-data">Error: Gagal memuat grafik bahan baku</div>';
                    }
                } else {
                    bahanBakuChartEl.innerHTML = '<div class="no-data">Tidak ada data bahan baku</div>';
                }
            }

            function renderPenggunaanBahanBakuChart() {
                const penggunaanChartEl = document.getElementById('penggunaan-bahan-baku-chart');
                if (!penggunaanChartEl) return;

                if (penggunaanData &&
                    penggunaanData.series_data &&
                    Array.isArray(penggunaanData.series_data) &&
                    penggunaanData.series_data.length > 0) {

                    try {
                        penggunaanChartEl.innerHTML = '';

                        const series = penggunaanData.series_data.map(item => ({
                            name: item.name,
                            data: item.data || []
                        }));

                        const chart = new ApexCharts(penggunaanChartEl, {
                            chart: {
                                type: 'bar',
                                height: 400,
                                stacked: true,
                                toolbar: {
                                    show: true,
                                    tools: {
                                        download: true,
                                        selection: true,
                                        zoom: true,
                                        zoomin: true,
                                        zoomout: true,
                                        pan: true,
                                        reset: true
                                    }
                                }
                            },
                            series: series,
                            xaxis: {
                                categories: penggunaanData.bulan_labels || [],
                                labels: {
                                    rotate: -45,
                                    style: {
                                        fontSize: '12px'
                                    }
                                },
                                title: {
                                    text: 'Bulan',
                                    style: {
                                        fontSize: '14px',
                                        fontWeight: 'bold'
                                    }
                                }
                            },
                            yaxis: {
                                title: {
                                    text: 'Jumlah Penggunaan',
                                    style: {
                                        fontSize: '14px',
                                        fontWeight: 'bold'
                                    }
                                },
                                labels: {
                                    formatter: function(val) {
                                        return val.toLocaleString('id-ID');
                                    }
                                }
                            },
                            colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0'],
                            legend: {
                                position: 'top',
                                horizontalAlign: 'center',
                                offsetY: 0
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val + ' unit';
                                    }
                                },
                                shared: true,
                                intersect: false
                            },
                            plotOptions: {
                                bar: {
                                    columnWidth: '70%',
                                    dataLabels: {
                                        position: 'top'
                                    }
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            grid: {
                                borderColor: '#f1f1f1',
                            },
                            responsive: [{
                                breakpoint: 768,
                                options: {
                                    chart: {
                                        height: 350
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }]
                        });

                        chart.render();
                        console.log('Chart penggunaan bahan baku berhasil dirender');
                    } catch (err) {
                        console.error('Error render penggunaan chart:', err);
                        penggunaanChartEl.innerHTML =
                            '<div class="no-data">Error: Gagal memuat grafik penggunaan bahan baku</div>';
                    }
                } else {
                    penggunaanChartEl.innerHTML = '<div class="no-data">Tidak ada data penggunaan bahan baku</div>';
                }
            }

            renderPenjualanChart();
            renderBahanBakuChart();
            renderPenggunaanBahanBakuChart();

            if (typeof $.fn.DataTable !== 'undefined' && $('.data-table').length) {
                $('.data-table').DataTable({
                    responsive: true,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Cari...",
                        lengthMenu: "_MENU_ item per halaman",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ item",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 item",
                        infoFiltered: "(disaring dari _MAX_ total item)",
                        zeroRecords: "Tidak ada data yang ditemukan",
                        paginate: {
                            previous: "<i class='icon-copy dw dw-left-arrow2'></i>",
                            next: "<i class='icon-copy dw dw-right-arrow2'></i>"
                        }
                    },
                    order: [
                        [0, 'asc']
                    ],
                    pageLength: 10,
                    lengthMenu: [
                        [5, 10, 25, 50, -1],
                        [5, 10, 25, 50, "Semua"]
                    ]
                });
            }

            console.log('Dashboard script selesai');
        });
    </script>
@endpush
