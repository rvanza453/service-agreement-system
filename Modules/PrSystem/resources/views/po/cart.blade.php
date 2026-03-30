<x-prsystem::app-layout>
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Keranjang PO</h2>
            <form action="{{ route('po.cart.clear') }}" method="POST" onsubmit="return confirm('Kosongkan keranjang?');">
                @csrf
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Kosongkan Keranjang
                </button>
            </form>
        </div>

        @if($items->isEmpty())
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Keranjang Kosong</h3>
                <p class="text-gray-500 mb-6">Belum ada item yang ditambahkan ke keranjang PO.</p>
                <a href="{{ route('pr.index') }}" class="px-6 py-2.5 bg-primary-600 text-white font-bold rounded-lg hover:bg-primary-700 transition">
                    Lihat Daftar PR
                </a>
            </div>
        @else
            <form action="{{ route('po.create') }}" method="POST" id="checkout-form">
                @csrf
                
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-gray-700 uppercase">Daftar Item</h3>
                        <div class="text-sm text-gray-500">
                            {{ $items->count() }} item
                        </div>
                    </div>
                    
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 w-10">
                                    <input type="checkbox" id="select-all" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" checked>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PR Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($items as $item)
                                <tr>
                                    <td class="px-6 py-4">
                                        <input type="checkbox" name="items[]" value="{{ $item->id }}" class="item-checkbox rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" checked>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <a href="{{ route('pr.show', $item->purchaseRequest) }}" class="text-primary-600 hover:underline">
                                            {{ $item->purchaseRequest->pr_number }}
                                        </a>
                                        <div class="text-xs text-gray-400">
                                            {{ $item->purchaseRequest->request_date->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $item->item_name }}
                                        <div class="text-xs text-gray-500 font-normal">
                                            {{ $item->specification ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 text-center">
                                        {{ $item->final_quantity }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $item->unit }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right">
                                        <button type="button" onclick="removeItem({{ $item->id }})" class="text-red-600 hover:text-red-900">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end bg-white p-6 rounded-xl shadow-sm">
                    <button type="submit" class="px-8 py-3 bg-primary-600 text-white font-bold rounded-lg hover:bg-primary-700 transition shadow-md hover:shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Buat PO dari Item Terpilih
                    </button>
                </div>
            </form>

            <form id="remove-form" action="{{ route('po.cart.remove') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="pr_item_id" id="remove-item-id">
            </form>

            <script>
                document.getElementById('select-all').addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('.item-checkbox');
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });

                function removeItem(id) {
                    if(confirm('Hapus item ini dari keranjang?')) {
                        document.getElementById('remove-item-id').value = id;
                        document.getElementById('remove-form').submit();
                    }
                }
            </script>
        @endif
    </div>
</x-prsystem::app-layout>
