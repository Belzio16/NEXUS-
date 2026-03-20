<!DOCTYPE html>
<html lang="pt" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NEXUS') — Gestão de Produtos | Jaraujo © 2024</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/nexus.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="nx-sidebar" id="sidebar">
    <div class="nx-sidebar-brand">
        <div class="nx-logo">
            <span class="nx-logo-icon"><i class="bi bi-hexagon-fill"></i></span>
            <div>
                <div class="nx-logo-name">NEXUS</div>
                <div class="nx-logo-sub">by Jaraujo · Product Mgmt</div>
            </div>
        </div>
        <button class="nx-sidebar-toggle d-lg-none" id="sidebarClose">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="nx-nav">
        <div class="nx-nav-section">Principal</div>
        <a href="{{ route('dashboard') }}" class="nx-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="nx-nav-icon"><i class="bi bi-grid-1x2-fill"></i></span>
            <span>Dashboard</span>
            @if(request()->routeIs('dashboard'))
                <span class="nx-nav-dot"></span>
            @endif
        </a>

        <div class="nx-nav-section">Catálogo</div>
        <a href="{{ route('products.index') }}" class="nx-nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <span class="nx-nav-icon"><i class="bi bi-box-seam-fill"></i></span>
            <span>Produtos</span>
            @if(request()->routeIs('products.*'))
                <span class="nx-nav-dot"></span>
            @endif
        </a>
        <a href="{{ route('products.create') }}" class="nx-nav-item {{ request()->routeIs('products.create') ? 'active' : '' }}">
            <span class="nx-nav-icon"><i class="bi bi-plus-square-fill"></i></span>
            <span>Novo Produto</span>
        </a>

        <div class="nx-nav-section">API</div>
        <a href="/api/v1/products" target="_blank" class="nx-nav-item">
            <span class="nx-nav-icon"><i class="bi bi-braces"></i></span>
            <span>REST API</span>
            <i class="bi bi-arrow-up-right-square ms-auto opacity-50" style="font-size:.65rem"></i>
        </a>
    </nav>

    <div class="nx-sidebar-footer">
        <div class="nx-user-card">
            <div class="nx-user-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div>
                <div class="nx-user-name">Administrador</div>
                <div class="nx-user-role">Gestor de Catálogo</div>
            </div>
        </div>
    </div>
</aside>

<!-- ══ OVERLAY ══ -->
<div class="nx-overlay" id="overlay"></div>

<!-- ══ MAIN CONTENT ══ -->
<div class="nx-wrapper">

    <!-- Topbar -->
    <header class="nx-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="nx-menu-btn" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <!-- Live Search -->
            <div class="nx-search-wrap" id="searchWrap">
                <i class="bi bi-search nx-search-icon"></i>
                <input type="text" class="nx-search-input" id="liveSearch"
                       placeholder="Pesquisar produtos, SKU…"
                       autocomplete="off">
                <kbd class="nx-search-kbd">⌘K</kbd>
                <div class="nx-search-results" id="searchResults"></div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button class="nx-topbar-btn position-relative" id="notifBtn">
                <i class="bi bi-bell-fill"></i>
                <span class="nx-notif-badge">3</span>
            </button>
            <a href="{{ route('products.create') }}" class="nx-btn-neon">
                <i class="bi bi-plus-lg"></i>
                <span class="d-none d-md-inline">Novo Produto</span>
            </a>
        </div>
    </header>

    <!-- Page Content -->
    <main class="nx-content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer style="text-align:center;padding:16px 24px;font-size:11px;color:var(--nx-txt-3);border-top:1px solid var(--nx-border);margin-top:auto">
        NEXUS · Desenvolvido por <strong style="color:var(--nx-neon)">Jaraujo</strong> · Inovação em Gestão de Produtos © 2024
    </footer>

</div><!-- /.nx-wrapper -->

<!-- ══ TOAST CONTAINER ══ -->
<div class="nx-toast-container" id="toastContainer"></div>

<!-- ══ NOTIFICATION PANEL ══ -->
<div class="nx-notif-panel" id="notifPanel">
    <div class="nx-notif-header">
        <span>Notificações</span>
        <button class="nx-notif-close" id="notifClose"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="nx-notif-body">
        <div class="nx-notif-item unread">
            <div class="nx-notif-dot" style="background:#f472b6"></div>
            <div>
                <div class="nx-notif-title">Stock crítico</div>
                <div class="nx-notif-desc">Apple Watch Series 9 — apenas 1 unidade</div>
                <div class="nx-notif-time">há 2 min</div>
            </div>
        </div>
        <div class="nx-notif-item unread">
            <div class="nx-notif-dot" style="background:#fb923c"></div>
            <div>
                <div class="nx-notif-title">Produto sem stock</div>
                <div class="nx-notif-desc">iPad Pro M2 — stock esgotado</div>
                <div class="nx-notif-time">há 15 min</div>
            </div>
        </div>
        <div class="nx-notif-item">
            <div class="nx-notif-dot" style="background:#22d3ee"></div>
            <div>
                <div class="nx-notif-title">Novo produto adicionado</div>
                <div class="nx-notif-desc">Razer DeathAdder V3 foi criado</div>
                <div class="nx-notif-time">há 1 hora</div>
            </div>
        </div>
    </div>
</div>

<!-- ══ SESSION TOAST ══ -->
@if(session('toast'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        showToast({!! json_encode(session('toast')) !!});
    });
</script>
@endif

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/nexus.js') }}"></script>

@stack('scripts')
</body>
</html>
