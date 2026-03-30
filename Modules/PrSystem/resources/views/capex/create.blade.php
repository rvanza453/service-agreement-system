<x-prsystem::app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Capex Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                

                {{-- Budget will always be filtered to user's department, no need to show warning --}}

                <form action="{{ route('capex.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Department: Dropdown for Admin, Read-only for regular users --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Department</label>
                            @if($isAdmin)
                                <select id="departmentSelect" name="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-400 mt-1">Admin dapat memilih department mana pun.</p>
                            @else
                                <div class="mt-1 flex items-center gap-2">
                                    <input type="text" value="{{ $userDept->name }}" class="block w-full rounded-md border-gray-200 bg-gray-100 text-gray-600 shadow-sm cursor-not-allowed" readonly>
                                    <span class="text-xs text-gray-400 whitespace-nowrap">🔒 Auto-assigned</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Department ditentukan secara otomatis berdasarkan akun Anda.</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Request Type</label>
                            <div class="mt-2 space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="type" value="Baru" class="form-radio text-indigo-600" checked>
                                    <span class="ml-2">Baru</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="type" value="Perbaikan" class="form-radio text-indigo-600">
                                    <span class="ml-2">Perbaikan</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="type" value="Penggantian" class="form-radio text-indigo-600">
                                    <span class="ml-2">Penggantian</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" name="quantity" id="quantity" min="1" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price Per Unit (Rp)</label>
                            <input type="number" name="price" id="price" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total Amount</label>
                            <input type="text" id="total_amount" class="mt-1 block w-full rounded-md border-gray-200 bg-gray-100 text-gray-500" readonly>
                        </div>
                    </div>

                    <script>
                        const qtyInput = document.getElementById('quantity');
                        const priceInput = document.getElementById('price');
                        const totalInput = document.getElementById('total_amount');

                        function calculateTotal() {
                            const qty = parseFloat(qtyInput.value) || 0;
                            const price = parseFloat(priceInput.value) || 0;
                            const total = qty * price;
                            totalInput.value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(total);
                        }

                        qtyInput.addEventListener('input', calculateTotal);
                        priceInput.addEventListener('input', calculateTotal);
                    </script>

                    <!-- Budget Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pilih Budget / Aset</label>

                        <select id="capex_budget_select" name="capex_budget_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">-- Pilih Aset Budget --</option>
                            @foreach($budgets as $budget)
                                <!-- <option value="{{ $budget->id }}" data-dept="{{ $budget->department_id }}"> -->
                                <option value="{{ $budget->id }}" >
                                    [{{ $budget->budget_code }}] {{ $budget->capexAsset->name }} — {{ $budget->department->name }} 
                                    (Limit: Rp {{ number_format($budget->amount + $budget->pta_amount, 0) }}{{ $budget->pta_amount > 0 ? ' incl PTA' : '' }} | Sisa: Rp {{ number_format($budget->remaining_amount, 0) }})
                                </option>
                            @endforeach
                        </select>

                        <p id="budget_hint" class="text-xs text-gray-500 mt-1">
                            Menampilkan budget aktif tahun {{ date('Y') }}.
                        </p>

                        @if($isAdmin)
                        <script>
                            const deptSelect = document.getElementById('departmentSelect');
                            const budgetSelect = document.getElementById('capex_budget_select');
                            const budgetHint = document.getElementById('budget_hint');

                            function filterBudgets() {
                                const selectedDeptId = deptSelect ? deptSelect.value : null;
                                const options = budgetSelect.querySelectorAll('option[data-dept]');
                                let visibleCount = 0;

                                options.forEach(opt => {
                                    if (!selectedDeptId || opt.dataset.dept === selectedDeptId) {
                                        opt.style.display = '';
                                        visibleCount++;
                                    } else {
                                        opt.style.display = 'none';
                                        // Reset selection if hidden
                                        if (opt.selected) {
                                            budgetSelect.value = '';
                                        }
                                    }
                                });

                                // Update hint text with selected dept name
                                const selectedDeptText = deptSelect ? (deptSelect.options[deptSelect.selectedIndex]?.text ?? '') : '';
                                budgetHint.innerHTML = `Menampilkan budget aktif tahun {{ date('Y') }} untuk department <strong>${selectedDeptText}</strong> (${visibleCount} tersedia).`;
                            }

                            if (deptSelect) {
                                deptSelect.addEventListener('change', filterBudgets);
                                // Trigger immediately on page load
                                filterBudgets();
                            }
                        </script>
                        @endif

                        <script>
                            // Initialize Tom Select for searchable capex dropdown
                            new TomSelect('#capex_budget_select', {
                                placeholder: '-- Cari Aset Budget --',
                                searchField: ['text'],
                                maxOptions: 50,
                                plugins: {
                                    clear_button: {title: 'Hapus pilihan'}
                                }
                            });
                        </script>
                    </div>


                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lampiran Pendukung (PDF)</label>
                        <input type="file" name="supporting_document" accept=".pdf,application/pdf" class="mt-1 block w-full rounded-md border-gray-300 text-sm file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-1">Hanya file PDF, maksimal 10 MB.</p>
                        @error('supporting_document')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-800">Justifikasi & Kuesioner</h3>
                        <div class="space-y-4">
                            @php
                                $questions = [
                                    1 => 'Apa yang biasa dipakai selama ini?',
                                    2 => 'Mengapa pengeluaran diperlukan?',
                                    3 => 'Dapatkah pengeluaran ditunda pada tahun depan? jika tidak, mengapa?',
                                    4 => 'Apa konsekuensi jika pengeluaran di tolak?',
                                    5 => 'Mungkinkah ada dampak buruk pada operasi yang ada? (contoh: Kekacauan, waktu, lingkungan)',
                                    6 => 'Berapa lama proyek berlangsung? Kapan proyek tersebut selesai?'
                                ];
                            @endphp

                            @foreach($questions as $index => $q)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $index }}. {{ $q }}</label>
                                    <textarea name="questionnaire[{{ $index }}]" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required></textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-indigo-700 transition">
                            Submit Capex Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
