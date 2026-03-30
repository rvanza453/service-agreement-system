@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">ISPO Master Data Management</h1>
    <button onclick="openModal('create', null, 'principle')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
        + Add New Principle
    </button>
</div>

<div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
    <div class="space-y-6">
        @foreach($principles as $principle)
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                <!-- Principle Header -->
                <div class="bg-gray-100 dark:bg-gray-700 px-4 py-3 flex justify-between items-center border-b border-gray-200 dark:border-gray-600">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mr-2">
                            Principle
                        </span>
                        <h3 class="font-bold text-lg text-gray-800 dark:text-white inline-block">
                            {{ $principle->code ? $principle->code . ' - ' : '' }}{{ $principle->name }}
                        </h3>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="openModal('edit', {{ $principle }})" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">Edit</button>
                        <button onclick="confirmDelete({{ $principle->id }})" class="text-xs text-red-600 hover:text-red-800 font-semibold">Delete</button>
                        <button onclick="openModal('create', {{ $principle->id }}, 'criteria')" class="text-xs bg-indigo-600 text-white px-2 py-1 rounded hover:bg-indigo-700">+ Criteria</button>
                    </div>
                </div>

                <!-- Children (Criteria) -->
                <div class="bg-white dark:bg-gray-800 p-4 space-y-4">
                    @foreach($principle->children as $criteria)
                        <div class="border-l-4 border-indigo-200 dark:border-indigo-600 pl-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200 mr-1">
                                        Criteria
                                    </span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">
                                        {{ $criteria->code ? $criteria->code . ' - ' : '' }}{{ $criteria->name }}
                                    </span>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="openModal('edit', {{ $criteria }})" class="text-xs text-blue-500 hover:text-blue-700">Edit</button>
                                    <button onclick="confirmDelete({{ $criteria->id }})" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                                    <button onclick="openModal('create', {{ $criteria->id }}, 'indicator')" class="text-xs bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded hover:bg-green-100">+ Indicator</button>
                                </div>
                            </div>

                            <!-- Children (Indicator) -->
                            <div class="ml-4 space-y-3 mt-2">
                                @foreach($criteria->children as $indicator)
                                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded p-3 border border-gray-100 dark:border-gray-700">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-50 text-green-700 dark:bg-green-900 dark:text-green-200 mr-1">
                                                    Indicator
                                                </span>
                                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                    {{ $indicator->code ? $indicator->code . ' - ' : '' }}{{ $indicator->name }}
                                                </span>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button onclick="openModal('edit', {{ $indicator }})" class="text-xs text-blue-400 hover:text-blue-600">Edit</button>
                                                <button onclick="confirmDelete({{ $indicator->id }})" class="text-xs text-red-400 hover:text-red-600">Delete</button>
                                                <button onclick="openModal('create', {{ $indicator->id }}, 'parameter')" class="text-xs bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 px-1.5 py-0.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700">+ Param</button>
                                                <button onclick="openModal('create', {{ $indicator->id }}, 'verifier')" class="text-xs bg-white dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50 px-1.5 py-0.5 rounded hover:bg-indigo-50 dark:hover:bg-indigo-900/50">+ Verifier</button>
                                            </div>
                                        </div>

                                        <!-- Children (Parameter/Verifier) -->
                                        <div class="ml-2 pl-2 border-l border-gray-300 dark:border-gray-600 space-y-2">
                                            @foreach($indicator->children as $child)
                                                <!-- Parameter/Verifier Item -->
                                                <div class="flex justify-between items-start text-xs group">
                                                    <div class="flex-1 pr-2">
                                                        @if($child->type === 'verifier')
                                                            <span class="text-indigo-600 dark:text-indigo-400 font-medium">[Verifier]</span>
                                                        @else
                                                            <span class="text-gray-500 dark:text-gray-400 font-medium">[Parameter]</span>
                                                        @endif
                                                        <span class="text-gray-700 dark:text-gray-300">{{ $child->name }}</span>
                                                    </div>
                                                    <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <button onclick="openModal('edit', {{ $child }})" class="text-blue-400 hover:text-blue-600">Edit</button>
                                                        <button onclick="confirmDelete({{ $child->id }})" class="text-red-400 hover:text-red-600">Del</button>
                                                        @if($child->type === 'parameter')
                                                            <button onclick="openModal('create', {{ $child->id }}, 'verifier')" class="text-indigo-500 hover:text-indigo-700">+ Verifier</button>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Verifiers under Parameter -->
                                                @if($child->children->count() > 0)
                                                    <div class="ml-2 mt-1 space-y-1">
                                                        @foreach($child->children as $verifier)
                                                            <div class="flex justify-between items-center text-xs pl-2 border-l border-indigo-200 group">
                                                                <span class="text-gray-600 dark:text-gray-400 flex-1">
                                                                    <span class="text-indigo-500">[V]</span> {{ $verifier->name }}
                                                                </span>
                                                                <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                    <button onclick="openModal('edit', {{ $verifier }})" class="text-blue-400 hover:text-blue-600">Edit</button>
                                                                    <button onclick="confirmDelete({{ $verifier->id }})" class="text-red-400 hover:text-red-600">Del</button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal -->
<div id="itemModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 dark:bg-black bg-opacity-75 dark:bg-opacity-60 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="itemForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="parent_id" id="parentId">
                
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modalTitle">Add Item</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                            <input type="text" name="type" id="itemType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white sm:text-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex justify-between">
                                Kode (Opsional)
                                <span class="text-[10px] text-gray-400 font-normal">Tidak perlu diisi untuk Param/Verifier</span>
                            </label>
                            <input type="text" name="code" id="itemCode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Item</label>
                            <textarea name="name" id="itemName" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white sm:text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi/Keterangan</label>
                            <textarea name="description" id="itemDescription" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white sm:text-sm"></textarea>
                        </div>
                        <!-- Order Index is now hidden and handled automatically -->
                        <input type="hidden" name="order_index" id="itemOrder" value="0">
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t dark:border-gray-700">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-base font-medium text-white hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Save</button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function openModal(mode, data = null, childType = null) {
        document.getElementById('itemModal').classList.remove('hidden');
        const form = document.getElementById('itemForm');
        
        if (mode === 'create') {
            document.getElementById('modalTitle').innerText = 'Add New ' + ucfirst(childType);
            form.action = "{{ route('ispo.admin.items.store') }}";
            document.getElementById('formMethod').value = 'POST';
            
            document.getElementById('parentId').value = data; // data = parent_id in create mode
            document.getElementById('itemType').value = childType;
            
            document.getElementById('itemCode').value = '';
            document.getElementById('itemName').value = '';
            document.getElementById('itemDescription').value = '';
            document.getElementById('itemOrder').value = 0;
        } else {
            // Edit
            document.getElementById('modalTitle').innerText = 'Edit Item';
            form.action = "/ispo/admin/items/" + data.id;
            document.getElementById('formMethod').value = 'PUT';
            
            document.getElementById('parentId').value = data.parent_id;
            document.getElementById('itemType').value = data.type;
            document.getElementById('itemCode').value = data.code;
            document.getElementById('itemName').value = data.name;
            document.getElementById('itemDescription').value = data.description;
            document.getElementById('itemOrder').value = data.order_index;
        }
    }

    function closeModal() {
        document.getElementById('itemModal').classList.add('hidden');
    }

    function confirmDelete(id) {
        if(confirm('Are you sure you want to delete this item?')) {
            const form = document.getElementById('deleteForm');
            form.action = "/ispo/admin/items/" + id;
            form.submit();
        }
    }

    function ucfirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
</script>
@endsection
