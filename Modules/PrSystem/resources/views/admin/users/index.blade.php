<x-prsystem::app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h2>
            <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-500 focus:bg-primary-500 active:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                + Tambah Pengguna
            </a>
        </div>

        <div class="space-y-4">
            @forelse($users as $siteName => $siteUsers)
                <div x-data="{ open: true }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button @click="open = !open" class="w-full flex justify-between items-center px-6 py-4 bg-gray-50 hover:bg-gray-100 transition-colors border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-800 text-lg">{{ $siteName }}</span>
                            <span class="bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full text-xs font-medium">{{ $siteUsers->count() }} Pengguna</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transform transition-transform duration-200" style="width: 16px; height: 16px;" :class="{'rotate-180': open, 'rotate-0': !open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    
                    <div x-show="open" x-transition class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/4">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/4">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Role Global</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Role Per Modul</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Dept / Posisi</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($siteUsers as $user)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @foreach($user->roles as $role)
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                    {{ ucfirst($role->name) }}
                                                </span>
                                            @endforeach
                                            @if($user->roles->isEmpty())
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            @if($user->moduleRoles->isEmpty())
                                                -
                                            @else
                                                @foreach($user->moduleRoles as $moduleRole)
                                                    <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-200 mr-1 mb-1">
                                                        {{ strtoupper($moduleRole->module_key) }}: {{ $moduleRole->role_name }}
                                                    </span>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="font-medium text-gray-700">{{ $user->department->name ?? '-' }}</div>
                                            <div class="text-xs text-gray-400">{{ $user->position ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                            <div class="flex justify-center items-center gap-2">
                                                <form action="{{ route('users.impersonate', $user) }}" method="POST" class="inline" onsubmit="return confirmImpersonate(this, '{{ $user->name }}')">
                                                    @csrf
                                                    <input type="hidden" name="admin_password" id="password-{{ $user->id }}">
                                                    <button type="submit" class="p-1 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded" title="Login As">
                                                        <svg class="w-4 h-4" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    </button>
                                                </form>
                                                <a href="{{ route('users.edit', $user) }}" class="p-1 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded">
                                                    <svg class="w-4 h-4" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </a>
                                                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 text-red-600 hover:text-red-900 hover:bg-red-50 rounded">
                                                        <svg class="w-4 h-4" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-500">
                    Belum ada data pengguna.
                </div>
            @endforelse
        </div>
    </div>

    <script>
    function confirmImpersonate(form, userName) {
        const password = prompt(`Masukkan password verifikasi untuk login sebagai "${userName}":`);
        if (password === null) {
            return false; // User cancelled
        }
        form.querySelector('input[name="admin_password"]').value = password;
        return true;
    }
    </script>
</x-prsystem::app-layout>
