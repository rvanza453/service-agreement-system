<x-prsystem::app-layout>
    <div class="max-w-xl mx-auto space-y-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Job</h2>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form action="{{ route('jobs.update', $job) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <x-prsystem::input-label for="site_id" value="Site / Lokasi" />
                    <select id="site_id" name="site_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                        <option value="" {{ $job->department_id == null ? 'selected' : '' }}>-- Global / Seluruh Site --</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ old('site_id', $job->site_id) == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                        @endforeach
                    </select>
                    <x-prsystem::input-error :messages="$errors->get('site_id')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-prsystem::input-label for="code" value="Kode Pekerjaan (COA)" />
                    <x-prsystem::text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $job->code)" required />
                    <x-prsystem::input-error :messages="$errors->get('code')" class="mt-2" />
                </div>
                
                <div class="mb-4">
                    <x-prsystem::input-label for="department_id" value="Unit / Department (Opsional)" />
                    <select id="department_id" name="department_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Global untuk Site ini --</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika job ini berlaku untuk semua unit di site tersebut.</p>
                    <x-prsystem::input-error :messages="$errors->get('department_id')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-prsystem::input-label for="name" value="Nama Pekerjaan (Job)" />
                    <x-prsystem::text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $job->name)" required />
                    <x-prsystem::input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="flex justify-end pt-4 border-t">
                    <a href="{{ route('jobs.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition mr-2">Batal</a>
                    <x-prsystem::primary-button>
                        {{ __('Simpan Perubahan') }}
                    </x-prsystem::primary-button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const siteSelect = document.getElementById('site_id');
        const deptSelect = document.getElementById('department_id');
        const currentDeptId = "{{ old('department_id', $job->department_id) }}";
        
        function loadDepartments(siteId, selectedDeptId = null) {
            deptSelect.innerHTML = '<option value="">-- Global untuk Site ini --</option>';
            
            if (siteId) {
                fetch(`/api/sites/${siteId}/departments`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(dept => {
                            const selected = dept.id == selectedDeptId ? 'selected' : '';
                            deptSelect.insertAdjacentHTML('beforeend', `<option value="${dept.id}" ${selected}>${dept.name}</option>`);
                        });
                    })
                    .catch(error => console.error('Error fetching departments:', error));
            }
        }

        siteSelect.addEventListener('change', function() {
            loadDepartments(this.value);
        });

        // Initial Load
        if (siteSelect.value) {
            loadDepartments(siteSelect.value, currentDeptId);
        }
    </script>
</x-prsystem::app-layout>
