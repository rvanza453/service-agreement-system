<x-prsystem::app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Konfigurasi Approval Department</h2>
            
        </div>

        <div class="space-y-4">
            @forelse($departments as $siteName => $siteDepts)
                <div x-data="{ open: true }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button @click="open = !open" class="w-full flex justify-between items-center px-6 py-4 bg-gray-50 hover:bg-gray-100 transition-colors border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-800 text-lg">{{ $siteName }}</span>
                            <span class="bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full text-xs font-medium">{{ $siteDepts->count() }} Unit</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transform transition-transform duration-200" style="width: 16px; height: 16px;" :class="{'rotate-180': open, 'rotate-0': !open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    
                    <div x-show="open" x-transition class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/4">Nama Unit</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">COA</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Budget Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/2">Approver Config</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($siteDepts as $department)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $department->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $department->coa }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if(($department->budget_type instanceof \Modules\PrSystem\Enums\BudgetingType ? $department->budget_type->value : $department->budget_type) === 'job_coa')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Per Pekerjaan (Job)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Per Station
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            @foreach($department->approverConfigs as $config)
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded border border-gray-200">Lvl {{ $config->level }}</span>
                                                    <span class="text-gray-700 font-medium">{{ $config->role_name }}</span>
                                                    <span class="text-gray-500 text-xs">({{ $config->user->name ?? 'User Deleted' }})</span>
                                                </div>
                                            @endforeach
            
                                            @if($department->use_global_approval && $globalApprovers->isNotEmpty())
                                                <div class="mt-2 pt-2 border-t border-dashed border-gray-200">
                                                    <div class="text-xs font-semibold text-indigo-600 mb-1 flex items-center gap-1">
                                                        <svg class="w-4 h-4 flex-shrink-0" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                        Approver HO +
                                                    </div>
                                                    @php $currentMaxLevel = $department->approverConfigs->max('level') ?? 0; @endphp
                                                    @foreach($globalApprovers as $global)
                                                        <div class="flex items-center gap-2 mb-1 pl-2 border-l-2 border-indigo-100">
                                                            <span class="bg-indigo-50 text-indigo-700 text-xs px-2 py-0.5 rounded">Lvl {{ $currentMaxLevel + $global->level }}</span>
                                                            <span class="text-gray-600 text-xs">{{ $global->role_name }} ({{ $global->user->name }})</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                            <a href="{{ route('departments.edit', $department) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                                Config
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-500">
                    Belum ada unit.
                </div>
            @endforelse
        </div>
    </div>
</x-prsystem::app-layout>
