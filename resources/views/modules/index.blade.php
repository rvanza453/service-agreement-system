<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pilih Modul ERP</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&family=space-grotesk:500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            /* Palette Background & Surface */
            --bg-color: #f4f7f6;
            --surface: rgba(255, 255, 255, 0.85);
            --surface-hover: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --border: rgba(226, 232, 240, 0.8);
            
            /* Accent Colors */
            --steel: #3b82f6;
            --steel-soft: #eff6ff;
            --amber: #f59e0b;
            --amber-soft: #fffbeb;
            --green: #10b981;
            --green-soft: #ecfdf5;
            --danger: #ef4444;
            
            /* Hero Gradient (Lebih Vibrant) */
            --hero-start: #0f172a;
            --hero-mid: #1e293b;
            --hero-end: #334155;
            --hero-glow: #38bdf8;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Manrope', sans-serif;
            color: var(--text);
            /* Animated Mesh Background */
            background: 
                radial-gradient(at 0% 0%, rgba(220, 232, 255, 0.7) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(216, 243, 220, 0.7) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(255, 238, 204, 0.7) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(230, 224, 255, 0.7) 0px, transparent 50%);
            background-color: var(--bg-color);
            background-size: 150% 150%;
            animation: gradientMove 15s ease infinite alternate;
        }

        .shell {
            max-width: 1120px;
            margin: 0 auto;
            padding: 40px 20px 60px;
        }

        /* --- HERO SECTION --- */
        .hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--hero-start), var(--hero-mid), var(--hero-end));
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 40px 36px;
            color: #ffffff;
            box-shadow: 0 30px 60px -15px rgba(15, 23, 42, 0.4);
            animation: fade-slide-down 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) both;
            margin-bottom: 32px;
        }

        /* Floating Orbs in Hero */
        .hero::before,
        .hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(60px);
            z-index: 0;
        }

        .hero::before {
            width: 400px;
            height: 400px;
            right: -100px;
            top: -150px;
            background: rgba(56, 189, 248, 0.3);
            animation: float 8s ease-in-out infinite;
        }

        .hero::after {
            width: 300px;
            height: 300px;
            left: -100px;
            bottom: -150px;
            background: rgba(16, 185, 129, 0.2);
            animation: float 10s ease-in-out infinite reverse;
        }

        .topbar {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 36px;
        }

        .brand h1 {
            margin: 0;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 42px;
            line-height: 1.1;
            letter-spacing: -0.04em;
            background: linear-gradient(to right, #ffffff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand p {
            margin: 8px 0 0;
            font-size: 16px;
            color: #cbd5e1;
            max-width: 500px;
        }

        .account {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border-radius: 100px;
            padding: 8px 24px 8px 8px;
            transition: all 0.3s ease;
        }

        .account:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-2px);
        }

        .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #38bdf8, #3b82f6);
            color: #fff;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .account small { display: block; color: #94a3b8; font-size: 12px; }
        .account strong { font-size: 15px; color: #ffffff; letter-spacing: -0.01em;}

        .hero-kpis {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .kpi {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .kpi:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-4px);
        }

        .kpi strong {
            display: block;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 28px;
            letter-spacing: -0.03em;
            line-height: 1;
            margin-bottom: 6px;
        }

        .kpi span {
            display: block;
            font-size: 13px;
            color: #94a3b8;
            font-weight: 500;
        }

        /* --- SECTION HEAD --- */
        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0 4px 20px;
        }

        .section-head h2 {
            margin: 0;
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: -0.03em;
            font-size: 24px;
            color: var(--text);
        }

        .section-head p {
            margin: 0;
            font-size: 14px;
            color: var(--muted);
        }

        /* --- MODULE CARDS --- */
        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }

        .module-card {
            position: relative;
            overflow: hidden;
            background: var(--surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 28px;
            text-decoration: none;
            color: inherit;
            /* Animasi Bouncy */
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05);
            animation: card-rise 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) both;
        }

        /* Staggered Animations */
        .module-grid > *:nth-child(1) { animation-delay: 0.1s; }
        .module-grid > *:nth-child(2) { animation-delay: 0.2s; }
        .module-grid > *:nth-child(3) { animation-delay: 0.3s; }
        .module-grid > *:nth-child(4) { animation-delay: 0.4s; }
        .module-grid > *:nth-child(5) { animation-delay: 0.5s; }

        .module-card:hover {
            transform: translateY(-8px) scale(1.02);
            background: var(--surface-hover);
            border-color: rgba(15, 23, 42, 0.1);
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.15);
            z-index: 10;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
            z-index: 0;
        }

        .module-card:hover::before {
            opacity: 1;
        }

        /* Glow effects for specific accents */
        .module-card.accent-steel:hover::before { background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.1), transparent 70%); }
        .module-card.accent-amber:hover::before { background: radial-gradient(circle at top right, rgba(245, 158, 11, 0.1), transparent 70%); }
        .module-card.accent-green:hover::before { background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.1), transparent 70%); }

        .module-card.disabled {
            cursor: not-allowed;
            background: rgba(255, 255, 255, 0.5);
            filter: grayscale(1);
            opacity: 0.7;
        }
        
        .module-card.disabled:hover {
            transform: none;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05);
            border-color: var(--border);
        }

        .icon-wrap {
            position: relative;
            z-index: 1;
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            margin-bottom: 20px;
            font-size: 24px;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .module-card:hover .icon-wrap {
            transform: scale(1.1) rotate(-5deg);
        }

        .module-card.accent-steel .icon-wrap { background: var(--steel-soft); color: var(--steel); }
        .module-card.accent-amber .icon-wrap { background: var(--amber-soft); color: var(--amber); }
        .module-card.accent-green .icon-wrap { background: var(--green-soft); color: var(--green); }

        .module-name {
            position: relative;
            z-index: 1;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 22px;
            margin: 0 0 10px;
            letter-spacing: -0.02em;
            color: var(--text);
        }

        .module-desc {
            position: relative;
            z-index: 1;
            margin: 0;
            color: var(--muted);
            min-height: 48px;
            line-height: 1.6;
            font-size: 14px;
        }

        .module-action {
            position: relative;
            z-index: 1;
            margin-top: 24px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 14px;
            color: var(--text);
            transition: gap 0.3s ease;
        }

        .module-card:hover .module-action {
            gap: 12px; /* Panah bergerak menjauh saat di hover */
        }

        .module-card.accent-steel .module-action { color: var(--steel); }
        .module-card.accent-amber .module-action { color: var(--amber); }
        .module-card.accent-green .module-action { color: var(--green); }

        .module-status {
            position: relative;
            z-index: 1;
            margin-top: 16px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 100px;
            background: #f1f5f9;
            color: #64748b;
        }

        /* --- BOTTOM FOOTER --- */
        .bottom {
            margin-top: 48px;
            display: flex;
            align-items: center;
            gap: 20px;
            justify-content: space-between;
            padding-top: 24px;
            border-top: 1px solid rgba(15, 23, 42, 0.05);
        }

        .credit {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 13px;
        }

        .credit-top {
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.02em;
        }

        .credit-bottom {
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .credit-bottom i {
            color: var(--amber);
            font-size: 11px;
            animation: pulse-icon 2s infinite;
        }

        .creator-link {
            position: relative;
            color: var(--text);
            font-weight: 700;
            text-decoration: none;
            transition: color 0.3s ease;
            margin-left: 2px;
        }

        /* Efek garis bawah meluncur saat di-hover */
        .creator-link::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: linear-gradient(90deg, var(--steel), var(--green));
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1);
            border-radius: 2px;
        }

        .creator-link:hover {
            color: var(--steel);
        }

        .creator-link:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            background: #fee2e2;
            color: var(--danger);
            border-radius: 12px;
            padding: 12px 20px;
            font-family: inherit;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background: #fca5a5;
            color: #7f1d1d;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        /* --- KEYFRAMES --- */
        @keyframes gradientMove {
            0% { background-position: 0% 0%; }
            100% { background-position: 100% 100%; }
        }

        @keyframes float {
            0% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
            100% { transform: translateY(0) scale(1); }
        }

        @keyframes fade-slide-down {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes card-rise {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes pulse-icon {
            0% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(1); opacity: 0.8; }
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            .hero { padding: 28px 24px; border-radius: 20px; }
            .brand h1 { font-size: 32px; }
            .hero-kpis { grid-template-columns: 1fr; gap: 12px; }
            .section-head { flex-direction: column; align-items: flex-start; gap: 8px; margin-bottom: 24px; }
            .bottom { flex-direction: column; align-items: flex-start; }
            .bottom-actions { width: 100%; }
            .btn-logout { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
@include('components.impersonation-banner')
@php
    $totalModules = count($modules);
    $disabledModules = collect($modules)->where('disabled', true)->count();
    $activeModules = $totalModules - $disabledModules;
@endphp
<div class="shell">
    <section class="hero">
        <div class="topbar">
            <div class="brand">
                <h1>Enterprise Module Hub</h1>
                <p>Pusat kendali seluruh modul ERP dalam satu layar yang cepat, rapi, dan fokus.</p>
            </div>
            <div class="account">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                <div>
                    <small>Login sebagai</small>
                    <strong>{{ auth()->user()->name ?? 'Admin System' }}</strong>
                </div>
            </div>
        </div>

        <div class="hero-kpis">
            <div class="kpi">
                <strong>{{ $activeModules }}</strong>
                <span>Modul Aktif</span>
            </div>
            <div class="kpi">
                <strong>{{ $disabledModules }}</strong>
                <span>Dalam Persiapan</span>
            </div>
            <div class="kpi">
                <strong>{{ $totalModules }}</strong>
                <span>Total Ekosistem Modul</span>
            </div>
        </div>
    </section>

    <div class="section-head">
        <h2>Pilih Modul Kerja</h2>
        <p>Kartu berwarna menandakan area kerja berbeda untuk navigasi lebih cepat.</p>
    </div>

    <div class="module-grid">
        @foreach($modules as $module)
            @if(!empty($module['disabled']))
                <div class="module-card accent-{{ $module['accent'] }} disabled" aria-disabled="true">
                    <div class="icon-wrap"><i class="fas {{ $module['icon'] }}"></i></div>
                    <h2 class="module-name">{{ $module['name'] }}</h2>
                    <p class="module-desc">{{ $module['description'] }}</p>
                    <div class="module-status"><i class="fas fa-clock"></i> Belum Siap</div>
                    <div class="module-action" style="color: #94a3b8">Akses dinonaktifkan</div>
                </div>
            @else
                <a href="{{ $module['route'] }}" class="module-card accent-{{ $module['accent'] }}">
                    <div class="icon-wrap"><i class="fas {{ $module['icon'] }}"></i></div>
                    <h2 class="module-name">{{ $module['name'] }}</h2>
                    <p class="module-desc">{{ $module['description'] }}</p>
                    <div class="module-action">Masuk ke modul <i class="fas fa-arrow-right"></i></div>
                </a>
            @endif
        @endforeach
    </div>

    <div class="bottom">
        <div class="credit" aria-label="Copyright dan kredit pembuat">
            <div class="credit-top">&copy; {{ date('Y') }} Enterprise Module Hub</div>
            <div class="credit-bottom">
                Create by  
                <a href="https://github.com/rvanza453" class="creator-link" target="_blank" rel="noopener noreferrer">Muhammad Revanza</a>
            </div>
        </div>

        <div class="bottom-actions">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout"><i class="fas fa-right-from-bracket"></i> Keluar dari Sistem</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>