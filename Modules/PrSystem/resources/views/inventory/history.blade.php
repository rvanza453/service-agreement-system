<x-prsystem::app-layout>
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                 <h2 class="text-2xl font-bold text-gray-800">Riwayat Pergerakan Stok</h2>
                 <p class="text-gray-500">Gudang: {{ $warehouse->name }}</p>
            </div>
            
            <a href="{{ route('inventory.show', $warehouse) }}" class="text-sm text-gray-500 hover:text-gray-700 md:ml-auto">
                &larr; Kembali ke Stok
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="GET" action="{{ route('inventory.history', $warehouse) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                
                <!-- Filter Product -->
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Barang</label>
                     <select name="product_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                        <option value="">Semua Barang</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->code }} - {{ $product->name }}
                            </option>
                        @endforeach
                     </select>
                </div>

                <!-- Filter Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                    <select name="type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                        <option value="">Semua Tipe</option>
                        <option value="IN" {{ request('type') == 'IN' ? 'selected' : '' }}>Barang Masuk</option>
                        <option value="OUT" {{ request('type') == 'OUT' ? 'selected' : '' }}>Barang Keluar</option>
                    </select>
               </div>

               <!-- Filter Date Range -->
               <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
               </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <div class="flex gap-2">
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- History Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto pr-mobile-scroll">
            <table class="min-w-[1100px] w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tujuan / Keperluan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Oleh</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($movements as $movement)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $movement->date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($movement->type === 'IN')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        MASUK
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        KELUAR
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="font-bold text-gray-800">{{ $movement->product->name }}</div>
                                <div class="text-xs text-gray-500 bg-gray-100 inline-block px-1.5 py-0.5 rounded mt-0.5">{{ $movement->product->code }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-bold text-center">
                                {{ $movement->quantity }} {{ $movement->product->unit }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if($movement->type === 'OUT')
                                    <div class="font-bold">
                                        {{ $movement->subDepartment->department->name ?? '' }} - {{ $movement->subDepartment->name ?? '-' }}
                                    </div>
                                    @if($movement->job)
                                        <div class="text-xs text-gray-500">{{ $movement->job->code }} - {{ $movement->job->name }}</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium text-right whitespace-nowrap">
                                Rp {{ number_format($movement->quantity * $movement->price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $movement->remarks ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                                        {{ substr($movement->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    {{ $movement->user->name ?? 'System' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    <span class="text-lg font-medium text-gray-900">Tidak ada riwayat ditemukan</span>
                                    <p class="text-sm text-gray-500 mt-1">Coba sesuaikan filter pencarian anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
