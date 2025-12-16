<div class="left-side-bar">
    <div class="brand-logo">
        <a href="{{ route('dashboard') }}">
            <img style="width: 100px; height: 80px; display: block; margin: 0 auto;"
                src="{{ asset('vendors/images/logo.png') }}" alt="" class="light-logo">
        </a>
        <div class="close-sidebar" data-toggle="left-sidebar-close">
            <i class="ion-close-round"></i>
        </div>
    </div>
    <div class="menu-block customscroll">
        <div class="sidebar-menu">
            <ul id="accordion-menu">
                @if (Auth::user()->role === 'admin')
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <span class="micon dw dw-house-1"></span><span class="mtext">Dashboard Admin</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.bahan-baku.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('admin.bahan-baku.*') ? 'active' : '' }}">
                            <span class="micon dw dw-box"></span><span class="mtext">Bahan Baku</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.produk.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('admin.produk.*') ? 'active' : '' }}">
                            <span class="micon dw dw-package"></span><span class="mtext">Produk</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.pembelian.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('admin.pembelian.*') ? 'active' : '' }}">
                            <span class="micon dw dw-shopping-bag"></span><span class="mtext">Pembelian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.penjualan.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('admin.penjualan.*') ? 'active' : '' }}">
                            <span class="micon dw dw-money"></span><span class="mtext">Penjualan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.supplier.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('admin.supplier.*') ? 'active' : '' }}">
                            <span class="micon dw dw-truck"></span><span class="mtext">Supplier</span>
                        </a>
                    </li>
                @elseif(Auth::user()->role === 'owner')
                    <li>
                        <a href="{{ route('owner.dashboard') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                            <span class="micon dw dw-house-1"></span><span class="mtext">Dashboard Owner</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.bahan-baku.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('owner.bahan-baku.*') ? 'active' : '' }}">
                            <span class="micon dw dw-box"></span><span class="mtext">Bahan Baku</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.produk.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('owner.produk.*') ? 'active' : '' }}">
                            <span class="micon dw dw-package"></span><span class="mtext">Produk</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.pembelian.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('owner.pembelian.*') ? 'active' : '' }}">
                            <span class="micon dw dw-shopping-bag"></span><span class="mtext">Pembelian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.penjualan.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('owner.penjualan.*') ? 'active' : '' }}">
                            <span class="micon dw dw-money"></span><span class="mtext">Penjualan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.supplier.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('owner.supplier.*') ? 'active' : '' }}">
                            <span class="micon dw dw-truck"></span><span class="mtext">Supplier</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.users.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('owner.users.*') ? 'active' : '' }}">
                            <span class="micon dw dw-user1"></span><span class="mtext">Manajemen User</span>
                        </a>
                    </li>
                @endif

                @if (Auth::user()->role === 'kasir')
                    <li>
                        <a href="{{ route('kasir.dashboard') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('kasir.dashboard') ? 'active' : '' }}">
                            <span class="micon dw dw-house-1"></span><span class="mtext">Dashboard Kasir</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('kasir.penjualan.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('kasir.penjualan.*') ? 'active' : '' }}">
                            <span class="micon dw dw-money"></span><span class="mtext">Penjualan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('kasir.produk.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('kasir.produk.*') ? 'active' : '' }}">
                            <span class="micon dw dw-package"></span><span class="mtext">Produk</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
<div class="mobile-menu-overlay"></div>

<style>
    .left-side-bar .sidebar-menu li a.active {
        background-color: rgba(94, 186, 125, 0.15);
        color: #5eba7d;
        border-left: 4px solid #5eba7d;
    }

    .left-side-bar .sidebar-menu li a.active .micon,
    .left-side-bar .sidebar-menu li a.active .mtext {
        color: #5eba7d;
    }

    .left-side-bar .sidebar-menu li a:hover {
        background-color: rgba(94, 186, 125, 0.1);
    }

    .sidebar-menu li a {
        transition: all 0.3s ease;
        position: relative;
    }

    .sidebar-menu li a.active:before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background-color: #5eba7d;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('#accordion-menu a');

        menuLinks.forEach(link => {
            const linkPath = link.getAttribute('href');
            link.classList.remove('active');

            if (currentPath.startsWith(linkPath.replace(/\/\d+$/, ''))) {
                link.classList.add('active');

                const parentLi = link.closest('li');
                if (parentLi.classList.contains('dropdown')) {
                    parentLi.classList.add('show');
                    const dropdownMenu = parentLi.querySelector('.dropdown-menu');
                    if (dropdownMenu) {
                        dropdownMenu.classList.add('show');
                    }
                }
            }
        });

        document.querySelector('.mobile-menu-overlay')?.addEventListener('click', function() {
            document.querySelector('.left-side-bar')?.classList.remove('show');
        });

        document.querySelector('.close-sidebar')?.addEventListener('click', function() {
            document.querySelector('.left-side-bar')?.classList.remove('show');
        });

        if (window.innerWidth < 768) {
            document.querySelectorAll('#accordion-menu a').forEach(link => {
                link.addEventListener('click', function() {
                    document.querySelector('.left-side-bar')?.classList.remove('show');
                });
            });
        }
    });
</script>
