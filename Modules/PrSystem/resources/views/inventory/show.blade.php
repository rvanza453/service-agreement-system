<x-prsystem::app-layout>
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-3xl font-bold text-gray-800">{{ $warehouse->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                        {{ $warehouse->site->name ?? 'No Site' }}
                    </span>
                </div>
                <p class="text-gray-500 text-sm">Kelola stok dan pergerakan barang untuk gudang ini.</p>
            </div>
            <div class="flex flex-col items-end gap-2">
                 <a href="{{ route('inventory.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>

        <!-- Quick Stats & Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Stat Card -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-indigo-50 rounded-full text-indigo-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Total Item</h4>
                    <p class="text-2xl font-bold text-gray-900">{{ $stocks->total() }}</p>
                </div>
            </div>

            <!-- Actions Toolbar -->
            <div class="md:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <h4 class="text-base font-bold text-gray-800">Aksi Cepat</h4>
                    <p class="text-sm text-gray-500">Catat pergerakan barang baru</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('inventory.movement', ['warehouse' => $warehouse, 'type' => 'IN']) }}" 
                       class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2 group">
                        <div class="p-1 bg-green-500 rounded-md group-hover:bg-green-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        Barang Masuk
                    </a>
                    <a href="{{ route('inventory.movement', ['warehouse' => $warehouse, 'type' => 'OUT']) }}" 
                       class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2 group">
                        <div class="p-1 bg-red-500 rounded-md group-hover:bg-red-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </div>
                        Barang Keluar
                    </a>
                </div>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Daftar Stok Saat Ini
                    </h3>
                    
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('inventory.show', $warehouse) }}" class="flex gap-2">
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Cari barang..." 
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm w-64">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium text-sm">
                            Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ route('inventory.show', $warehouse) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium text-sm">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kode Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Stok</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($stocks as $stock)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $stock->product->code }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div class="font-medium text-gray-900">{{ $stock->product->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $stock->product->category ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="text-sm font-bold {{ $stock->quantity > $stock->product->min_stock ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $stock->formatted_quantity }}
                                    </span>
                                    <span class="text-xs text-gray-500 ml-1">{{ $stock->product->unit }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('inventory.history', ['warehouse' => $warehouse, 'product_id' => $stock->product_id]) }}" 
                                       class="text-gray-400 hover:text-primary-600 transition-colors p-2 hover:bg-gray-100 rounded-full inline-flex" 
                                       title="Lihat Riwayat Item">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        <span class="text-base font-medium">Belum ada barang di gudang ini</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $stocks->links() }}
            </div>
        </div>
    
        <!-- History Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Riwayat Pergerakan (Terbaru)
                </h3>
                <a href="{{ route('inventory.history', $warehouse) }}" class="text-sm font-bold text-primary-600 hover:text-primary-800 flex items-center gap-1 hover:underline">
                    Lihat Semua
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Barang</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($movements as $movement)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $movement->date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($movement->type === 'IN')
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800 border border-green-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                            MASUK
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800 border border-red-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            KELUAR
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-bold">{{ $movement->product->name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $movement->product->code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">
                                    {{ $movement->quantity }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $movement->remarks ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                            {{ substr($movement->user->name ?? 'S', 0, 1) }}
                                        </div>
                                        {{ $movement->user->name ?? 'System' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">
                                    Belum ada aktivitas pergerakan stok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
