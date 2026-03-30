<x-prsystem::app-layout>
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Daftar Purchase Order (PO)</h2>
                <div class="text-sm text-gray-500">Kelola semua PO yang telah dibuat</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="GET" action="{{ route('po.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="Nomor PO, Vendor, PR...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                        <option value="">Semua Status</option>
                        <option value="Issued" {{ request('status') === 'Issued' ? 'selected' : '' }}>Issued</option>
                        <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                </div>

                <div class="md:col-span-4 flex flex-wrap justify-end gap-2">
                    <a href="{{ route('po.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition">
                        Reset
                    </a>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- PO List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto pr-mobile-scroll">
            <table class="min-w-[900px] w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor PO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PR Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pos as $po)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <a href="{{ route('po.show', $po) }}" class="text-primary-600 hover:text-primary-800">
                                    {{ $po->po_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $po->po_date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($po->related_prs->isNotEmpty())
                                    @foreach($po->related_prs as $pr)
                                        <a href="{{ route('pr.show', $pr) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                            {{ $pr->pr_number }}
                                        </a>{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @else
                                    {{ $po->pr_number ?? '-' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $po->vendor_name }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right">
                                Rp {{ number_format($po->final_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                @php
                                    $statusColor = match($po->status) {
                                        'Issued' => 'bg-blue-100 text-blue-800',
                                        'Completed' => 'bg-green-100 text-green-800',
                                        'Cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                    {{ $po->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('po.show', $po) }}" class="text-blue-600 hover:text-blue-800" title="Lihat Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('po.export.pdf', $po) }}" class="text-red-600 hover:text-red-800" title="Export PDF">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>
                                    @if($po->status !== 'Completed' || auth()->user()->hasRole('Admin'))
                                        <a href="{{ route('po.edit', $po) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm">Belum ada PO yang dibuat</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            <!-- Pagination -->
            @if($pos->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $pos->links() }}
                </div>
            @endif
        </div>
    </div>
</x-prsystem::app-layout>
