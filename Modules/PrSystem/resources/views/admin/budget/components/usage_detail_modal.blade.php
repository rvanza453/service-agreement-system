<div class="overflow-x-auto rounded-lg border border-gray-200">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ref Number</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Qty</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($movements as $m)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $m->date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $m->reference_number }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <div class="font-bold text-gray-900">{{ $m->product->name }}</div>
                        <div class="text-xs text-gray-500">{{ $m->product->code }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $m->type == 'IN' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $m->type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($m->quantity, 4) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($m->price, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-bold">{{ number_format($m->quantity * $m->price, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                        Tidak ada riwayat penggunaan.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-gray-50">
            <tr>
                <th colspan="6" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total Usage Reported Here:</th>
                <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">
                    {{ number_format($movements->sum(function($m){ return $m->quantity * $m->price; }), 2) }}
                </th>
            </tr>
            <tr>
                <th colspan="6" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actual Budget Used Amount:</th>
                <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">{{ number_format($budget->used_amount, 2) }}</th>
            </tr>
        </tfoot>
    </table>
</div>
