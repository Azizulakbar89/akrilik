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
                        <a href="{{ route('admin.dashboard') }}" class="dropdown-toggle no-arrow">
                            <span class="micon dw dw-house-1"></span><span class="mtext">Dashboard Admin</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.bahan-baku.index') }}" class="dropdown-toggle no-arrow">
                            <span class="micon dw dw-box"></span><span class="mtext">Bahan Baku</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.produk.index') }}" class="dropdown-toggle no-arrow">
                            <span class="micon dw dw-package"></span><span class="mtext">Produk</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.pembelian.index') }}" class="dropdown-toggle no-arrow">
                            <span class="micon dw dw-shopping-bag"></span><span class="mtext">Pembelian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.penjualan.index') }}" class="dropdown-toggle no-arrow">
                            <span class="micon dw dw-money"></span><span class="mtext">Penjualan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.supplier.index') }}" class="dropdown-toggle no-arrow">
                            <span class="micon dw dw-truck"></span><span class="mtext">Supplier</span>
                        </a>
                    </li>
                @elseif(Auth::user()->role === 'owner')
                    <li>
                        <a href="{{ route('owner.dashboard') }}" class="dropdown-toggle no-arrow">
                            <span class="micon dw dw-house-1"></span><span class="mtext">Dashboard Owner</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.bahan-baku.index') }}" class="dropdown-toggle no-arrow">
                            <span class="micon dw dw-box"></span><span class="mtext">Bahan Baku</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.produk.index') }}" class="dropdown-toggle no-arrow">
                            <span class="micon dw dw-package"></span><span class="mtext">Produk</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.pembelian.index') }}" class="dropdown-toggle no-arrow">
                            <span class="micon dw dw-shopping-bag"></span><span class="mtext">Pembelian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.penjualan.index') }}" class="dropdown-toggle no-arrow">
                            <span class="micon dw dw-money"></span><span class="mtext">Penjualan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.supplier.index') }}" class="dropdown-toggle no-arrow">
                            <span class="micon dw dw-truck"></span><span class="mtext">Supplier</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
<div class="mobile-menu-overlay"></div>
