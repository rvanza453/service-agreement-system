<x-prsystem::app-layout>
    @php
        $isPrAdmin = auth()->user()?->moduleRole('pr') === 'Admin';
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Gudang & Inventory</h2>
            <div class="flex gap-2">
                @if($isPrAdmin)
                    <a href="{{ route('inventory.import.out') }}" class="px-4 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import Stock OUT
                    </a>
                    <a href="{{ route('system.reset-warehouse') }}" class="px-4 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition flex items-center gap-2 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Reset Warehouse
                    </a>
                    <a href="{{ route('inventory.create') }}" class="px-4 py-2 bg-white text-gray-700 font-bold rounded-lg border border-gray-300 hover:bg-gray-50 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Buat Gudang
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($warehouses as $warehouse)
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">{{ $warehouse->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $warehouse->site->name ?? '-' }}</p>
                            </div>
                        </div>
                        @if($isPrAdmin)
                            <div class="flex gap-2">
                                <a href="{{ route('inventory.edit', $warehouse) }}" class="text-gray-400 hover:text-blue-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                <form action="{{ route('inventory.destroy', $warehouse) }}" method="POST" onsubmit="return confirmDelete(this, 'gudang', '{{ $warehouse->name }}')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="admin_password" class="delete-password">
                                    <button type="submit" class="p-1 text-red-600 hover:text-red-900 hover:bg-red-50 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-t border-gray-100 mt-2">
                        <span class="text-sm text-gray-500">Total Items:</span>
                        <span class="font-bold text-gray-800">{{ $warehouse->stocks_count }} SKUs</span>
                    </div>

                    <a href="{{ route('inventory.show', $warehouse) }}" class="block mt-4 text-center px-4 py-2 bg-gray-50 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition text-sm">
                        Lihat Stok
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <script>
    function confirmDelete(form, type, name) {
        const password = prompt(`Masukkan password verifikasi untuk menghapus ${type} "${name}":`);
        if (password === null) {
            return false; // User cancelled
        }
        form.querySelector('.delete-password').value = password;
        return true;
    }
    </script>
</x-prsystem::app-layout>
