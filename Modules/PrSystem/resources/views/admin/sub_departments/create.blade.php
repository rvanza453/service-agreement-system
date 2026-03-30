<x-prsystem::app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Sub Department') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('sub-departments.store') }}" class="space-y-6 max-w-xl">
                        @csrf

                        <div>
                            <x-prsystem::input-label for="department_id" :value="__('Parent Department')" />
                            <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm">
                                <option value="">-- Select Department --</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }} ({{ $department->site->name ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            <x-prsystem::input-error :messages="$errors->get('department_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-prsystem::input-label for="name" :value="__('Name')" />
                            <x-prsystem::text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-prsystem::input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-prsystem::input-label for="coa" :value="__('COA (Optional)')" />
                            <x-prsystem::text-input id="coa" class="block mt-1 w-full" type="text" name="coa" :value="old('coa')" />
                            <x-prsystem::input-error :messages="$errors->get('coa')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-prsystem::primary-button>{{ __('Save Sub Department') }}</x-prsystem::primary-button>
                            <a href="{{ route('sub-departments.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
