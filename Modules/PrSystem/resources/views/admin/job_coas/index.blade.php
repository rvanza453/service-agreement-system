<x-prsystem::app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Job COA</h2>
                <nav class="flex text-gray-500 text-sm mt-1" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('pr.dashboard') }}" class="inline-flex items-center hover:text-gray-900">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                                Dashboard
                            </a>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 font-medium text-gray-500 md:ml-2">Job COA</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            
            <a href="{{ route('job-coas.create') }}" class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm font-semibold hover:bg-primary-700 transition">
                + Tambah Job COA
            </a>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm p-4">
             <form action="{{ route('job-coas.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
                 <div class="flex-1">
                     <x-prsystem::text-input name="search" class="w-full" placeholder="Cari Kode atau Nama Job..." value="{{ request('search') }}" />
                 </div>
                 <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold hover:bg-gray-700 transition">Filter</button>
                 @if(request()->hasAny(['search']))
                    <a href="{{ route('job-coas.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-200 transition text-center">Reset</a>
                 @endif
             </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto pr-mobile-scroll">
            <table class="min-w-[760px] w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode COA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi Job</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($jobCoas as $jobCoa)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $jobCoa->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $jobCoa->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <div class="flex justify-center items-center gap-3">
                                    <a href="{{ route('job-coas.edit', $jobCoa) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <form action="{{ route('job-coas.destroy', $jobCoa) }}" method="POST" onsubmit="return confirm('Hapus Job COA ini? Hati-hati, Job terkait mungkin akan terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    <span>Belum ada data Job COA.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            
            @if($jobCoas->hasPages())
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    {{ $jobCoas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-prsystem::app-layout>
