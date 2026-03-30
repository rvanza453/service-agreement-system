<x-prsystem::app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if(isset($department))
                Manage Budget: {{ $department->name }}
                <span class="text-sm font-normal text-gray-500 ml-2">({{ $department->site->name }})</span>
            @else
                Manage Budget: {{ $subDepartment->name }}
                <span class="text-sm font-normal text-gray-500 ml-2">({{ $subDepartment->department->name }})</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ isset($department) ? route('admin.budgets.update-department', $department) : route('admin.budgets.update', $subDepartment) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            @if(isset($isJobCoa) && $isJobCoa)
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Budget Limits per Job (Year {{ date('Y') }})</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach ($jobs as $job)
                                        <div>
                                            <div class="flex justify-between items-end mb-1">
                                                <x-prsystem::input-label :for="'budget_'.$job->id" :value="$job->code . ' - ' . $job->name" />
                                            </div>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                                </div>
                                                <input type="number" 
                                                       name="budgets[{{ $job->id }}]" 
                                                       id="budget_{{ $job->id }}"
                                                       class="block w-full rounded-md border-gray-300 pl-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                                       placeholder="0"
                                                       value="{{ old('budgets.'.$job->id, $existingBudgets[$job->id] ?? 0) }}"
                                                       min="0"
                                                       step="any">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Budget Limits per Category (Year {{ date('Y') }})</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach ($categories as $category)
                                        <div>
                                            <x-prsystem::input-label :for="'budget_'.$category" :value="$category" />
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                                </div>
                                                <input type="number" 
                                                       name="budgets[{{ $category }}]" 
                                                       id="budget_{{Str::slug($category)}}"
                                                       class="block w-full rounded-md border-gray-300 pl-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                                       placeholder="0"
                                                       value="{{ old('budgets.'.$category, $existingBudgets[$category] ?? 0) }}"
                                                       min="0"
                                                       step="any">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-4 border-t pt-4">
                            <x-prsystem::primary-button>{{ __('Save Budget Configuration') }}</x-prsystem::primary-button>
                            <a href="{{ route('admin.budgets.index') }}" class="text-gray-600 hover:text-gray-900">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
