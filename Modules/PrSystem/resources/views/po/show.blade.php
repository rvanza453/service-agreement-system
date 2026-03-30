<x-prsystem::app-layout>
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Detail Purchase Order</h2>
                <div class="text-sm text-gray-500">Nomor: {{ $po->po_number }}</div>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $statusColor = match($po->status) {
                        'Issued' => 'bg-blue-100 text-blue-800',
                        'Completed' => 'bg-green-100 text-green-800',
                        'Cancelled' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                @endphp
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusColor }}">
                    {{ $po->status }}
                </span>
                
                <a href="{{ route('po.export.pdf', $po) }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export PDF
                </a>

                @if(auth()->user()->hasAnyRole(['Admin', 'Warehouse']) && ($po->status !== 'Completed' || auth()->user()->hasRole('Admin')))
                    <a href="{{ route('po.edit', $po) }}" 
                       class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm font-medium transition inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit PO
                    </a>
                @endif
            </div>
        </div>

        <!-- PO Details Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="block text-xs text-gray-400 uppercase">Nomor PO</span>
                    <span class="block text-sm font-medium text-gray-800">{{ $po->po_number }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400 uppercase">Tanggal PO</span>
                    <span class="block text-sm font-medium text-gray-800">{{ $po->po_date->format('d M Y') }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400 uppercase">Tanggal Pengiriman</span>
                    <span class="block text-sm font-medium text-gray-800">{{ $po->delivery_date ? $po->delivery_date->format('d M Y') : '-' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div>
                    <span class="block text-xs text-gray-400 uppercase">PR Number</span>
                    <span class="block text-sm font-medium text-gray-800">
                        @if($po->purchaseRequest)
                            <a href="{{ route('pr.show', $po->purchaseRequest) }}" class="text-primary-600 hover:text-primary-800">
                                {{ $po->pr_number }}
                            </a>
                        @else
                            {{ $po->pr_number }}
                        @endif
                    </span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400 uppercase">PR Date</span>
                    <span class="block text-sm font-medium text-gray-800">{{ $po->pr_date ? $po->pr_date->format('d M Y') : '-' }}</span>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <div class="flex justify-between items-center mb-2">
                    <span class="block text-xs text-gray-400 uppercase">Vendor</span>
                    
                    @php
                        $isModified = false;
                        if($po->vendor) {
                            // Compare key fields (Normalized)
                            $poName = trim($po->vendor_name ?? '');
                            $masterName = trim($po->vendor->name ?? '');
                            
                            $poAddress = trim($po->vendor_address ?? '');
                            $masterAddress = trim($po->vendor->address ?? '');

                            $poPhone = trim($po->vendor_phone ?? '');
                            $masterPhone = trim($po->vendor->phone ?? '');

                            $poContact = trim($po->vendor_contact_person ?? '');
                            $masterContact = trim($po->vendor->pic_name ?? ''); // Accessing pic_name for master

                            $poContactPhone = trim($po->vendor_contact_phone ?? '');
                            $masterContactPhone = trim($po->vendor->admin_phone ?? ''); // Accessing admin_phone for master

                            $poEmail = trim($po->vendor_email ?? '');
                            $masterEmail = trim($po->vendor->email ?? '');

                            if (
                                strcasecmp($poName, $masterName) !== 0 || 
                                strcasecmp($poAddress, $masterAddress) !== 0 || 
                                strcasecmp($poPhone, $masterPhone) !== 0 ||
                                strcasecmp($poContact, $masterContact) !== 0 ||
                                strcasecmp($poContactPhone, $masterContactPhone) !== 0 ||
                                strcasecmp($poEmail, $masterEmail) !== 0
                            ) {
                                $isModified = true;
                            }
                        }
                    @endphp

                    @if($isModified)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Data Vendor Berbeda dengan Master
                        </span>
                    @endif
                </div>

                <div class="bg-gray-50 rounded-lg p-3 grid grid-cols-1 md:grid-cols-2 gap-4 relative overflow-hidden">
                    @if($isModified)
                        <div class="absolute top-0 right-0 w-2 h-full bg-yellow-400"></div>
                    @endif
                    
                    <div class="space-y-1">
                        <div class="font-medium text-gray-900">{{ $po->vendor_name }}</div>
                        <div class="text-sm text-gray-600">{{ $po->vendor_address }}</div>
                        @if($po->vendor_postal_code)
                            <div class="text-sm text-gray-600">Kode Pos: {{ $po->vendor_postal_code }}</div>
                        @endif
                        <div class="text-sm text-gray-600">Telp: {{ $po->vendor_phone }}</div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-xs text-gray-400 uppercase font-semibold">Kontak Admin (UP)</div>
                        @if($po->vendor_contact_person)
                            <div class="text-sm text-gray-600">Nama: {{ $po->vendor_contact_person }}</div>
                        @endif
                        @if($po->vendor_contact_phone)
                            <div class="text-sm text-gray-600">HP: {{ $po->vendor_contact_phone }}</div>
                        @endif
                        @if($po->vendor_email)
                            <div class="text-sm text-gray-600">Email: {{ $po->vendor_email }}</div>
                        @endif
                        @if(!$po->vendor_contact_person && !$po->vendor_contact_phone && !$po->vendor_email)
                            <div class="text-sm text-gray-400 italic">Tidak ada informasi kontak admin</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-bold text-gray-700 uppercase">Item Barang</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Spesifikasi</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($po->items as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-500 text-center">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item->prItem->product->code ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $item->prItem->item_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item->prItem->specification ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-center font-medium">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item->unit }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Summary -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Ringkasan</h3>
            
            <div class="max-w-md ml-auto space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-medium text-gray-900">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</span>
                </div>
                
                @if($po->discount_percentage > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Diskon ({{ $po->discount_percentage }}%):</span>
                        <span class="font-medium text-red-600">- Rp {{ number_format($po->discount_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Jumlah Setelah Diskon:</span>
                        <span class="font-medium text-gray-900">Rp {{ number_format($po->subtotal - $po->discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                
                @if($po->dpp_lainnya > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">DPP Lainnya:</span>
                        <span class="font-medium text-gray-900">Rp {{ number_format($po->dpp_lainnya, 0, ',', '.') }}</span>
                    </div>
                @endif
                
                @if($po->use_vat || $po->ppn_amount > 0)
                    <div class="flex justify-between text-sm border-t border-gray-300 pt-2">
                        <span class="text-gray-600">DPP:</span>
                        <span class="font-medium text-gray-900">Rp {{ number_format($po->dpp, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">PPN {{ $po->ppn_percentage }}%:</span>
                        <span class="font-medium text-gray-900">Rp {{ number_format($po->ppn_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                
                <div class="flex justify-between text-lg font-bold border-t-2 border-gray-400 pt-2">
                    <span class="text-gray-900">TOTAL:</span>
                    <span class="text-primary-600">Rp {{ number_format($po->final_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($po->notes)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Catatan</h3>
                <div class="text-sm text-gray-600 whitespace-pre-line">{{ $po->notes }}</div>
            </div>
        @endif

        <!-- Back Button -->
        <div class="flex justify-start">
            <a href="{{ route('po.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition">
                ← Kembali ke Daftar PO
            </a>
        </div>
    </div>
</x-prsystem::app-layout>
