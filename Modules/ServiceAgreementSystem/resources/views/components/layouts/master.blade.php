<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - Service Agreement System</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --bg-card-hover: #f1f5f9;
            --bg-input: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --accent-light: rgba(37, 99, 235, 0.08);
            --success: #059669;
            --success-bg: #ecfdf5;
            --warning: #d97706;
            --warning-bg: #fffbeb;
            --danger: #dc2626;
            --danger-bg: #fef2f2;
            --info: #0891b2;
            --info-bg: #ecfeff;
            --sidebar-width: 260px;
            --radius: 12px;
            --transition: all 0.2s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            -webkit-font-smoothing: antialiased;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: #ffffff; /* Elegant white/grey sidebar */
            border-right: 1px solid var(--border-color);
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            z-index: 50;
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-logo-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            color: #fff;
        }

        .sidebar-logo-text {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .sidebar-logo-sub {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 400;
        }

        .sidebar-nav {
            flex: 1;
            padding: 24px 12px;
            overflow-y: auto;
        }

        .sidebar-section {
            margin-bottom: 28px;
        }

        .sidebar-section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            padding: 0 16px;
            margin-bottom: 12px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            margin-bottom: 4px;
        }

        .sidebar-link:hover {
            background: var(--bg-primary);
            color: var(--accent);
        }

        .sidebar-link.active {
            background: var(--accent-light);
            color: var(--accent);
        }

        .sidebar-link i {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }

        .sidebar-user {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            background: #fdfdfd;
        }

        .sidebar-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), #6366f1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            color: #fff;
        }

        .sidebar-user-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .sidebar-user-role {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-height: 100vh;
        }

        .mobile-header,
        .mobile-overlay,
        .mobile-bottom-nav {
            display: none;
        }

        .top-bar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .top-bar-title {
            font-size: 18px;
            font-weight: 700;
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-content {
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .card-header {
            padding: 20px 28px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fdfdfd;
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
        }

        .card-body {
            padding: 24px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
        }

        .btn-success {
            background: var(--success);
            color: #fff;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-secondary {
            background: var(--bg-card-hover);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            color: var(--accent);
            border-color: var(--accent);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-lg {
            padding: 12px 24px;
            font-size: 14px;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: #fff;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: #fff;
        }

        .form-control::placeholder {
            color: #cbd5e1;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 18px;
            padding-right: 40px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }

        .form-error {
            color: var(--danger);
            font-size: 12px;
            margin-top: 4px;
        }

        .required::after {
            content: ' *';
            color: var(--danger);
        }

        /* Tables */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead th {
            text-align: left;
            padding: 12px 16px;
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }

        table tbody td {
            padding: 14px 16px;
            font-size: 13.5px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-secondary);
        }

        table tbody tr:hover {
            background: #f8fafc;
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-draft { background: #f1f5f9; color: #64748b; }
        .badge-submitted { background: #fffbeb; color: #d97706; }
        .badge-in_review { background: #f5f3ff; color: #7c3aed; }
        .badge-approved { background: #ecfdf5; color: #059669; }
        .badge-rejected { background: #fef2f2; color: #dc2626; }
        .badge-pending { background: #fff7ed; color: #ea580c; }
        .badge-on_hold { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 20px;
            transition: var(--transition);
        }

        .stat-card:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-bottom: 12px;
        }

        .stat-value {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 14px;
        }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: var(--success-bg);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .alert-error {
            background: var(--danger-bg);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert-info {
            background: var(--info-bg);
            color: var(--info);
            border: 1px solid rgba(6, 182, 212, 0.2);
        }

        /* Pagination */
        .pagination-wrapper {
            padding: 16px 24px;
            border-top: 1px solid var(--border-color);
        }

        .pagination-wrapper nav > div:first-child {
            display: none;
        }

        .pagination-wrapper span,
        .pagination-wrapper a {
            color: var(--text-secondary) !important;
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-color);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-dot {
            position: absolute;
            left: -25px;
            top: 4px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--border-color);
        }

        .timeline-dot.approved { background: var(--success); }
        .timeline-dot.rejected { background: var(--danger); }
        .timeline-dot.pending { background: var(--warning); }

        .timeline-content {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 14px;
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .timeline-name {
            font-weight: 600;
            font-size: 13px;
        }

        .timeline-date {
            font-size: 11px;
            color: var(--text-muted);
        }

        .timeline-comment {
            font-size: 12.5px;
            color: var(--text-secondary);
            margin-top: 6px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            html,
            body {
                width: 100%;
                max-width: 100%;
                overflow-x: hidden;
            }

            body {
                display: block;
            }

            .mobile-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: sticky;
                top: 0;
                z-index: 65;
                background: #fff;
                border-bottom: 1px solid var(--border-color);
                padding: 12px 16px;
            }

            .mobile-header-title {
                font-size: 14px;
                font-weight: 700;
            }

            .mobile-menu-btn {
                border: 1px solid var(--border-color);
                background: #fff;
                color: var(--text-primary);
                border-radius: 10px;
                width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            }

            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                z-index: 80;
                box-shadow: 0 18px 35px rgba(15, 23, 42, 0.25);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .mobile-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.45);
                z-index: 70;
            }

            .mobile-overlay.show {
                display: block;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
                overflow-x: hidden;
            }

            .top-bar { display: none; }

            .page-content {
                max-width: 100%;
                overflow-x: hidden;
                padding: 16px 12px calc(78px + env(safe-area-inset-bottom));
            }

            .form-row { grid-template-columns: 1fr; }

            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .mobile-bottom-nav {
                display: block !important;
                position: fixed !important;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100vw;
                z-index: 75;
                background: #fff;
                border-top: 1px solid var(--border-color);
                box-shadow: 0 -8px 18px rgba(15, 23, 42, 0.08);
                padding-bottom: env(safe-area-inset-bottom);
            }

            .mobile-bottom-nav-inner {
                height: 62px;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
            }

            .mobile-bottom-nav a,
            .mobile-bottom-nav button {
                border: 0;
                background: transparent;
                color: #64748b;
                text-decoration: none;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 3px;
                font-size: 10px;
                font-weight: 700;
                cursor: pointer;
            }

            .mobile-bottom-nav .active {
                color: var(--accent);
            }
        }

        /* Utility */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: var(--text-muted); }
        .mb-0 { margin-bottom: 0; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-4 { margin-top: 16px; }
        .gap-2 { gap: 8px; }
        .d-flex { display: flex; }
        .align-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .flex-wrap { flex-wrap: wrap; }
    </style>
    @stack('styles')
</head>
<body>
    @include('components.impersonation-banner')
    @include('components.module-hub-button')
    <div class="mobile-overlay" id="sasMobileOverlay" onclick="toggleSasSidebar(false)"></div>

    {{-- Sidebar --}}
    <aside class="sidebar" id="sasSidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon">SA</div>
                <div>
                    <div class="sidebar-logo-text">SAS</div>
                    <div class="sidebar-logo-sub">Service Agreement System</div>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Menu Utama</div>
                <a href="{{ route('sas.dashboard') }}" class="sidebar-link {{ request()->routeIs('sas.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Master Data</div>
                <a href="{{ route('sas.contractors.index') }}" class="sidebar-link {{ request()->routeIs('sas.contractors.*') ? 'active' : '' }}">
                    <i class="fas fa-hard-hat"></i> Kontraktor
                </a>
                <a href="{{ route('sas.approval-schemas.index') }}" class="sidebar-link {{ request()->routeIs('sas.approval-schemas.*') ? 'active' : '' }}">
                    <i class="fas fa-sitemap"></i> Approval Schema
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">USPK</div>
                <a href="{{ route('sas.uspk.index') }}" class="sidebar-link {{ request()->routeIs('sas.uspk.*') && !request()->routeIs('sas.uspk-approvals.*') ? 'active' : '' }}">
                    <i class="fas fa-file-signature"></i> Pengajuan USPK
                </a>
                <a href="{{ route('sas.uspk-approvals.index') }}" class="sidebar-link {{ request()->routeIs('sas.uspk-approvals.*') ? 'active' : '' }}">
                    <i class="fas fa-check-double"></i> Persetujuan Saya
                </a>
            </div>
        </nav>

        <div class="sidebar-user">
            <div class="sidebar-user-info">
                <div class="sidebar-user-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <div>
                    <div class="sidebar-user-name">{{ auth()->user()->name ?? 'User' }}</div>
                    <div class="sidebar-user-role">{{ auth()->user()->position ?? 'Staff' }}</div>
                </div>
                <form action="{{ route('sas.logout') }}" method="POST" style="margin-left: auto;">
                    @csrf
                    <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 14px;" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <main class="main-content">
        <div class="mobile-header">
            <button type="button" class="mobile-menu-btn" onclick="toggleSasSidebar(true)" aria-label="Buka menu navigasi">
                <i class="fas fa-bars"></i>
            </button>
            <div class="mobile-header-title">{{ $title ?? 'Dashboard' }}</div>
            <a href="{{ route('modules.index') }}" class="mobile-menu-btn" aria-label="Kembali ke Hub Modul">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>

        <div class="top-bar">
            <div class="top-bar-title">{{ $title ?? 'Dashboard' }}</div>
            <div class="top-bar-actions">
                @stack('actions')
            </div>
        </div>

        <div class="page-content">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>

    <nav class="mobile-bottom-nav" aria-label="SAS mobile navigation">
        <div class="mobile-bottom-nav-inner">
            <a href="{{ route('modules.index') }}"><i class="fas fa-th-large"></i><span>Modul</span></a>
            <a href="{{ route('sas.dashboard') }}" class="{{ request()->routeIs('sas.dashboard') ? 'active' : '' }}"><i class="fas fa-chart-pie"></i><span>Dashboard</span></a>
            <a href="{{ route('sas.uspk.index') }}" class="{{ request()->routeIs('sas.uspk.*') ? 'active' : '' }}"><i class="fas fa-file-signature"></i><span>USPK</span></a>
            <button type="button" onclick="toggleSasSidebar(true)"><i class="fas fa-bars"></i><span>Menu</span></button>
        </div>
    </nav>

    <script>
        // CSRF token setup for AJAX
        window.csrfToken = '{{ csrf_token() }}';

        function toggleSasSidebar(show) {
            const sidebar = document.getElementById('sasSidebar');
            const overlay = document.getElementById('sasMobileOverlay');

            if (!sidebar || !overlay) {
                return;
            }

            sidebar.classList.toggle('open', show);
            overlay.classList.toggle('show', show);
            document.body.style.overflow = show ? 'hidden' : '';
        }

        // Number formatting helper
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        function formatCurrency(num) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
        }
    </script>
    @stack('scripts')
</body>
</html>
