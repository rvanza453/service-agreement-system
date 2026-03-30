<x-prsystem::app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Edit Unit (Master Data)</h2>
            <a href="{{ route('master-departments.index') }}" class="text-gray-600 hover:text-gray-900">Kembali</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form action="{{ route('master-departments.update', $department) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <x-prsystem::input-label for="site_id" value="Site / Lokasi" />
                        <select id="site_id" name="site_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ $department->site_id == $site->id ? 'selected' : '' }}>{{ $site->name }} ({{ $site->code }})</option>
                            @endforeach
                        </select>
                         <x-prsystem::input-error :messages="$errors->get('site_id')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-prsystem::input-label for="name" value="Nama Unit" />
                        <x-prsystem::text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $department->name)" required />
                        <x-prsystem::input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-prsystem::input-label for="warehouse_id" value="Warehouse (Gudang) - Optional" />
                        <select id="warehouse_id" name="warehouse_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- Tidak Terhubung ke Gudang --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ $department->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                         <div class="text-xs text-gray-500 mt-1">Pilih gudang jika unit ini memiliki stok fisik yang perlu ditampilkan di PR.</div>
                         <x-prsystem::input-error :messages="$errors->get('warehouse_id')" class="mt-2" />
                    </div>

                    <div class="mb-4 col-span-2">
                        <x-prsystem::input-label for="coa" value="COA" />
                        <x-prsystem::text-input id="coa" class="block mt-1 w-full" type="text" name="coa" :value="old('coa', $department->coa)" required />
                         <x-prsystem::input-error :messages="$errors->get('coa')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t">
                    <x-prsystem::primary-button>
                        {{ __('Update Data Utama') }}
                    </x-prsystem::primary-button>
                </div>
            </form>
            
            {{-- Sub Departments Section --}}
            <div class="mt-8 border-t border-gray-100 pt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Stasiun (Afdeling)</h3>
                    <button type="button" onclick="showSubDeptModal()" class="text-sm text-primary-600 hover:text-primary-700 font-medium">+ Tambah Sub Dept</button>
                </div>

                @if($department->subDepartments->count() > 0)
                    <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">COA</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($department->subDepartments as $sub)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $sub->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $sub->coa ?? '-' }}</td>
                                        <td class="px-4 py-2 text-right text-sm font-medium">
                                            <form action="{{ route('sub-departments.destroy', $sub) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus sub department ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 italic">Belum ada Stasiun / Afdeling.</p>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Sub Dept Modal Logic
        function showSubDeptModal() {
            document.getElementById('subDeptModal').classList.remove('hidden');
        }

        function closeSubDeptModal() {
            document.getElementById('subDeptModal').classList.add('hidden');
        }
    </script>
    
    {{-- Sub Dept Modal --}}
    <div id="subDeptModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeSubDeptModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('sub-departments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="department_id" value="{{ $department->id }}">
                    <input type="hidden" name="redirect_back" value="1"> {{-- Signal to controller --}}
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tambah Stasiun / Afdeling</h3>
                        <div class="mt-2 space-y-4">
                            <div>
                                <label for="sub_name" class="block text-sm font-medium text-gray-700">Nama Stasiun / Afdeling</label>
                                <input type="text" name="name" id="sub_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label for="sub_coa" class="block text-sm font-medium text-gray-700">COA (Opsional)</label>
                                <input type="text" name="coa" id="sub_coa" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeSubDeptModal()">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
