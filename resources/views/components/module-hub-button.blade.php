@if(auth()->check() && Route::has('modules.index') && !request()->routeIs('modules.index'))
    <style>
        .module-hub-fab {
            position: fixed;
            bottom: 84px;
            left: 14px;
            z-index: 9998;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0f172a;
            color: #ffffff;
            border-radius: 999px;
            padding: 9px 12px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.3);
            border: 1px solid rgba(15, 23, 42, 0.25);
        }

        .module-hub-fab:hover {
            background: #1e293b;
        }

        .module-hub-fab .label-mobile { display: inline; }
        .module-hub-fab .label-desktop { display: none; }

        @media (min-width: 768px) {
            .module-hub-fab {
                left: auto;
                right: 20px;
                bottom: 18px;
                font-size: 13px;
            }

            .module-hub-fab .label-mobile { display: none; }
            .module-hub-fab .label-desktop { display: inline; }
        }
    </style>

    <a href="{{ route('modules.index') }}"
       class="module-hub-fab"
       title="Kembali ke Pilihan Modul">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span class="label-desktop">Pilihan Modul</span>
        <span class="label-mobile">Modul</span>
    </a>
@endif
