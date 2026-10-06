<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Rekap Berita Kemitraan') - Diskominfo Kab. Bogor</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @stack('styles')
</head>
<body>


    <div class="app-layout">
        <!-- Backdrop Overlay for Mobile Drawer -->
        <div id="sidebar-backdrop" class="sidebar-backdrop"></div>

        <!-- Sidebar Navigation (Navbar di samping) -->
        <aside id="app-sidebar" class="app-sidebar">
            <div class="sidebar-header">
                <a href="{{ route('dashboard') }}" class="sidebar-brand">
                    <img src="{{ asset('assets/logo_baru.png') }}" alt="Logo Diskominfo Kab. Bogor" class="sidebar-logo">
                    <div>
                        <div class="sidebar-brand-title">
                            DISKOMINFO
                            <span class="brand-tag">BOGOR</span>
                        </div>
                        <div class="sidebar-brand-subtitle">Rekap Berita Kemitraan</div>
                    </div>
                </a>
                <button type="button" id="btn-close-sidebar" class="btn-close-sidebar" aria-label="Tutup Menu">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="sidebar-nav-wrap">
                <div class="sidebar-group-title">Menu Utama</div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Beranda</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('scan.index') }}" class="sidebar-link sidebar-link-accent {{ request()->routeIs('scan.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-camera"></i>
                            <span>Pindai Satuan (1 Berita)</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('scan.tabel') }}" class="sidebar-link sidebar-link-accent {{ request()->routeIs('scan.tabel*') ? 'active' : '' }}">
                            <i class="fa-solid fa-table-cells"></i>
                            <span>Pindai Tabel Rekap</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('rekap.index') }}" class="sidebar-link {{ request()->routeIs('rekap.*') && !request()->routeIs('rekap.export') ? 'active' : '' }}">
                            <i class="fa-solid fa-table-list"></i>
                            <span>Data Rekap</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('rekap.export') }}" class="sidebar-link {{ request()->routeIs('rekap.export') ? 'active' : '' }}">
                            <i class="fa-solid fa-file-excel" style="color: var(--accent-green-dark);"></i>
                            <span>Ekspor Excel</span>
                        </a>
                    </li>
                </ul>

                <div class="sidebar-group-title" style="margin-top: 24px;">Tautan Resmi</div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="https://diskominfo.bogorkab.go.id" target="_blank" class="sidebar-link">
                            <i class="fa-solid fa-globe"></i>
                            <span>Portal Diskominfo</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://bogorkab.go.id" target="_blank" class="sidebar-link">
                            <i class="fa-solid fa-building-columns"></i>
                            <span>Portal Kab. Bogor</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- User Status & Logout -->
            <div class="sidebar-footer">
                @auth
                <div class="sidebar-user-card">
                    <div class="sidebar-user-avatar">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name">{{ Str::limit(Auth::user()->name, 16) }}</div>
                        <div class="sidebar-user-role">Petugas Diskominfo</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin keluar?');">
                    @csrf
                    <button type="submit" class="sidebar-logout-btn">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
                @endauth
            </div>
        </aside>

        <!-- Main Content Body -->
        <div class="app-body">
            <!-- App Topbar with Hamburger Toggle for Mobile -->
            <header class="app-topbar">
                <div class="topbar-left">
                    <button type="button" id="btn-toggle-sidebar" class="btn-hamburger" aria-label="Buka Menu Navigasi">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="mobile-brand">
                        <span>DISKOMINFO</span>
                        <span class="brand-tag">KAB. BOGOR</span>
                    </div>
                    <div class="desktop-breadcrumb">
                        <span>@yield('breadcrumb', 'Sistem Rekapitulasi Berita Kemitraan Diskominfo')</span>
                    </div>
                </div>

                <div class="topbar-right" style="display: flex; gap: 8px;">
                    <a href="{{ route('scan.index') }}" class="btn btn-sm btn-outline">
                        <i class="fa-solid fa-camera"></i> <span class="hide-mobile">Pindai Satuan</span>
                    </a>
                    <a href="{{ route('scan.tabel') }}" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-table-cells"></i> <span class="hide-mobile">Pindai Tabel</span>
                    </a>
                </div>
            </header>

            <!-- Main Content -->
            <main class="app-content">
                @if (session('success'))
                    <div class="alert alert-success">
                        <span><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</span>
                        <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; font-size:1.1rem; color:inherit;">&times;</button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        <span><i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}</span>
                        <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; font-size:1.1rem; color:inherit;">&times;</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <div>
                            <strong><i class="fa-solid fa-circle-xmark"></i> Terjadi kesalahan:</strong>
                            <ul style="margin-left: 20px; margin-top: 4px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Compact Clean Footer -->
            <footer class="app-footer">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>&copy; {{ date('Y') }} <strong>Diskominfo Kab. Bogor</strong> &bull; Sistem Rekapitulasi Berita Kemitraan</div>
                    <div style="font-size: 0.76rem;">
                        Didukung oleh <strong>Gemini Flash Vision AI</strong>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Mobile Sidebar Drawer Script -->
    <script>
        const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');
        const btnCloseSidebar = document.getElementById('btn-close-sidebar');
        const sidebarBackdrop = document.getElementById('sidebar-backdrop');

        function openMobileSidebar() {
            document.body.classList.add('sidebar-open');
        }

        function closeMobileSidebar() {
            document.body.classList.remove('sidebar-open');
        }

        if (btnToggleSidebar) btnToggleSidebar.addEventListener('click', openMobileSidebar);
        if (btnCloseSidebar) btnCloseSidebar.addEventListener('click', closeMobileSidebar);
        if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeMobileSidebar);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeMobileSidebar();
        });
    </script>

    @stack('scripts')
</body>
</html>

