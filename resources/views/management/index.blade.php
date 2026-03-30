<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Global Management</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg: #f3f7f5;
            --surface: #ffffff;
            --border: #d6e2dc;
            --text: #1f2937;
            --muted: #64748b;
            --brand: #1f6f5f;
            --brand-soft: #e6f4f1;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Manrope', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 8% 0%, #dff1e9 0%, transparent 30%),
                radial-gradient(circle at 100% 0%, #e5edf8 0%, transparent 24%),
                var(--bg);
        }

        .shell {
            max-width: 1160px;
            margin: 0 auto;
            padding: 30px 20px 44px;
        }

        .head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
        }

        .head h1 {
            margin: 0;
            font-size: 30px;
            letter-spacing: -0.02em;
        }

        .head p {
            margin: 6px 0 0;
            color: var(--muted);
        }

        .head .actions {
            display: inline-flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn {
            text-decoration: none;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: #fff;
            color: #334155;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 700;
        }

        .btn.primary {
            background: var(--brand);
            color: #fff;
            border-color: #155e52;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 14px;
        }

        .card {
            display: block;
            text-decoration: none;
            color: inherit;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            border-color: #b9d5cb;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.1);
        }

        .icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: var(--brand-soft);
            color: var(--brand);
            margin-bottom: 10px;
            font-size: 18px;
        }

        .card h2 {
            margin: 0;
            font-size: 17px;
            letter-spacing: -0.01em;
        }

        .card p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .arrow {
            margin-top: 12px;
            font-size: 12px;
            font-weight: 800;
            color: #0f766e;
        }

        @media (max-width: 740px) {
            .head {
                flex-direction: column;
                align-items: flex-start;
            }

            .head .actions {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
@include('components.impersonation-banner')
<div class="shell">
    <div class="head">
        <div>
            <h1>Global Management</h1>
            <p>Area global untuk pengelolaan user, role, master organisasi, dan audit aktivitas lintas modul.</p>
        </div>
        <div class="actions">
            <a href="{{ route('modules.index') }}" class="btn">Kembali ke Module Hub</a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline-block;">
                @csrf
                <button type="submit" class="btn primary"><i class="fas fa-right-from-bracket"></i> Logout</button>
            </form>
        </div>
    </div>

    <div class="grid">
        @foreach($items as $item)
            <a href="{{ $item['route'] }}" class="card">
                <div class="icon"><i class="fas {{ $item['icon'] }}"></i></div>
                <h2>{{ $item['title'] }}</h2>
                <p>{{ $item['description'] }}</p>
                <div class="arrow">Buka menu <i class="fas fa-arrow-right"></i></div>
            </a>
        @endforeach
    </div>
</div>
</body>
</html>
