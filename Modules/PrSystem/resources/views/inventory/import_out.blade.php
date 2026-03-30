<x-prsystem::app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Inventory (Stock In / Out)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                             <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Instructions</h3>
                        <p class="text-sm text-gray-600 mb-2">
                            Upload a CSV file with the following columns (order matters):
                        </p>
                        <ul class="list-decimal list-inside text-xs text-gray-500 font-mono bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <li>Date (YYYY-MM-DD or DD/MM/YYYY)</li>
                            <li>Doc ID (Reference Number)</li>
                            <li>Module (STOCK-OUT / GOOD-RECEIVED)</li>
                            <li>COA ID (Job Code - 4 segments x.x.x.xx)</li>
                            <li>Station/Afdeling (Linked to SubDepartment)</li>
                            <li>Warehouse (Ref Only) </li>
                            <li>Warehouse (REAL STOCK LOCATION) - Col 7</li>
                            <li>Item ID (Product Code)</li>
                            <li>Item Name</li>
                            <li>Unit</li>
                            <li>Qty IN (Stock In)</li>
                            <li>Price IN (Stock In)</li>
                            <li>Total IN</li>
                            <li>Qty OUT (Stock Out)</li>
                            <li>Price OUT (Stock Out)</li>
                        </ul>
                        <p class="text-xs text-red-500 mt-2">Note: Budget will be deducted for Stock Out based on COA or Station.</p>
                    </div>

                    <form action="{{ route('inventory.import.out.process') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Select Target Warehouse (Default for Import)</label>
                            <select name="warehouse_id" id="warehouse_id" required
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">-- Select Warehouse --</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }} (Site: {{ $warehouse->site->name ?? '-' }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">If "Warehouse" column in CSV is empty or not found, this warehouse will be used.</p>
                        </div>

                        <div>
                            <label for="override_department_id" class="block text-sm font-medium text-gray-700">Filter Department (Optional - Manual Override)</label>
                            <select name="override_department_id" id="override_department_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">-- Auto Detect (Use Warehouse Link) --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }} (Site: {{ $dept->site->name ?? '-' }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-red-500 mt-1">Select a Department ONLY if you want to force the import to check budget for THAT specific department (e.g. choose "KDE" for Gudang KDE import).</p>
                        </div>


                        
                        <div>
                            <label for="file" class="block text-sm font-medium text-gray-700">Choose CSV File</label>
                            <input type="file" name="file" id="file" accept=".csv,.txt" required
                                class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100
                                border border-gray-300 rounded-md
                                ">
                        </div>

                        <div class="flex items-center gap-4 mt-6">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Import Stock OUT
                            </button>
                            <a href="{{ route('inventory.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
