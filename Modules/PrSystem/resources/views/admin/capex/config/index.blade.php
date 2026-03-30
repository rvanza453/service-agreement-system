<x-prsystem::app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Capex Approval Configuration</h2>
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
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/4">Department Config</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/2">Current Approvers (Step 1-5)</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($siteDepts as $department)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $department->name }}
                                            <div class="text-xs text-gray-400 font-normal mt-1">{{ $department->coa }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            @php
                                                // Sort configs by index
                                                $configs = $department->capexConfigs->sortBy('column_index')->take(5);
                                            @endphp
                                            
                                            @if($configs->isEmpty())
                                                <span class="text-gray-400 italic text-xs">Not configured</span>
                                            @else
                                                <div class="flex flex-wrap gap-2">
                                                @foreach($configs as $config)
                                                    <div class="flex items-center gap-1 bg-gray-50 px-2 py-1 rounded border border-gray-200 text-xs" title="{{ $config->label }}">
                                                        <span class="font-bold text-gray-600">{{ $config->column_index }}.</span>
                                                        @if($config->approver_user_id)
                                                            <span class="text-indigo-600 font-medium">{{ $config->approverUser->name ?? 'User?' }}</span>
                                                        @elseif($config->approver_role)
                                                            <span class="text-green-600 font-medium">{{ $config->approver_role }}</span>
                                                        @else
                                                            <span class="text-red-400">?</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                            <a href="{{ route('admin.capex.config.edit', $department) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                                Configure
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
                    No departments found.
                </div>
            @endforelse
        </div>
    </div>
</x-prsystem::app-layout>
