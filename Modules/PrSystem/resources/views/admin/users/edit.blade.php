<x-prsystem::app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Edit Pengguna</h2>
            <a href="{{ route('users.index') }}" class="text-gray-600 hover:text-gray-900">Kembali</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <x-prsystem::input-label for="name" :value="__('Nama Lengkap')" />
                    <x-prsystem::text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus />
                    <x-prsystem::input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-prsystem::input-label for="email" :value="__('Email')" />
                    <x-prsystem::text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                    <x-prsystem::input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <!-- Phone Number -->
                <div>
                     <x-prsystem::input-label for="phone_number" :value="__('No. WhatsApp / HP')" />
                     <p class="text-xs text-gray-500 mb-1">Format: 08123xxx atau 628123xxx</p>
                     <x-prsystem::text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full" :value="old('phone_number', $user->phone_number)" />
                     <x-prsystem::input-error class="mt-2" :messages="$errors->get('phone_number')" />
                </div>

                <!-- Role -->
                <div>
                    <x-prsystem::input-label for="global_role" :value="__('Role Global')" />
                    <select id="global_role" name="global_role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Pilih Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('global_role', old('role', $user->roles->first()->name ?? '')) == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                    <x-prsystem::input-error class="mt-2" :messages="$errors->get('global_role')" />
                    <x-prsystem::input-error class="mt-2" :messages="$errors->get('role')" />
                </div>

                <div>
                    <x-prsystem::input-label :value="__('Role Per Modul')" />
                    <div class="mt-2 grid gap-3">
                        @foreach($moduleRoleConfig as $moduleKey => $moduleConfig)
                            <div class="rounded-lg border border-gray-200 p-3">
                                <div class="text-sm font-semibold text-gray-700 mb-2">{{ $moduleConfig['label'] ?? strtoupper($moduleKey) }}</div>
                                <select name="module_roles[{{ $moduleKey }}]" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Tidak di-set</option>
                                    @foreach(($moduleConfig['roles'] ?? []) as $moduleRole)
                                        <option value="{{ $moduleRole }}" @selected(old('module_roles.' . $moduleKey, $selectedModuleRoles[$moduleKey] ?? null) === $moduleRole)>
                                            {{ $moduleRole }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-prsystem::input-error class="mt-2" :messages="$errors->get('module_roles.' . $moduleKey)" />
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Site -->
                <div>
                    <x-prsystem::input-label for="site_id" :value="__('Site')" />
                    <select id="site_id" name="site_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Pilih Site (Opsional)</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ old('site_id', $user->site_id) == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                        @endforeach
                    </select>
                    <x-prsystem::input-error class="mt-2" :messages="$errors->get('site_id')" />
                </div>

                <!-- Department -->
                <div>
                    <x-prsystem::input-label for="department_id" :value="__('Unit')" />
                    <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Pilih Unit (Opsional)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <x-prsystem::input-error class="mt-2" :messages="$errors->get('department_id')" />
                </div>

                <!-- Position -->
                <div>
                    <x-prsystem::input-label for="position" :value="__('Posisi / Jabatan')" />
                    <x-prsystem::text-input id="position" name="position" type="text" class="mt-1 block w-full" :value="old('position', $user->position)" />
                    <x-prsystem::input-error class="mt-2" :messages="$errors->get('position')" />
                </div>

                <div class="border-t border-gray-100 pt-6 mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ubah Password (Opsional)</h3>
                    
                    <!-- Password -->
                    <div>
                        <x-prsystem::input-label for="password" :value="__('Password Baru')" />
                        <x-prsystem::text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah password.</p>
                        <x-prsystem::input-error class="mt-2" :messages="$errors->get('password')" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="mt-4">
                        <x-prsystem::input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                        <x-prsystem::text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
                        <x-prsystem::input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Batal
                    </a>
                    <x-prsystem::primary-button>{{ __('Simpan Perubahan') }}</x-prsystem::primary-button>
                </div>
            </form>
        </div>
    </div>
</x-prsystem::app-layout>

<script>
    document.getElementById('site_id').addEventListener('change', function() {
        const siteId = this.value;
        const deptSelect = document.getElementById('department_id');
        
        // Reset dropdown
        deptSelect.innerHTML = '<option value="">Pilih Unit (Opsional)</option>';
        deptSelect.disabled = true;
        
        if (siteId) {
            deptSelect.disabled = false;
            fetch(`/api/sites/${siteId}/departments`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(dept => {
                        deptSelect.innerHTML += `<option value="${dept.id}">${dept.name}</option>`;
                    });
                })
                .catch(error => console.error('Error fetching departments:', error));
        }
    });

    // Ensure dropdown state on load (if site is selected but dept is not?) 
    // Actually, for Edit, blade handles the initial state correctly.
</script>
