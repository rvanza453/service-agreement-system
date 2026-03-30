<x-prsystem::app-layout>
    <div class="max-w-4xl mx-auto py-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Reset Data Warehouse & Budget</h2>
            <p class="text-sm text-gray-500">
                Fitur ini akan <strong class="text-red-600">MENGHAPUS SEMUA</strong> data stock dan riwayat pergerakan di Warehouse, 
                serta mereset seluruh `used_amount` di tabel Budget menjadi 0.
            </p>
        </div>

        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">PERINGATAN!</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>Tindakan ini tidak dapat dibatalkan. Pastikan Anda benar-benar ingin mereset data sebelum melanjutkan.</p>
                        <ul class="list-disc pl-5 mt-1">
                            <li>Menghapus semua records di tabel <code class="bg-red-100 px-1 rounded">warehouse_stocks</code></li>
                            <li>Menghapus semua records di tabel <code class="bg-red-100 px-1 rounded">stock_movements</code></li>
                            <li>Mereset nilai di tabel <code class="bg-red-100 px-1 rounded">budgets</code> kolom `used_amount` menjadi 0</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="{{ route('system.reset-warehouse.post') }}" method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN INGIN MERESET SEMUA DATA WAREHOUSE DAN BUDGET? TINDAKAN INI TIDAK BISA DIBATALKAN!');">
                @csrf
                <div class="mb-4">
                    <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">
                        Masukkan Password Verifikasi Admin
                    </label>
                    <input type="password" name="admin_password" id="admin_password" required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                           placeholder="Password Verifikasi" autocomplete="off">
                    @error('admin_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('pr.dashboard') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Eksekusi Reset Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-prsystem::app-layout>
