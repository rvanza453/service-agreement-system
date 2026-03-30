<x-prsystem::app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Unit</h2>
                <nav class="flex text-gray-500 text-sm mt-1" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('master-departments.index') }}" class="hover:text-primary-600 {{ !isset($site) ? 'text-primary-600 font-bold' : '' }}">
                                Semua Site
                            </a>
                        </li>
                        @if(isset($site))
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                    </svg>
                                    <span class="ml-1 text-primary-600 font-bold md:ml-2">{{ $site->name }}</span>
                                </div>
                            </li>
                        @endif
                    </ol>
                </nav>
            </div>
            
            @if(isset($site))
                <a href="{{ route('master-departments.create', ['site_id' => $site->id]) }}" class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm font-semibold hover:bg-primary-700 transition">
                    + Tambah Unit
                </a>
            @endif
        </div>

        @if(!isset($site))
            {{-- Site Selection Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($sites as $s)
                    <a href="{{ route('master-departments.index', ['site_id' => $s->id]) }}" class="group bg-white p-6 rounded-xl shadow-sm border border-transparent hover:border-primary-500 hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-primary-50 rounded-lg group-hover:bg-primary-100 transition-colors">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-primary-600 transition-colors">{{ $s->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $s->departments_count }} Unit</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-all transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            {{-- Department List Table --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto pr-mobile-scroll">
                <table class="min-w-[860px] w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">COA</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sub Depts</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($departments as $department)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $department->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $department->coa }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs">{{ $department->subDepartments->count() }} Sub Depts</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('master-departments.edit', $department) }}" class="text-indigo-600 hover:text-indigo-900">Edit & Sub Dept</a>
                                        <a href="{{ route('departments.edit', $department) }}" class="text-green-600 hover:text-green-900" title="Config Budget & Approval">Config</a>
                                        <form action="{{ route('master-departments.destroy', $department) }}" method="POST" onsubmit="return confirm('Hapus unit ini? Semua data terkait akan ikut terhapus!');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada unit di site ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        @endif
    </div>
</x-prsystem::app-layout>
