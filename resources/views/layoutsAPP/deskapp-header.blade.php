<div class="header">
    <div class="header-left">
        <div class="menu-icon dw dw-menu" id="mobile-menu-toggle"></div>
        {{-- <div class="mobile-logo" style="display: none;">
            <a href="{{ route('dashboard') }}">
                <img style="width: 80px; height: 60px;" src="{{ asset('vendors/images/logo.png') }}" alt=""
                    class="light-logo">
            </a>
        </div> --}}
    </div>
    <div class="header-right">
        <div class="user-info-dropdown">
            <div class="dropdown">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <span class="user-icon">
                        <img src="{{ asset('vendors/images/photo1.jpg') }}" alt="">
                    </span>
                    <span class="user-name">{{ Auth::user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="dw dw-logout"></i> Log Out
                        </a>
                    </form>
                </div>
            </div>
        </div>
        <div class="github-link">
        </div>
    </div>
</div>
