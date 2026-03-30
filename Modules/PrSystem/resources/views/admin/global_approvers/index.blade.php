<x-prsystem::app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Konfigurasi Approval Head Office</h2>
                <p class="text-sm text-gray-500">Atur approver yang akan ditambahkan otomatis untuk unit yang mewajibkan approval HO.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Form Card -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tambah Approver HO</h3>
                <form action="{{ route('global-approvers.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <x-prsystem::input-label for="site_id" :value="__('Berlaku Untuk Site')" />
                        <select id="site_id" name="site_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Global (Semua Site) --</option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }} ({{ $site->code }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Biarkan kosong untuk Deputy/Direktur (berlaku nasional). Pilih Site khusus untuk Investor.</p>
                        <x-prsystem::input-error class="mt-2" :messages="$errors->get('site_id')" />
                    </div>

                    <div>
                        <x-prsystem::input-label for="user_id" :value="__('User / Pejabat')" />
                        <select id="user_id" name="user_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Pilih User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->position }} @if($user->site) ({{ $user->site->code }}) @endif</option>
                            @endforeach
                        </select>
                        <x-prsystem::input-error class="mt-2" :messages="$errors->get('user_id')" />
                    </div>

                    <div>
                        <x-prsystem::input-label for="role_name" :value="__('Label Role')" />
                        <x-prsystem::text-input id="role_name" name="role_name" type="text" class="mt-1 block w-full" placeholder="HO Approver" value="HO Approver" required />
                        <x-prsystem::input-error class="mt-2" :messages="$errors->get('role_name')" />
                    </div>

                    <div>
                        <x-prsystem::input-label for="level" :value="__('Urutan Level')" />
                        <x-prsystem::text-input id="level" name="level" type="number" class="mt-1 block w-full" placeholder="1" required />
                        <p class="text-xs text-gray-500 mt-1">Approver HO akan ditambahkan SETELAH approver lokal. Level ini menentukan urutan DIANTARA approver HO.</p>
                        <x-prsystem::input-error class="mt-2" :messages="$errors->get('level')" />
                    </div>

                    <x-prsystem::primary-button class="w-full justify-center">{{ __('Tambahkan') }}</x-prsystem::primary-button>
                </form>
            </div>

            <!-- List Card -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden md:col-span-2">
                <div class="overflow-x-auto pr-mobile-scroll">
                <table class="min-w-[900px] w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scope</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($approvers as $approver)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $approver->level }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($approver->site)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Khusus: {{ $approver->site->code }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Global (All)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="font-medium">{{ $approver->user->name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $approver->user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        {{ $approver->role_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form action="{{ route('global-approvers.destroy', $approver) }}" method="POST" onsubmit="return confirm('Hapus approver ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada approver HO yang dikonfigurasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
