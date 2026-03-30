<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen Akun</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Manrope', sans-serif; margin:0; background:#f5f6f2; color:#1f2937; }
        .wrap { max-width:1100px; margin:0 auto; padding:24px 18px 40px; }
        .top { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; }
        .card { background:#fff; border:1px solid #dbe4d7; border-radius:14px; overflow:hidden; }
        .btn { border:1px solid #cbd5e1; border-radius:10px; padding:9px 12px; text-decoration:none; color:#0f172a; background:#fff; font-weight:600; }
        .btn-primary { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:left; vertical-align:top; }
        th { background:#f8fafc; font-size:13px; color:#475569; }
        .pill { display:inline-block; padding:2px 8px; border-radius:999px; background:#eef2ff; color:#3730a3; font-size:12px; margin:0 6px 6px 0; }
        .flash { padding:10px 12px; border-radius:10px; margin-bottom:12px; }
        .ok { background:#ecfdf3; border:1px solid #86efac; color:#166534; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <div>
            <h1 style="margin:0;">Manajemen Akun</h1>
            <p style="margin:6px 0 0; color:#64748b;">Kelola akun dan role per module untuk kebutuhan ERP.</p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('modules.index') }}" class="btn">Kembali ke Hub</a>
            <a href="{{ route('accounts.create') }}" class="btn btn-primary">Tambah Akun</a>
        </div>
    </div>

    @if(session('success'))
        <div class="flash ok">{{ session('success') }}</div>
    @endif

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Global Role</th>
                <th>Role Per Module</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                    <td>
                        @if($user->moduleRoles->isEmpty())
                            -
                        @else
                            @foreach($user->moduleRoles as $moduleRole)
                                <span class="pill">{{ strtoupper($moduleRole->module_key) }}: {{ $moduleRole->role_name }}</span>
                            @endforeach
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('accounts.edit', $user) }}" class="btn">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="color:#64748b;">Belum ada data akun.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:12px;">{{ $users->links() }}</div>
</div>
</body>
</html>
