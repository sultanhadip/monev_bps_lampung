<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <i class="bi bi-list toggle-sidebar-btn me-3"></i>
        <a href="{{ route('dashboard') }}" class="logo d-flex align-items-center">
            <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" />
            <span class="d-none d-lg-block">Montify</span>
        </a>
    </div>
    <!-- End Logo -->

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
            <!-- Notifikasi sebagai nav-item -->
            <li class="nav-item me-3 position-relative dropdown">
                <button
                    id="notificationButton"
                    class="btn btn-link p-0 position-relative me-3"
                    title="Notifikasi Usulan Verifikasi"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-controls="notifDropdownMenu"
                    style="cursor: pointer;">
                    <i class="fas fa-bell fa-lg"></i>
                    <span
                        id="notificationBadge"
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        style="display:none; font-size: 0.75rem;"
                        aria-live="polite"
                        aria-atomic="true">
                        0
                        <span class="visually-hidden">notifikasi baru</span>
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end p-3 rounded-3"
                    aria-labelledby="notificationButton"
                    id="notifDropdownMenu"
                    style="min-width: 350px; max-width: 500px; overflow-y: auto;"> <!-- Update width values -->
                    <li id="notifUsulanContent" class="small text-wrap">
                        Memuat data...
                    </li>
                </ul>
            </li>

            <!-- Profile Nav Item -->
            <li class="nav-item dropdown pe-3">
                <a
                    class="nav-link nav-profile d-flex align-items-center pe-0"
                    href="#"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <img src="{{ asset('assets/img/user.png') }}" alt="User Profile" class="rounded-circle" />
                    <span class="d-none d-md-block dropdown-toggle ps-2">{{ Auth::user()->nama }}</span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>{{ Auth::user()->nama }}</h6>
                    </li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="GET">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
    <!-- End Icons Navigation -->
</header>