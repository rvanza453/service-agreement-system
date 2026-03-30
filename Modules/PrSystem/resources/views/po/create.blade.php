<x-prsystem::app-layout>
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Error Alert -->
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-red-800 font-bold mb-2">Gagal membuat PO! Terdapat {{ $errors->count() }} kesalahan:</h3>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Header -->
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Buat Purchase Order (PO)</h2>
                <div class="text-sm text-gray-500">Dari PR: {{ $prNumberString }}</div>
            </div>
            <a href="{{ route('pr.show', $firstPr) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition">
                Kembali ke PR
            </a>
        </div>

        <form method="POST" action="{{ route('po.store') }}" id="po-form" class="space-y-8">
            @csrf
            <input type="hidden" name="pr_number_string" value="{{ $prNumberString }}">
            <input type="hidden" name="pr_date_string" value="{{ $prDateString }}">

            <!-- Selected Items -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-bold text-gray-700 uppercase">Item yang Dipilih</h3>
                </div>
                <!-- ... table ... -->
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
                        @foreach($selectedItems as $item)
                            <tr>
                                <input type="hidden" name="items[{{ $loop->index }}][pr_item_id]" value="{{ $item->id }}">
                                <input type="hidden" name="items[{{ $loop->index }}][quantity]" value="{{ $item->final_quantity }}">
                                <input type="hidden" name="items[{{ $loop->index }}][unit]" value="{{ $item->unit }}">
                                
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($item->product)
                                        {{ $item->product->code }}
                                    @else
                                        <div class="space-y-2">
                                            <input type="text" 
                                                   name="items[{{ $loop->index }}][new_product_code]" 
                                                   value="{{ old('items.'.$loop->index.'.new_product_code') }}"
                                                   class="w-full text-xs border-gray-300 rounded focus:border-primary-500 focus:ring-primary-500" 
                                                   placeholder="Kode Barang (Baru)" 
                                                   required>
                                            <select name="items[{{ $loop->index }}][new_product_category]" 
                                                    class="w-full text-xs border-gray-300 rounded focus:border-primary-500 focus:ring-primary-500" 
                                                    required>
                                                <option value="">Pilih Kategori</option>
                                                @foreach((array) (config('options.product_categories') ?? config('prsystem.options.product_categories') ?? []) as $category)
                                                    <option value="{{ $category }}" {{ old('items.'.$loop->index.'.new_product_category') == $category ? 'selected' : '' }}>
                                                        {{ $category }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                    {{ $item->item_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $item->specification ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-center font-medium">
                                    {{ $item->final_quantity }}
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
                                           placeholder="0"
                                           data-quantity="{{ $item->final_quantity }}"
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
                
                <div class="grid grid-cols-1 gap-6">
                    <!-- PO Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor PO *</label>
                        <input type="text" name="po_number" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                        @error('po_number')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Vendor Information -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Informasi Vendor</h4>
                    
                    <div class="space-y-4">
                        <!-- Vendor Selection -->
                        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="w-full md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Vendor (Master Data)</label>
                                <select id="vendor-select" name="vendor_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" onchange="handleVendorChange()">
                                    <option value="">-- Pilih Vendor --</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" 
                                            {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}
                                            data-name="{{ $vendor->name }}"
                                            data-address="{{ $vendor->address }}"
                                            data-phone="{{ $vendor->phone }}"
                                            data-email="{{ $vendor->email }}"
                                            data-pic="{{ $vendor->pic_name }}"
                                            data-site="{{ $vendor->location }}"
                                            data-admin-phone="{{ $vendor->admin_phone }}">
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                    <option value="new">+ Tambah Vendor Baru</option>
                                </select>
                            </div>
                            <div class="flex items-center pt-5 pl-4 border-l border-gray-300 ml-4">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" id="manual-edit-toggle" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" onchange="toggleManualEdit()">
                                    <span class="text-sm font-bold text-gray-700">Edit Info Manual</span>
                                </label>
                            </div>
                        </div>

                        <!-- Manual/Auto Input Fields -->
                        <div id="vendor-details" class="space-y-4 pt-2">
                            <div id="manual-edit-alert" class="hidden p-3 mb-2 text-sm text-yellow-800 rounded-lg bg-yellow-50 border border-yellow-200" role="alert">
                                <span class="font-bold"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></path></svg> Mode Edit Manual:</span> 
                                Perubahan data vendor di sini <b>hanya berlaku untuk PO ini</b> dan tidak akan mengubah Master Data Vendor.
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Vendor *</label>
                                <input type="text" id="vendor_name" name="vendor_name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100 @error('vendor_name') border-red-500 @enderror" placeholder="PT AGRINDO CIPTA NUSA" value="{{ old('vendor_name') }}" required readonly>
                                @error('vendor_name')
                                    <p class="text-sm text-red-600 mt-1 font-bold">⚠️ {{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Vendor *</label>
                                <textarea id="vendor_address" name="vendor_address" rows="2" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100 @error('vendor_address') border-red-500 @enderror" placeholder="JALAN SEMARANG B2/14B SURABAYA" required readonly>{{ old('vendor_address') }}</textarea>
                                @error('vendor_address')
                                    <p class="text-sm text-red-600 mt-1 font-bold">⚠️ {{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Telepon Vendor *</label>
                                    <input type="text" id="vendor_phone" name="vendor_phone" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100 @error('vendor_phone') border-red-500 @enderror" placeholder="0315473760" value="{{ old('vendor_phone') }}" required readonly>
                                    @error('vendor_phone')
                                        <p class="text-sm text-red-600 mt-1 font-bold">⚠️ {{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                                    <input type="text" id="vendor_postal_code" name="vendor_postal_code" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100" value="{{ old('vendor_postal_code') }}" readonly>
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
                                        <input type="text" id="vendor_contact_person" name="vendor_contact_person" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100" value="{{ old('vendor_contact_person') }}" readonly>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Admin</label>
                                        <input type="text" id="vendor_contact_phone" name="vendor_contact_phone" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100" value="{{ old('vendor_contact_phone') }}" readonly>
                                    @error('vendor_contact_phone')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror                                    
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Admin</label>
                                        <input type="email" id="vendor_email" name="vendor_email" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100" value="{{ old('vendor_email') }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- New Vendor Alert -->
                        <div id="new-vendor-alert" class="hidden p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50" role="alert">
                            <span class="font-medium">Info:</span> Anda menambahkan vendor baru. Vendor ini akan disimpan dengan status <b>"Di Ajukan"</b> dan menunggu approval admin agar bisa muncul di list utama.
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
                                   min="0" 
                                   max="100" 
                                   step="0.01" 
                                   value="{{ old('discount_percentage', 0) }}" 
                                   oninput="calculateTotals()">
                        </div>
                        <div class="w-full md:w-1/3 flex items-center pt-6">
                            <label class="inline-flex items-center">
                                <input type="checkbox" 
                                       id="use-vat" 
                                       name="use_vat" 
                                       value="1" 
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                       checked 
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
                    <textarea name="notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('notes') }}</textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('pr.show', $firstPr) }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white font-bold rounded-lg hover:bg-primary-700 transition shadow-md hover:shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Buat PO
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
                subtotal += quantity * unitPrice;
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
            const tableSubtotal = document.getElementById('subtotal-display');
            if (tableSubtotal) {
                tableSubtotal.textContent = formatRupiah(subtotal);
            }
            
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

        function handleVendorChange() {
            const select = document.getElementById('vendor-select');
            const selectedOption = select.options[select.selectedIndex];
            const isNew = select.value === 'new';
            const isEmpty = select.value === '';
            const isManual = document.getElementById('manual-edit-toggle').checked;

            console.log('=== VENDOR CHANGE DEBUG ===');
            console.log('Selected Vendor ID:', select.value);
            console.log('Is New:', isNew);
            console.log('Is Empty:', isEmpty);
            console.log('Is Manual Mode:', isManual);

            // Fields to control
            const fields = [
                'vendor_name', 'vendor_address', 'vendor_phone', 'vendor_postal_code', 
                'vendor_contact_person', 'vendor_contact_phone', 'vendor_email'
            ];
            
            // If Manual Mode is ON, do NOT overwrite fields unless switching to NEW
            if (isManual && !isNew) {
                console.log('Manual mode is ON, skipping auto-populate');
                return; 
            }

            if (isNew) {
                console.log('NEW VENDOR MODE - Clearing all fields');
                // Enable editing, clear fields
                fields.forEach(id => {
                    const el = document.getElementById(id);
                    el.value = '';
                    setFieldState(el, false);
                });
                document.getElementById('new-vendor-alert').classList.remove('hidden');
                
                // Disable manual toggle for New Vendor (it's always manual/new)
                document.getElementById('manual-edit-toggle').checked = false;
                document.getElementById('manual-edit-toggle').disabled = true;
                toggleManualEdit(); // Ensure UI updates
            } else if (!isEmpty) {
                console.log('EXISTING VENDOR - Populating fields');
                
                // Get vendor data from option attributes
                const vendorData = {
                    name: selectedOption.dataset.name || '',
                    address: selectedOption.dataset.address || '',
                    phone: selectedOption.dataset.phone || '',
                    email: selectedOption.dataset.email || '',
                    pic: selectedOption.dataset.pic || '',
                    adminPhone: selectedOption.dataset.adminPhone || ''
                };
                
                console.log('Vendor Data:', vendorData);
                
                // Populate fields
                document.getElementById('vendor_name').value = vendorData.name;
                document.getElementById('vendor_address').value = vendorData.address;
                document.getElementById('vendor_phone').value = vendorData.phone;
                document.getElementById('vendor_email').value = vendorData.email;
                document.getElementById('vendor_contact_person').value = vendorData.pic;
                document.getElementById('vendor_contact_phone').value = vendorData.adminPhone;
                document.getElementById('vendor_postal_code').value = ''; // Always empty unless manually filled
                
                console.log('Fields populated. Checking for empty required fields...');
                
                // Check if required fields are empty
                const emptyFields = [];
                if (!vendorData.name) emptyFields.push('Nama Vendor');
                if (!vendorData.address) emptyFields.push('Alamat Vendor');
                if (!vendorData.phone) emptyFields.push('Telepon Vendor');
                
                
                // Set custom validation messages for HTML5 form submission
                function validateField(id, value, fieldName, rules = {}) {
                    const el = document.getElementById(id);
                    if (!el) return;
                    
                    el.setCustomValidity(''); // Reset first
                    
                    if (rules.required && (!value || value.trim() === '' || value.trim() === '-')) {
                        el.setCustomValidity(`⚠️ ${fieldName} wajib diisi dan tidak boleh hanya strip (-).`);
                    } else if (rules.noHyphen && value.includes('-')) {
                        el.setCustomValidity(`⚠️ ${fieldName} tidak boleh mengandung karakter strip (-). Format harus angka tersambung.`);
                    }
                }

                validateField('vendor_name', vendorData.name, 'Nama Vendor', { required: true });
                validateField('vendor_address', vendorData.address, 'Alamat Vendor', { required: true });
                validateField('vendor_phone', vendorData.phone, 'Telepon Vendor', { required: true, noHyphen: true });
                
                // Set all fields to readonly
                fields.forEach(id => {
                    const el = document.getElementById(id);
                    setFieldState(el, true);
                });
                
                document.getElementById('new-vendor-alert').classList.add('hidden');
                document.getElementById('manual-edit-toggle').disabled = false;
                
                // Reset manual mode
                document.getElementById('manual-edit-toggle').checked = false;
                toggleManualEdit();
            } else {
                console.log('EMPTY SELECTION - Resetting fields');
                // Reset
                fields.forEach(id => {
                    const el = document.getElementById(id);
                    el.value = '';
                    setFieldState(el, true);
                });
                document.getElementById('new-vendor-alert').classList.add('hidden');
                document.getElementById('manual-edit-toggle').disabled = false;
                document.getElementById('manual-edit-toggle').checked = false;
                toggleManualEdit();
            }
            
            console.log('=== END VENDOR CHANGE ===\n');
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
            const select = document.getElementById('vendor-select');
            const alert = document.getElementById('manual-edit-alert');
            
            // If "New Vendor" or Empty is selected, ignore manual toggle or force it off
            if (select.value === 'new' || select.value === '') {
                 if (alert) alert.classList.add('hidden');
                 return;
            }

            const fields = [
                'vendor_name', 'vendor_address', 'vendor_phone', 'vendor_postal_code', 
                'vendor_contact_person', 'vendor_contact_phone', 'vendor_email'
            ];

            if (isManual) {
                // Unlock fields
                fields.forEach(id => {
                    setFieldState(document.getElementById(id), false);
                });
                if (alert) alert.classList.remove('hidden');
            } else {
                // Lock fields and implicitly reset to master data (by calling handleVendorChange)
                // However, calling handleVendorChange might cause recursion or data loss if not careful.
                // We simply loop and set readonly. The user must re-select vendor if they want to discard changes?
                // OR: we simply re-trigger handleVendorChange to "Reset" to master data.
                handleVendorChange(); 
                if (alert) alert.classList.add('hidden');
            }
        }

        // Initialize calculations on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateTotals();
            restoreVendorState();
            
            // Initialize TomSelect for Vendor dropdown to make it searchable
            new TomSelect('#vendor-select', {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: "-- Cari & Pilih Vendor --",
                onChange: function(value) {
                    handleVendorChange();
                }
            });
        });

        function restoreVendorState() {
            const select = document.getElementById('vendor-select');
            const isNew = select.value === 'new';
            const hasValue = select.value !== '';
            
            if (isNew) {
                // If 'new' was selected (and persisted via old()), ensure fields are unlocked
                const fields = [
                    'vendor_name', 'vendor_address', 'vendor_phone', 'vendor_postal_code', 
                    'vendor_contact_person', 'vendor_contact_phone', 'vendor_email'
                ];
                fields.forEach(id => {
                    const el = document.getElementById(id);
                    if(el) setFieldState(el, false);
                });
                
                document.getElementById('new-vendor-alert').classList.remove('hidden');
                document.getElementById('manual-edit-toggle').checked = false;
                document.getElementById('manual-edit-toggle').disabled = true;
                toggleManualEdit();
            } else if (hasValue) {
                // Existing vendor selected. 
                // Fields are already populated via old() and readonly via HTML default.
                // Just enable the manual edit toggle.
                document.getElementById('new-vendor-alert').classList.add('hidden');
                document.getElementById('manual-edit-toggle').disabled = false;
            }

            // Clear custom validity on input so user can submit after fixing
            document.querySelectorAll('#vendor_name, #vendor_address, #vendor_phone').forEach(el => {
                if (el) {
                    el.addEventListener('input', function() {
                        this.setCustomValidity(''); 
                        if (this.id === 'vendor_phone' && this.value.includes('-')) {
                            this.setCustomValidity('⚠️ Telepon Vendor tidak boleh mengandung karakter strip (-). Format harus angka tersambung.');
                        } else if (this.value.trim() === '' || this.value.trim() === '-') {
                            this.setCustomValidity(`⚠️ Kolom ini wajib diisi dan tidak boleh hanya strip (-).`);
                        }
                    });
                }
            });
        }
    </script>
</x-prsystem::app-layout>
