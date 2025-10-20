@extends('layoutsAPP.deskapp')

@section('title', 'Owner Dashboard')

@section('content')
    <div class="card-box pd-20 height-100-p mb-30">
        <h4 class="font-20 weight-500 mb-10 text-capitalize">
            Welcome back <span class="weight-600 font-30 text-blue">{{ Auth::user()->name }}</span>
        </h4>
        <p class="font-18 max-width-600">You are logged in as <strong>Owner</strong>. Here's your business overview.</p>
    </div>

    <div class="row">
        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-30">
            <div class="card-box height-100-p pd-20">
                <h5 class="mb-3">Grafik Pembelian Bahan Baku (6 Bulan Terakhir)</h5>
                <div id="pembelian-chart" class="chart-container"></div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-30">
            <div class="card-box height-100-p pd-20">
                <h5 class="mb-3">Grafik Penjualan Bahan Baku (6 Bulan Terakhir)</h5>
                <div id="penjualan-bahan-baku-chart" class="chart-container"></div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-30">
            <div class="card-box height-100-p pd-20">
                <h5 class="mb-3">Grafik Penjualan Produk (6 Bulan Terakhir)</h5>
                <div id="penjualan-produk-chart" class="chart-container"></div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-30">
            <div class="card-box height-100-p pd-20">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="mt-3">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <button class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-bahan-baku">
                                <i class="dw dw-box1 mr-2"></i>Lihat Bahan Baku
                            </button>
                        </div>
                        <div class="col-6 mb-3">
                            <button class="btn btn-success btn-block" data-toggle="modal" data-target="#modal-produk">
                                <i class="dw dw-shopping-bag mr-2"></i>Lihat Produk
                            </button>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('owner.pembelian.index') }}" class="btn btn-warning btn-block">
                                <i class="dw dw-money mr-2"></i>Kelola Pembelian
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('owner.penjualan.index') }}" class="btn btn-info btn-block">
                                <i class="dw dw-analytics-1 mr-2"></i>Kelola Penjualan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-bahan-baku" tabindex="-1" role="dialog" aria-labelledby="modalBahanBakuLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBahanBakuLabel">Data Bahan Baku</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Stok</th>
                                    <th>Min</th>
                                    <th>Max</th>
                                    <th>Safety Stock</th>
                                    <th>ROP</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (App\Models\BahanBaku::all() as $bahan)
                                    <tr>
                                        <td>{{ $bahan->nama }}</td>
                                        <td>{{ $bahan->stok }} {{ $bahan->satuan }}</td>
                                        <td>{{ $bahan->min }} {{ $bahan->satuan }}</td>
                                        <td>{{ $bahan->max }} {{ $bahan->satuan }}</td>
                                        <td>{{ $bahan->safety_stock }} {{ $bahan->satuan }}</td>
                                        <td>{{ $bahan->rop }} {{ $bahan->satuan }}</td>
                                        <td>{!! $bahan->status_stok !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <a href="{{ route('owner.bahan-baku.index') }}" class="btn btn-primary">Kelola Bahan Baku</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-produk" tabindex="-1" role="dialog" aria-labelledby="modalProdukLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProdukLabel">Data Produk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Stok</th>
                                    <th>Satuan</th>
                                    <th>Harga</th>
                                    <th>HPP</th>
                                    <th>Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (App\Models\Produk::all() as $produk)
                                    <tr>
                                        <td>{{ $produk->nama }}</td>
                                        <td>{{ $produk->stok }}</td>
                                        <td>{{ $produk->satuan }}</td>
                                        <td>Rp {{ number_format($produk->harga, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($produk->hpp, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge badge-success">
                                                Rp {{ number_format($produk->margin, 0, ',', '.') }}
                                                ({{ $produk->margin_persen }}%)
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <a href="{{ route('owner.produk.index') }}" class="btn btn-primary">Kelola Produk</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM fully loaded, initializing charts...');

            if (typeof ApexCharts === 'undefined') {
                console.error('ApexCharts is not loaded!');
                showChartError(
                    'ApexCharts library failed to load. Please check your internet connection or CDN availability.'
                );
                return;
            }

            initializeCharts();
        });

        function initializeCharts() {
            console.log('Initializing charts...');

            try {
                const pembelianData = @json($grafikPembelian ?? []);
                const penjualanBahanBakuData = @json($grafikPenjualanBahanBaku ?? []);
                const penjualanProdukData = @json($grafikPenjualanProduk ?? []);

                console.log('Chart data received:', {
                    pembelianData,
                    penjualanBahanBakuData,
                    penjualanProdukData
                });

                const sampleMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
                const sampleData = [1000000, 1500000, 1200000, 1800000, 2000000, 1700000];

                const chartContainers = {
                    pembelian: document.querySelector("#pembelian-chart"),
                    penjualanBahanBaku: document.querySelector("#penjualan-bahan-baku-chart"),
                    penjualanProduk: document.querySelector("#penjualan-produk-chart")
                };

                for (const [key, container] of Object.entries(chartContainers)) {
                    if (!container) {
                        console.error(`Chart container for ${key} not found!`);
                        showChartError(`Chart container for ${key} is missing.`, key);
                        return;
                    }
                }

                const yAxisFormatter = (val) => {
                    if (val >= 1000000) return `Rp ${(val / 1000000).toFixed(1)} jt`;
                    if (val >= 1000) return `Rp ${(val / 1000).toFixed(0)} rb`;
                    return `Rp ${val}`;
                };

                const charts = [{
                        id: 'pembelian-chart',
                        config: {
                            chart: {
                                type: 'line',
                                height: 300,
                                toolbar: {
                                    show: false
                                }
                            },
                            series: [{
                                name: 'Total Pembelian',
                                data: pembelianData.data?.length ? pembelianData.data : sampleData
                            }],
                            xaxis: {
                                categories: pembelianData.bulan?.length ? pembelianData.bulan : sampleMonths
                            },
                            yaxis: {
                                labels: {
                                    formatter: yAxisFormatter
                                }
                            },
                            colors: ['#1b00ff'],
                            stroke: {
                                curve: 'smooth',
                                width: 3
                            },
                            markers: {
                                size: 5
                            }
                        }
                    },
                    {
                        id: 'penjualan-bahan-baku-chart',
                        config: {
                            chart: {
                                type: 'bar',
                                height: 300,
                                toolbar: {
                                    show: false
                                }
                            },
                            series: [{
                                name: 'Penjualan Bahan Baku',
                                data: penjualanBahanBakuData.data?.length ? penjualanBahanBakuData.data :
                                    sampleData
                            }],
                            xaxis: {
                                categories: penjualanBahanBakuData.bulan?.length ? penjualanBahanBakuData.bulan :
                                    sampleMonths
                            },
                            yaxis: {
                                labels: {
                                    formatter: yAxisFormatter
                                }
                            },
                            colors: ['#00eccf']
                        }
                    },
                    {
                        id: 'penjualan-produk-chart',
                        config: {
                            chart: {
                                type: 'area',
                                height: 300,
                                toolbar: {
                                    show: false
                                }
                            },
                            series: [{
                                name: 'Penjualan Produk',
                                data: penjualanProdukData.data?.length ? penjualanProdukData.data : sampleData
                            }],
                            xaxis: {
                                categories: penjualanProdukData.bulan?.length ? penjualanProdukData.bulan : sampleMonths
                            },
                            yaxis: {
                                labels: {
                                    formatter: yAxisFormatter
                                }
                            },
                            colors: ['#09cc06'],
                            stroke: {
                                curve: 'smooth',
                                width: 3
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.7,
                                    opacityTo: 0.3
                                }
                            }
                        }
                    }
                ];

                charts.forEach(chart => {
                    const chartInstance = new ApexCharts(document.querySelector(`#${chart.id}`), chart.config);
                    chartInstance.render();
                    console.log(`Chart ${chart.id} rendered successfully`);
                });

            } catch (error) {
                console.error('Error rendering charts:', error);
                showChartError(`Error rendering charts: ${error.message}`);
            }
        }

        function showChartError(message, specificChartId = null) {
            const containers = specificChartId ? [document.querySelector(`#${specificChartId}`)] :
                document.querySelectorAll('.chart-container');

            containers.forEach(container => {
                if (container) {
                    container.innerHTML = `<div class="chart-error">${message}</div>`;
                }
            });
        }
    </script>
@endsection
