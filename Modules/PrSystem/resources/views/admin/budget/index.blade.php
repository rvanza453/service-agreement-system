<x-prsystem::app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Budget ({{ $year }})</h2>
                <nav class="flex text-gray-500 text-sm mt-1" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('admin.budgets.index') }}" class="hover:text-primary-600 {{ !isset($site) && !isset($department) ? 'text-primary-600 font-bold' : '' }}">
                                Semua Site
                            </a>
                        </li>
                        @if(isset($site))
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                    </svg>
                                    <a href="{{ route('admin.budgets.index', ['site_id' => $site->id]) }}" class="ml-1 hover:text-primary-600 {{ !isset($department) ? 'text-primary-600 font-bold' : '' }} md:ml-2">{{ $site->name }}</a>
                                </div>
                            </li>
                        @endif
                        @if(isset($department))
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                    </svg>
                                    <span class="ml-1 text-primary-600 font-bold md:ml-2">{{ $department->name }}</span>
                                </div>
                            </li>
                        @endif
                    </ol>
                </nav>
            </div>
        </div>

        @if(!isset($site) && !isset($department))
            {{-- Level 1: Site List --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($sites as $s)
                    <a href="{{ route('admin.budgets.index', ['site_id' => $s->id]) }}" class="group bg-white p-6 rounded-xl shadow-sm border border-transparent hover:border-indigo-500 hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-indigo-50 rounded-lg group-hover:bg-indigo-100 transition-colors">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $s->name }}</h3>
                                    <p class="text-sm font-semibold text-indigo-600">Rp {{ number_format($s->total_budget, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500">{{ $s->dept_count }} Unit</p>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @elseif(isset($site) && !isset($department))
            {{-- Level 2: Department List --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($departments as $d)
                    <a href="{{ $d->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA 
                        ? route('admin.budgets.edit-department', $d) 
                        : route('admin.budgets.index', ['site_id' => $site->id, 'department_id' => $d->id]) 
                    }}" class="group bg-white p-6 rounded-xl shadow-sm border border-transparent hover:border-indigo-500 hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-blue-50 rounded-lg group-hover:bg-blue-100 transition-colors">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $d->name }}</h3>
                                    <p class="text-sm font-semibold text-indigo-600">Rp {{ number_format($d->total_budget, 0, ',', '.') }}</p>
                                    @if($d->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA)
                                        <p class="text-xs text-gray-500">Budget Unit / Job</p>
                                    @else
                                        <p class="text-xs text-gray-500">{{ $d->subDepartments->count() }} Stasiun / Afdeling</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            {{-- Level 3: Sub Department List Table OR Department Level Management --}}
            
            @if(isset($department) && $department->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Budget Unit / Job Configuration</h3>
                            <p class="text-gray-500 text-sm mt-1">Manage budget limits for this Unit directly (per Job).</p>
                        </div>
                        <div class="text-right">
                             <p class="text-sm font-semibold text-gray-500">Total Budget</p>
                             <p class="text-xl font-bold text-indigo-600">Rp {{ number_format($department->budgets->sum('amount'), 0, ',', '.') }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 border-t pt-6">
                        <a href="{{ route('admin.budgets.edit-department', $department) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Manage Unit Budget
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto pr-mobile-scroll">
                    <table class="min-w-[700px] w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stasiun / Afdeling</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Budget Configured</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($subDepartments as $sub)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{{ $sub->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-indigo-600">
                                        Rp {{ number_format($sub->budgets->sum('amount'), 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('admin.budgets.edit', $sub) }}" class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-600 rounded-md hover:bg-indigo-100 transition-colors">
                                            Manage Budget
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">Belum ada Stasiun / afdeling di unit ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-prsystem::app-layout>
