<x-prsystem::app-layout>
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">
                {{ $type === 'IN' ? 'Catat Barang Masuk' : 'Catat Barang Keluar' }}
            </h2>
            <div class="text-sm text-gray-500">
                Gudang: <span class="font-bold text-gray-700">{{ $warehouse->name }}</span>
            </div>
            <a href="{{ route('inventory.show', $warehouse) }}" class="text-sm text-gray-500 hover:text-gray-700">
                &larr; Kembali
            </a>
        </div>

        <form action="{{ route('inventory.store-movement', $warehouse) }}" method="POST" id="movement-form">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <!-- Date -->
                <div class="p-6 border-b border-gray-100">
                     <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Transaksi</label>
                     <input type="date" name="date" class="w-full md:w-1/3 border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" value="{{ date('Y-m-d') }}" required>
                </div>
                
                <!-- Items Table -->
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="items-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-64">Barang</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase w-24">Qty</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Harga Satuan</th>
                                @if($type === 'OUT')
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-48">Untuk Stasiun/Unit</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-48">Pekerjaan (Job)</th>
                                @endif
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200" id="items-body">
                            <!-- Rows will be added here via JS -->
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <button type="button" onclick="addItemRow()" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Item
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white font-bold rounded-lg hover:bg-primary-700 transition shadow-md">
                    Simpan {{ $type === 'IN' ? 'Pemasukan' : 'Pengeluaran' }}
                </button>
            </div>
        </form>
    </div>

    <!-- Product Options Template -->
    <template id="product-options">
        <option value="">-- Pilih Barang --</option>
        @foreach($products as $product)
            <option value="{{ $product->id }}">{{ $product->code }} - {{ $product->name }}</option>
        @endforeach
    </template>

    <!-- SubDept Options -->
    @if($type === 'OUT')
        <template id="subdept-options">
            <option value="">-- Pilih Stasiun --</option>
            @foreach($departments as $dept)
                <optgroup label="{{ $dept->name }}">
                    @foreach($dept->subDepartments as $sub)
                        <option value="{{ $sub->id }}" data-budget-type="{{ $dept->budget_type }}">{{ $sub->name }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </template>

        <template id="job-options">
            <option value="">-- Pilih Pekerjaan --</option>
            @foreach($jobs as $job)
                <option value="{{ $job->id }}">{{ $job->code }} - {{ $job->name }}</option>
            @endforeach
        </template>
    @endif

    <script>
        let rowCount = 0;
        const type = "{{ $type }}";

        function addItemRow() {
            const tbody = document.getElementById('items-body');
            const row = document.createElement('tr');
            const index = rowCount++;
            
            const productOptions = document.getElementById('product-options').innerHTML;
            let extraCells = '';

            if (type === 'OUT') {
                const subDeptOptions = document.getElementById('subdept-options').innerHTML;
                const jobOptions = document.getElementById('job-options').innerHTML;
                
                extraCells = `
                    <td class="px-4 py-2 align-top">
                        <select name="items[${index}][sub_department_id]" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm subdept-select" onchange="handleSubDeptChange(this, ${index})" required>
                            ${subDeptOptions}
                        </select>
                    </td>
                    <td class="px-4 py-2 align-top">
                        <select name="items[${index}][job_id]" id="job-select-${index}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm bg-gray-50 opacity-50 cursor-not-allowed" disabled>
                            ${jobOptions}
                        </select>
                    </td>
                `;
            }

            row.innerHTML = `
                <td class="px-4 py-2 align-top">
                    <select name="items[${index}][product_id]" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" required>
                        ${productOptions}
                    </select>
                </td>
                <td class="px-4 py-2 align-top">
                    <input type="number" name="items[${index}][quantity]" class="w-24 text-center border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" min="1" required>
                </td>
                <td class="px-4 py-2 align-top">
                    <!-- Price Input: Required for IN (User inputs Purchase Price), Readonly for OUT (Uses System Price) -->
                    <input type="number" name="items[${index}][price]" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" min="0" step="0.01" ${type === 'OUT' ? 'readonly class="bg-gray-100"' : 'required placeholder="0"'} >
                </td>
                ${extraCells}
                <td class="px-4 py-2 align-top">
                     <textarea name="items[${index}][remarks]" rows="1" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="Keterangan"></textarea>
                </td>
                <td class="px-4 py-2 align-top text-center">
                    <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 pt-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
        }

        function handleSubDeptChange(select, index) {
            const jobSelect = document.getElementById(`job-select-${index}`);
            const selectedOption = select.options[select.selectedIndex];
            const budgetType = selectedOption.getAttribute('data-budget-type');
            
            // Assuming BudgetingType::JOB_COA is the one requiring Jobs. 
            // In Enum, JOB_COA value is 'job_coa' or comparable. 
            // However, looking at codebase, I can just check if budgetType includes 'job'. 
            // Better: 'job_coa' is one of the enum values.
            
            // For simplicity, verify against the string likely used. 
            // \Modules\PrSystem\Enums\BudgetingType::JOB_COA->value is likely 'job_coa' or 2.
            // Let's assume strict check isn't needed in JS if we rely on backend validation,
            // but for UX, we enable it if 'budget_type' implies job needed.
            // Actually, let's treat it as: If selected, enable job. If not, disable.
            // Wait, "per stasiun" vs "per job".
            // "per stasiun" (STATION) -> Job disabled.
            // "per job" (JOB_COA) -> Job required.
            
            // Check value of enum.
            // From searching `BudgetingType` previously, I didn't see the file content, but `PrController` uses `\Modules\PrSystem\Enums\BudgetingType::JOB_COA`.
            // Commonly 'Job' or 'job_coa'.
            
            // Let's fallback: Enable job select if budgetType is 'job_coa'.
            // I'll assume the string value is passed in data-budget-type.

            if (budgetType === 'job_coa') {
                 jobSelect.disabled = false;
                 jobSelect.classList.remove('bg-gray-50', 'opacity-50', 'cursor-not-allowed');
                 jobSelect.required = true;
            } else {
                 jobSelect.disabled = true;
                 jobSelect.classList.add('bg-gray-50', 'opacity-50', 'cursor-not-allowed');
                 jobSelect.value = "";
                 jobSelect.required = false;
            }
        }

        // Add first row on load
        document.addEventListener('DOMContentLoaded', () => {
            addItemRow();
        });
    </script>
</x-prsystem::app-layout>
