<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — System ISPO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .ispo-mobile-header,
        .ispo-mobile-drawer,
        .ispo-mobile-overlay,
        .ispo-mobile-bottom-nav {
            display: none;
        }

        @media (max-width: 768px) {
            .ispo-desktop-nav {
                display: none;
            }

            .ispo-mobile-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: sticky;
                top: 0;
                z-index: 65;
                padding: 10px 12px;
                background: #fff;
                border-bottom: 1px solid #e5e7eb;
            }

            .ispo-mobile-header h2 {
                margin: 0;
                font-size: 14px;
                font-weight: 700;
            }

            .ispo-mobile-btn {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                border: 1px solid #d1d5db;
                background: #fff;
                color: #374151;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
            }

            .ispo-mobile-overlay {
                position: fixed;
                inset: 0;
                z-index: 70;
                background: rgba(15, 23, 42, 0.45);
            }

            .ispo-mobile-drawer {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: min(82vw, 320px);
                background: #fff;
                z-index: 80;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                padding: 16px 12px;
                overflow-y: auto;
            }

            .ispo-mobile-drawer.open { transform: translateX(0); }

            .ispo-mobile-drawer a {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 12px;
                border-radius: 10px;
                text-decoration: none;
                color: #374151;
                font-size: 13px;
                margin-bottom: 6px;
            }

            .ispo-mobile-drawer a.active,
            .ispo-mobile-drawer a:hover {
                background: #eef2ff;
                color: #4f46e5;
            }

            .ispo-mobile-bottom-nav {
                display: block;
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 68;
                background: #fff;
                border-top: 1px solid #e5e7eb;
                box-shadow: 0 -8px 18px rgba(15, 23, 42, 0.08);
            }

            .ispo-mobile-bottom-nav .inner {
                height: 62px;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
            }

            .ispo-mobile-bottom-nav a,
            .ispo-mobile-bottom-nav button {
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
            }

            .ispo-mobile-bottom-nav .active { color: #4f46e5; }

            main { padding: 14px 14px 90px !important; }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen antialiased">

    @include('components.impersonation-banner')
    @include('components.module-hub-button')

    <nav class="ispo-desktop-nav bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-3 flex items-center justify-between sticky top-0 z-50 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('modules.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Hub Modul
            </a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                System ISPO
            </span>
        </div>

        <div class="flex items-center gap-3">
            <span class="hidden sm:inline text-xs font-medium px-2.5 py-1 rounded-full
                {{ auth()->user()->moduleRole('ispo') === 'ISPO Admin'
                    ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                    : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' }}">
                {{ auth()->user()->moduleRole('ispo') ?? 'Viewer' }}
            </span>
            <span class="text-sm text-gray-600 dark:text-gray-400 font-medium">
                {{ auth()->user()->name }}
            </span>
        </div>
    </nav>

    <header class="ispo-mobile-header">
        <button type="button" class="ispo-mobile-btn" onclick="toggleIspoDrawer(true)" aria-label="Buka menu">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
        <h2>System ISPO</h2>
        <a href="{{ route('modules.index') }}" class="ispo-mobile-btn" aria-label="Hub modul">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </a>
    </header>

    <div id="ispoMobileOverlay" class="ispo-mobile-overlay" style="display:none;" onclick="toggleIspoDrawer(false)"></div>
    <aside id="ispoMobileDrawer" class="ispo-mobile-drawer" aria-hidden="true">
        <a href="{{ route('modules.index') }}"><span>Hub Modul</span></a>
        <a href="{{ route('ispo.index') }}" class="{{ request()->routeIs('ispo.index') ? 'active' : '' }}"><span>Daftar Dokumen</span></a>
        @if(auth()->user()->moduleRole('ispo') === 'ISPO Admin')
            <a href="{{ route('ispo.admin.items.index') }}" class="{{ request()->routeIs('ispo.admin.items.*') ? 'active' : '' }}"><span>Master Data ISPO</span></a>
        @endif
    </aside>

    <main class="p-6">
        @yield('content')
    </main>

    <nav class="ispo-mobile-bottom-nav" aria-label="ISPO mobile navigation">
        <div class="inner">
            <a href="{{ route('modules.index') }}"><span>Modul</span></a>
            <a href="{{ route('ispo.index') }}" class="{{ request()->routeIs('ispo.index') ? 'active' : '' }}"><span>Dokumen</span></a>
            @if(auth()->user()->moduleRole('ispo') === 'ISPO Admin')
                <a href="{{ route('ispo.admin.items.index') }}" class="{{ request()->routeIs('ispo.admin.items.*') ? 'active' : '' }}"><span>Master</span></a>
            @else
                <a href="{{ route('ispo.index') }}" class="{{ request()->routeIs('ispo.*') ? 'active' : '' }}"><span>Status</span></a>
            @endif
            <button type="button" onclick="toggleIspoDrawer(true)"><span>Menu</span></button>
        </div>
    </nav>

    <script>
        function toggleIspoDrawer(show) {
            const drawer = document.getElementById('ispoMobileDrawer');
            const overlay = document.getElementById('ispoMobileOverlay');

            if (!drawer || !overlay) {
                return;
            }

            drawer.classList.toggle('open', show);
            overlay.style.display = show ? 'block' : 'none';
            document.body.style.overflow = show ? 'hidden' : '';
        }
    </script>

</body>
</html>
