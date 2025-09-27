@extends('layoutsAPP.deskapp')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="card-box pd-20 height-100-p mb-30">
        <h4 class="font-20 weight-500 mb-10 text-capitalize">
            Welcome back <span class="weight-600 font-30 text-blue">{{ Auth::user()->name }}</span>
        </h4>
        <p class="font-18 max-width-600">You are logged in as <strong>Admin</strong>.</p>
    </div>

    <!-- Card Stats -->
    <div class="row">
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-30">
            <div class="card-box height-100-p widget-style1">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="widget-data">
                        <div class="h4 mb-0 text-blue">15</div>
                        <div class="weight-600 font-14">Total Supplier</div>
                    </div>
                    <div class="widget-icon">
                        <div class="icon" data-color="#00eccf">
                            <i class="icon-copy dw dw-truck"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-30">
            <div class="card-box height-100-p widget-style1">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="widget-data">
                        <div class="h4 mb-0 text-success">Rp 25.500.000</div>
                        <div class="weight-600 font-14">Total Pembelian</div>
                    </div>
                    <div class="widget-icon">
                        <div class="icon" data-color="#ff5b5b">
                            <i class="icon-copy dw dw-shopping-bag"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-30">
            <div class="card-box height-100-p widget-style1">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="widget-data">
                        <div class="h4 mb-0 text-warning">Rp 38.750.000</div>
                        <div class="weight-600 font-14">Total Penjualan</div>
                    </div>
                    <div class="widget-icon">
                        <div class="icon" data-color="#09cc06">
                            <i class="icon-copy dw dw-money"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-30">
            <div class="card-box height-100-p pd-20">
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-0">
                    <div class="h5 mb-0">Grafik Penjualan dan Pembelian</div>
                    <div class="dropdown">
                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" data-color="#1b3133"
                            href="#" role="button" data-toggle="dropdown">
                            <i class="dw dw-more"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                            <a class="dropdown-item" href="#"><i class="dw dw-eye"></i> View</a>
                            <a class="dropdown-item" href="#"><i class="dw dw-edit2"></i> Edit</a>
                            <a class="dropdown-item" href="#"><i class="dw dw-delete-3"></i> Delete</a>
                        </div>
                    </div>
                </div>
                <div id="sales-purchase-chart" style="min-height: 400px;"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            var salesPurchaseData = {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                    'Dec'
                ],
                series: [{
                        name: 'Penjualan',
                        data: [2500000, 3200000, 2800000, 3500000, 4200000, 3800000, 4500000, 5000000,
                            4800000, 5200000, 5500000, 6000000
                        ]
                    },
                    {
                        name: 'Pembelian',
                        data: [1800000, 2200000, 2000000, 2800000, 3200000, 3000000, 3500000, 3800000,
                            3600000, 4000000, 4200000, 4500000
                        ]
                    }
                ]
            };

            if ($('#sales-purchase-chart').length) {
                var options = {
                    chart: {
                        type: 'line',
                        height: 400,
                        toolbar: {
                            show: true
                        }
                    },
                    series: salesPurchaseData.series,
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 5
                    },
                    colors: ['#1b00ff', '#ff0000'],
                    xaxis: {
                        categories: salesPurchaseData.categories
                    },
                    yaxis: {
                        labels: {
                            formatter: function(val) {
                                return 'Rp ' + (val / 1000000).toFixed(1) + ' jt';
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
                    legend: {
                        position: 'top'
                    }
                };

                var chart = new ApexCharts(document.querySelector("#sales-purchase-chart"), options);
                chart.render();
            }
        });
    </script>
@endsection
