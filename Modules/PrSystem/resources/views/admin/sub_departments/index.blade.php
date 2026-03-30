<x-prsystem::app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Sub Departments') }}
            </h2>
            <a href="{{ route('sub-departments.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Add New Sub Department
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3">Department</th>
                                    <th class="px-6 py-3">Sub Department Name</th>
                                    <th class="px-6 py-3">COA</th>
                                    <th class="px-6 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($subDepartments as $sub)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $sub->department->name }}</td>
                                        <td class="px-6 py-4">{{ $sub->name }}</td>
                                        <td class="px-6 py-4">{{ $sub->coa ?? '-' }}</td>
                                        <td class="px-6 py-4 text-center space-x-2">
                                            <a href="{{ route('sub-departments.edit', $sub) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            <form action="{{ route('sub-departments.destroy', $sub) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center">No Sub Departments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
