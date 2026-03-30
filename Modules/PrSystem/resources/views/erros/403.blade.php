<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
        <h1 class="text-6xl font-bold text-red-500 mb-4">403</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Akses Ditolak (Forbidden)</h2>
        <p class="text-gray-600 mb-6">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}
        </p>
        <a href="{{ url()->previous() }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            Kembali
        </a>
    </div>
</body>
</html>
