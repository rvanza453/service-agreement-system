<x-prsystem::app-layout>
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Edit Purchase Order (PO)</h2>
                <div class="text-sm text-gray-500">PO Number: {{ $po->po_number }} | PR: {{ $po->pr_number }}</div>
            </div>
            <a href="{{ route('po.show', $po) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition">
                Kembali ke Detail PO
            </a>
        </div>

        <form method="POST" action="{{ route('po.update', $po) }}" id="po-form">
            @csrf
            @method('PUT')

            <!-- Items -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-bold text-gray-700 uppercase">Item yang Dipilih</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Spesifikasi</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-blue-600 uppercase">Harga Satuan *</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($po->items as $item)
                            <tr>
                                <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                <input type="hidden" name="items[{{ $loop->index }}][quantity]" value="{{ $item->quantity }}">
                                
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $item->prItem->product->code ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                    {{ $item->prItem->item_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $item->prItem->specification ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-center font-medium">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $item->unit }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <input type="number" 
                                           name="items[{{ $loop->index }}][unit_price]" 
                                           class="item-price w-full text-right border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" 
                                           min="0" 
                                           step="any"
                                           value="{{ $item->unit_price }}"
                                           data-quantity="{{ $item->quantity }}"
                                           data-index="{{ $loop->index }}"
                                           required
                                           onchange="calculateItemTotal(this)">
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right font-medium">
                                    <span id="item-total-{{ $loop->index }}">Rp 0</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-sm font-bold text-gray-700 text-right">Subtotal:</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900 text-right" id="subtotal-display">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- PO Information -->
            <div class="bg-white rounded-xl shadow-sm p-6 space-y-6">
                <h3 class="text-lg font-bold text-gray-800">Informasi PO</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- PO Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor PO</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-md text-gray-900 font-medium">
                            {{ $po->po_number }}
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="Issued" {{ $po->status === 'Issued' ? 'selected' : '' }}>Issued</option>
                            <option value="Completed" {{ $po->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                            <option value="Cancelled" {{ $po->status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Delivery Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengiriman</label>
                        <input type="date" name="delivery_date" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" value="{{ $po->delivery_date ? $po->delivery_date->format('Y-m-d') : '' }}">
                    </div>
                </div>

                <!-- Vendor Information -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Informasi Vendor</h4>
                    
                     <!-- Manual Edit Alert -->
                    <div id="manual-edit-alert" class="hidden p-3 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 border border-yellow-200" role="alert">
                         <span class="font-bold">Mode Edit Manual:</span> 
                         Perubahan data vendor di sini <b>hanya berlaku untuk PO ini</b> dan tidak akan mengubah Master Data Vendor.
                    </div>

                    <div class="flex items-center mb-4">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" id="manual-edit-toggle" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" onchange="toggleManualEdit()">
                             <span class="text-sm font-bold text-gray-700">Edit Info Vendor Secara Manual</span>
                        </label>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Vendor *</label>
                            <input type="text" id="vendor_name" name="vendor_name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100" value="{{ old('vendor_name', $po->vendor_name) }}" required readonly>
                            @error('vendor_name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Vendor *</label>
                            <textarea id="vendor_address" name="vendor_address" rows="2" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100" required readonly>{{ old('vendor_address', $po->vendor_address) }}</textarea>
                            @error('vendor_address')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Telepon Vendor *</label>
                                <input type="text" id="vendor_phone" name="vendor_phone" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100" value="{{ old('vendor_phone', $po->vendor_phone) }}" required readonly>
                                @error('vendor_phone')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                                <input type="text" id="vendor_postal_code" name="vendor_postal_code" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100" value="{{ old('vendor_postal_code', $po->vendor_postal_code) }}" readonly>
                                @error('vendor_postal_code')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="pt-2">
                            <h5 class="text-sm font-bold text-gray-700 mb-2">Kontak Admin (UP)</h5>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Admin / UP</label>
                                    <input type="text" id="vendor_contact_person" name="vendor_contact_person" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100" value="{{ old('vendor_contact_person', $po->vendor_contact_person) }}" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Admin</label>
                                    <input type="text" id="vendor_contact_phone" name="vendor_contact_phone" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100" value="{{ old('vendor_contact_phone', $po->vendor_contact_phone) }}" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Admin</label>
                                    <input type="email" id="vendor_email" name="vendor_email" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100" value="{{ old('vendor_email', $po->vendor_email) }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discount Input -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Diskon & Pajak</h4>
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="w-full md:w-1/3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Diskon (%)</label>
                            <input type="number" 
                                   name="discount_percentage" 
                                   id="discount-percentage" 
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" 
                                   min="0" 
                                   max="100" 
                                   step="0.01" 
                                   value="{{ old('discount_percentage', $po->discount_percentage) }}" 
                                   oninput="calculateTotals()">
                        </div>
                        <div class="w-full md:w-1/3 flex items-center pt-6">
                            <label class="inline-flex items-center">
                                <input type="checkbox" 
                                       id="use-vat" 
                                       name="use_vat" 
                                       value="1" 
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                       {{ $po->use_vat ? 'checked' : '' }}
                                       onchange="calculateTotals()">
                                <span class="ml-2 text-sm font-bold text-gray-700">Kenakan PPN 12%</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Totals Summary (Auto-calculated) -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Ringkasan Perhitungan (Otomatis)</h4>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium text-gray-900" id="summary-subtotal">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-sm text-red-600" id="row-discount" style="display: none;">
                            <span class="text-gray-600 font-medium">Diskon (<span id="discount-percent-display">0</span>%):</span>
                            <span class="font-medium" id="summary-discount">- Rp 0</span>
                        </div>
                        <div class="flex justify-between text-sm" id="row-after-discount" style="display: none;">
                            <span class="text-gray-600 font-medium">Setelah Diskon:</span>
                            <span class="font-medium text-gray-900" id="summary-after-discount">Rp 0</span>
                        </div>
                        
                        <div class="border-t border-gray-200 my-2"></div>

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">DPP Lainnya (11/12 × Harga):</span>
                            <span class="font-medium text-gray-900" id="summary-dpp-lainnya">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">PPN 12% (dari DPP Lainnya):</span>
                            <span class="font-medium text-gray-900" id="summary-ppn">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t-2 border-gray-400 pt-2 mt-2">
                            <span class="text-gray-900">TOTAL:</span>
                            <span class="text-primary-600" id="summary-total">Rp 0</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-2 italic">
                            * Perhitungan otomatis: Total = Harga (setelah diskon) + PPN 12%
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="border-t border-gray-200 pt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Kontrak Kepada Vendor : </label>
                    <textarea name="notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('notes', $po->notes) }}</textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('po.show', $po) }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white font-bold rounded-lg hover:bg-primary-700 transition shadow-md hover:shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function formatRupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 10 }).format(amount);
        }

        function calculateItemTotal(input) {
            const index = input.dataset.index;
            const quantity = parseFloat(input.dataset.quantity);
            let priceStr = String(input.value).replace(',', '.');
            const unitPrice = parseFloat(priceStr) || 0;
            const total = quantity * unitPrice;
            
            document.getElementById(`item-total-${index}`).textContent = formatRupiah(total);
            calculateTotals();
        }

        function calculateTotals() {
            // Calculate subtotal from all items
            let subtotal = 0;
            document.querySelectorAll('.item-price').forEach(input => {
                const quantity = parseFloat(input.dataset.quantity);
                let priceStr = String(input.value).replace(',', '.');
                const unitPrice = parseFloat(priceStr) || 0;
                
                // Update individual item total display on initial load/recalc
                const index = input.dataset.index;
                const itemTotal = quantity * unitPrice;
                const totalDisplay = document.getElementById(`item-total-${index}`);
                if (totalDisplay) {
                    totalDisplay.textContent = formatRupiah(itemTotal);
                }

                subtotal += itemTotal;
            });

            // Get Discount Percentage
            let discountStr = String(document.getElementById('discount-percentage').value).replace(',', '.');
            const discountPercent = parseFloat(discountStr) || 0;
            
            // Calculate Discount Amount
            const discountAmount = subtotal * (discountPercent / 100);
            
            // Amount after discount
            const afterDiscount = subtotal - discountAmount;

            // Auto-calculate using new formula based on After Discount Amount:
            // DPP Lainnya = Jumlah Harga (After Discount) × (11/12)
            const dppLainnya = afterDiscount * (11 / 12);
            
            // PPN 12% = DPP Lainnya × 12% (IF CHECKED)
            const useVat = document.getElementById('use-vat').checked;
            const ppnAmount = useVat ? (dppLainnya * 0.12) : 0;
            
            // Total = Jumlah Harga (After Discount) + PPN
            const finalTotal = afterDiscount + ppnAmount;

            // Update displays
            document.getElementById('summary-subtotal').textContent = formatRupiah(subtotal);
            
            // Update Discount UI
            if (discountPercent > 0) {
                document.getElementById('row-discount').style.display = 'flex';
                document.getElementById('row-after-discount').style.display = 'flex';
                document.getElementById('discount-percent-display').textContent = discountPercent;
                document.getElementById('summary-discount').textContent = '- ' + formatRupiah(discountAmount);
                document.getElementById('summary-after-discount').textContent = formatRupiah(afterDiscount);
            } else {
                document.getElementById('row-discount').style.display = 'none';
                document.getElementById('row-after-discount').style.display = 'none';
            }

            document.getElementById('summary-dpp-lainnya').textContent = formatRupiah(dppLainnya);
            document.getElementById('summary-ppn').textContent = formatRupiah(ppnAmount);
            document.getElementById('summary-total').textContent = formatRupiah(finalTotal);
        }

        function setFieldState(element, isReadOnly) {
            element.readOnly = isReadOnly;
            if (isReadOnly) {
                element.classList.add('bg-gray-100');
                element.classList.remove('bg-white');
            } else {
                element.classList.remove('bg-gray-100');
                element.classList.add('bg-white');
            }
        }

        function toggleManualEdit() {
            const isManual = document.getElementById('manual-edit-toggle').checked;
            const alert = document.getElementById('manual-edit-alert');
            
            const fields = [
                'vendor_name', 'vendor_address', 'vendor_phone', 'vendor_postal_code', 
                'vendor_contact_person', 'vendor_contact_phone', 'vendor_email'
            ];

            if (isManual) {
                // Unlock fields
                fields.forEach(id => {
                    const el = document.getElementById(id);
                    if(el) setFieldState(el, false);
                });
                if (alert) alert.classList.remove('hidden');
            } else {
                fields.forEach(id => {
                    const el = document.getElementById(id);
                    if(el) setFieldState(el, true);
                });
                if (alert) alert.classList.add('hidden');
            }
        }

        // Initialize calculations on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateTotals();
        });
    </script>
</x-prsystem::app-layout>
