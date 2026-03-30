<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tambah Akun</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family:'Manrope', sans-serif; margin:0; background:#f5f6f2; color:#1f2937; }
        .wrap { max-width:860px; margin:0 auto; padding:24px 18px 40px; }
        .card { background:#fff; border:1px solid #dbe4d7; border-radius:14px; padding:16px; }
        .field label { display:block; font-weight:600; margin-bottom:6px; }
        .input, .select { width:100%; border:1px solid #cbd5e1; border-radius:10px; padding:10px; }
        .btn { border:1px solid #cbd5e1; border-radius:10px; padding:9px 12px; text-decoration:none; color:#0f172a; background:#fff; font-weight:600; }
        .btn-primary { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
        .errors { background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; border-radius:10px; padding:10px 12px; margin-bottom:12px; }
    </style>
</head>
<body>
<div class="wrap">
    <h1 style="margin-top:0;">Tambah Akun</h1>
    <p style="color:#64748b;">Sekalian set role global dan role per module.</p>

    @if($errors->any())
        <div class="errors">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('accounts.store') }}" class="card">
        @include('users._form')
    </form>
</div>
</body>
</html>
