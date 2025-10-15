<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Bahan Baku - Akrilik')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vendors/images/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vendors/images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vendors/images/logo.png') }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/styles/core.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/styles/icon-font.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('src/plugins/datatables/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('src/plugins/datatables/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/styles/style.css') }}">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-success {
            background-color: #28a745;
            color: #fff;
        }

        .dropdown-menu-icon-list .dropdown-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .table img {
            border-radius: 5px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .data-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .dropdown-menu {
            z-index: 1001;
        }

        .modal-backdrop {
            z-index: 999;
        }

        .modal {
            z-index: 1000;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="pre-loader">
        <div class="pre-loader-box">
            <div style="width: 100px; height: 100px; margin-bottom: 1rem;"><img
                    src="{{ asset('vendors/images/logo.png') }}" alt="">
            </div>
            <div class='loader-progress' id="progress_div">
                <div class='bar' id='bar1'></div>
            </div>
            <div class='percent' id='percent1'>0%</div>
            <div class="loading-text">Loading...</div>
        </div>
    </div>

    @include('layoutsAPP.deskapp-header')
    @include('layoutsAPP.deskapp-sidebar')

    <div class="main-container">
        <div class="pd-ltr-20">
            @yield('content')
        </div>
    </div>

    @stack('modals')

    <script src="{{ asset('vendors/scripts/core.js') }}"></script>
    <script src="{{ asset('vendors/scripts/script.min.js') }}"></script>
    <script src="{{ asset('vendors/scripts/process.js') }}"></script>
    <script src="{{ asset('vendors/scripts/layout-settings.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script src="{{ asset('src/plugins/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/responsive.bootstrap4.min.js') }}"></script>

    @stack('scripts')
</body>

</html>
