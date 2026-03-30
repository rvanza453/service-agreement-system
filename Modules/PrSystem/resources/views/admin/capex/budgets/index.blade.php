<x-prsystem::app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Capex Budgets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full max-w-none mx-auto sm:px-6 lg:px-8">
            
            <!-- Create New Budget -->
            <div class="bg-white shadow-sm sm:rounded-lg mb-6 p-6">
                <h3 class="text-lg font-bold mb-4">Set Budget</h3>
                <form action="{{ route('admin.capex.budgets.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <select name="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Asset</label>
                        <select name="capex_asset_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->code }} - {{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Budget Code Auto Generated --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Quantity</label>
                        <input type="number" name="original_quantity" min="1" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Amount</label>
                        <input type="number" name="amount" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fiscal Year</label>
                        <input type="number" name="fiscal_year" value="{{ date('Y') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    </div>
                    <div class="flex items-center pt-6">
                        <input type="checkbox" name="is_budgeted" value="1" checked id="is_budgeted" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="is_budgeted" class="ml-2 block text-sm text-gray-900">Is Budgeted (Dianggarkan)</label>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Set Budget</button>
                    </div>
                </form>
            </div>

            <!-- List Budgets -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="w-full max-w-full overflow-x-auto overflow-y-hidden pb-2" style="-webkit-overflow-scrolling: touch;">
                    <table class="min-w-[1200px] w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dept & Asset</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty (Rem)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total (PTA / Rem)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[170px]">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($budgets as $budget)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $budget->budget_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $budget->department->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $budget->capexAsset->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $budget->fiscal_year }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ $budget->original_quantity }} 
                                        <span class="text-xs text-gray-400">({{ $budget->remaining_quantity }})</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="font-bold text-gray-900">Awal: Rp {{ number_format($budget->amount, 0) }}</div>
                                        @if($budget->pta_amount > 0)
                                            <div class="text-xs text-blue-600 font-medium">+ PTA: Rp {{ number_format($budget->pta_amount, 0) }}</div>
                                            <div class="text-xs text-indigo-700 font-bold border-t border-gray-100 mt-1 pt-1">Total: Rp {{ number_format($budget->amount + $budget->pta_amount, 0) }}</div>
                                        @endif
                                        <div class="text-xs text-green-600 font-bold mt-1">Sisa: Rp {{ number_format($budget->remaining_amount, 0) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($budget->is_budgeted)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Budgeted</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Unbudgeted</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium min-w-[170px]">
                                        <div class="flex items-center gap-3">
                                            <button type="button" onclick="openPtaModal({{ $budget->id }}, '{{ $budget->budget_code }}')" class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1 text-blue-700 hover:bg-blue-100 font-semibold transition-colors">
                                                + PTA
                                            </button>
                                            <form action="{{ route('admin.capex.budgets.destroy', $budget) }}" method="POST" onsubmit="return confirm('Delete this budget?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1 text-red-700 hover:bg-red-100 font-medium transition-colors">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PTA Modal -->
    <div id="ptaModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative w-full max-w-md p-5 border shadow-xl rounded-xl bg-white m-4">
            <div class="mt-2">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Tambah PTA</h3>
                    <button type="button" onclick="closePtaModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="mb-5 px-3 py-2 bg-blue-50 border border-blue-100 text-blue-800 text-sm rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Budget Code: <strong id="ptaBudgetCode"></strong></span>
                </div>

                <form id="ptaForm" method="POST" action="">
                    @csrf
                    <div class="mb-5">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="pta_amount">
                            Nominal Tambahan (Rp)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" name="pta_amount" id="pta_amount" min="1" required
                                   class="pl-10 shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                        </div>
                        <p class="text-[11px] text-gray-500 mt-2 leading-relaxed">Nilai ini akan ditambahkan ke total margin limit capex request, tanpa merubah catatan nilai budget awal yang telah dibuat.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                        <button type="button" onclick="closePtaModal()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium shadow-sm transition-colors flex items-center gap-2">
                            Tambah PTA
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        function openPtaModal(id, code) {
            document.getElementById('ptaBudgetCode').innerText = code;
            document.getElementById('ptaForm').action = `/admin/capex/budgets/${id}/pta`;
            document.getElementById('ptaModal').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('pta_amount').focus();
            }, 100);
        }

        function closePtaModal() {
            document.getElementById('ptaModal').classList.add('hidden');
            document.getElementById('ptaForm').reset();
        }
    </script>
</x-prsystem::app-layout>
