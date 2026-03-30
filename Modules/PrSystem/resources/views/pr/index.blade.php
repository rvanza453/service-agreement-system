<x-prsystem::app-layout>
    <div class="space-y-6">
        <!-- Header & Actions -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Daftar Pengajuan PR</h2>
                <p class="text-sm text-gray-500">Monitor dan kelola pengajuan purchase request.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('pr.export', request()->query()) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </a>
                <a href="{{ route('pr.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-500 focus:bg-primary-500 active:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat PR Baru
                </a>
            </div>
        </div>

        <!-- View Mode Switcher -->
        <div class="flex justify-end mb-4">
            <div class="bg-gray-100 p-1 rounded-lg inline-flex">
                <a href="{{ request()->fullUrlWithQuery(['view_mode' => 'pr']) }}" 
                   class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ ($viewMode ?? 'pr') === 'pr' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                   View By No. PR
                </a>
                <a href="{{ request()->fullUrlWithQuery(['view_mode' => 'items']) }}" 
                   class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ ($viewMode ?? 'pr') === 'items' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                   View By Items
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ filterOpen: {{ request()->hasAny(['search', 'status', 'department_id', 'sub_department_id', 'current_approver_id', 'start_date', 'end_date', 'sort']) || session()->has('pr_filters') ? 'true' : 'false' }} }">
            <!-- Filter Header -->
            <div class="px-5 py-3 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 flex items-center justify-between cursor-pointer" @click="filterOpen = !filterOpen">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-700">Filter & Pencarian</h3>
                    @if(request()->hasAny(['search', 'status', 'department_id', 'sub_department_id', 'current_approver_id', 'start_date', 'end_date', 'sort']) || session()->has('pr_filters'))
                        <span class="px-2 py-0.5 bg-primary-100 text-primary-700 text-xs font-medium rounded-full">Aktif</span>
                    @endif
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': filterOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <!-- Filter Content -->
            <div x-show="filterOpen" x-collapse>
                <form method="GET" action="{{ route('pr.index') }}" class="p-5">
                    <input type="hidden" name="view_mode" value="{{ $viewMode ?? 'pr' }}">
                    <input type="hidden" name="filter_active" value="1">
                    
                    <div class="space-y-4">
                        <!-- Row 1: Search & Quick Filters -->
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                            <!-- Search -->
                            <div class="md:col-span-2">
                                <label for="search" class="block text-xs font-medium text-gray-700 mb-1.5">Pencarian</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" 
                                    placeholder="{{ $viewMode === 'items' ? 'Nama Barang / No. PR' : 'No. PR / Keterangan' }}">
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-xs font-medium text-gray-700 mb-1.5">Status Approval</label>
                                <select name="status" id="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" onchange="this.form.submit()">
                                    <option value="">Semua</option>
                                    @foreach(['Pending', 'On Hold', 'Approved', 'Waiting PO', 'Complete PO', 'Rejected'] as $stat)
                                        <option value="{{ $stat }}" {{ request('status') == $stat ? 'selected' : '' }}>{{ $stat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Department -->
                            <div>
                                <label for="department_id" class="block text-xs font-medium text-gray-700 mb-1.5">Unit</label>
                                <select name="department_id" id="department_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" onchange="this.form.submit()">
                                    <option value="">Semua</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Sub Department -->
                            <div>
                                <label for="sub_department_id" class="block text-xs font-medium text-gray-700 mb-1.5">Stasiun/Afdeling</label>
                                <select name="sub_department_id" id="sub_department_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" onchange="this.form.submit()">
                                    <option value="">Semua</option>
                                    @foreach($departments as $dept)
                                        @if(request('department_id') && request('department_id') != $dept->id)
                                            @continue
                                        @endif
                                        <optgroup label="{{ $dept->name }}">
                                            @foreach($dept->subDepartments as $sub)
                                                <option value="{{ $sub->id }}" {{ request('sub_department_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Approver Filter -->
                            <div>
                                <label for="current_approver_id" class="block text-xs font-medium text-gray-700 mb-1.5">Pending di</label>
                                <select name="current_approver_id" id="current_approver_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" onchange="this.form.submit()">
                                    <option value="">Semua Approver</option>
                                    @foreach($approvers as $approver)
                                        <option value="{{ $approver->id }}" {{ request('current_approver_id') == $approver->id ? 'selected' : '' }}>{{ $approver->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Row 2: Date Range & Actions -->
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
                            <!-- Date Range -->
                            <div class="md:col-span-2 grid grid-cols-2 gap-2">
                                <div>
                                    <label for="start_date" class="block text-xs font-medium text-gray-700 mb-1.5">Dari Tanggal</label>
                                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                </div>
                                <div>
                                    <label for="end_date" class="block text-xs font-medium text-gray-700 mb-1.5">Sampai Tanggal</label>
                                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                </div>
                            </div>

                            <!-- Spacer or additional fields can go here -->
                            <div class="md:col-span-1">
                                <label for="sort" class="block text-xs font-medium text-gray-700 mb-1.5">Urutkan</label>
                                <select name="sort" id="sort" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" onchange="this.form.submit()">
                                    <option value="terbaru" {{ request('sort', 'terbaru') == 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                                    <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Terlama</option>
                                    <option value="expired" {{ request('sort') == 'expired' ? 'selected' : '' }}>Mendekati Expired</option>
                                </select>
                            </div>
                            
                            <!-- Spacer for formatting -->
                            <div class="md:col-span-1"></div>

                            <!-- Actions -->
                            <div class="md:col-span-2 flex gap-2">
                                <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 shadow-sm transition-colors duration-150 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    Terapkan
                                </button>
                                <a href="{{ route('pr.index', ['reset' => 1, 'view_mode' => $viewMode ?? 'pr']) }}" class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 text-center shadow-sm transition-colors duration-150 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                @if(($viewMode ?? 'pr') === 'items')
                    <!-- ITEMS VIEW -->
                     <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Barang</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Qty</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Satuan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dari PR</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Unit</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($items as $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $item->item_name }}</div>
                                        @if($item->product)
                                             <div class="text-xs text-gray-500">{{ $item->product->code }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        {{ $item->final_quantity }}
                                        @if($item->quantity != $item->final_quantity)
                                            <span class="text-xs font-normal text-gray-400 line-through ml-1">{{ $item->quantity }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $item->unit }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('pr.show', $item->purchaseRequest) }}" class="text-primary-600 hover:text-primary-800 hover:underline font-medium">
                                            {{ $item->purchaseRequest->pr_number }}
                                        </a>

                                        <div class="text-xs text-gray-500">{{ $item->purchaseRequest->request_date->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $item->purchaseRequest->department->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                       @php
                                            $status = $item->purchaseRequest->status;
                                            $class = match($status) {
                                                'Approved' => 'bg-green-100 text-green-800',
                                                'Pending' => 'bg-yellow-100 text-yellow-800',
                                                'Rejected' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $class }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                        <span class="block text-sm font-medium text-gray-900">Tidak ada item ditemukan.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <!-- PR VIEW (Default) -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No. PR / Tanggal</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Unit</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total Harga</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Approval</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($prs as $pr)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-bold text-primary-600">{{ $pr->pr_number }}</div>

                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $pr->request_date->format('d M Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $pr->department->name ?? '-' }}
                                        @if($pr->subDepartment)
                                            <div class="text-xs text-gray-400">({{ $pr->subDepartment->name }})</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                        Rp {{ number_format($pr->final_total, 0, ',', '.') }}
                                    </td>
                                    
                                    <!-- Approval Column -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $approvalStatus = $pr->status;
                                            
                                            // Check if PR is on hold with reply
                                            $hasReply = false;
                                            if ($approvalStatus === 'On Hold') {
                                                $holdApproval = $pr->approvals()
                                                    ->where('status', 'On Hold')
                                                    ->whereNotNull('hold_reply')
                                                    ->first();
                                                $hasReply = $holdApproval !== null;
                                            }
                                            
                                            $approvalClass = match($approvalStatus) {
                                                'Pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'On Hold' => $hasReply ? 'bg-blue-100 text-blue-800 border-blue-200' : 'bg-orange-100 text-orange-800 border-orange-200',
                                                'Approved', 'PO Created' => 'bg-green-100 text-green-800 border-green-200',
                                                'Rejected' => 'bg-red-100 text-red-800 border-red-200',
                                                default => 'bg-gray-100 text-gray-800 border-gray-200',
                                            };
                                            
                                            $approvalIcon = match($approvalStatus) {
                                                'Pending' => '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                                                'On Hold' => $hasReply ? '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/><path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/></svg>' : '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                                                'Approved', 'PO Created' => '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
                                                'Rejected' => '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
                                                default => ''
                                            };
                                            
                                            $currentApprover = ($approvalStatus === 'Pending' || $approvalStatus === 'On Hold') ? $pr->getCurrentApprover() : null;
                                            $displayStatus = $approvalStatus === 'PO Created' ? 'Approved' : $approvalStatus;
                                            
                                            // Update display status for on hold with reply
                                            if ($approvalStatus === 'On Hold' && $hasReply) {
                                                $displayStatus = 'On Hold (Replied)';
                                            }
                                            
                                            $displayText = $currentApprover ? $displayStatus . ' / ' . $currentApprover->name : $displayStatus;
                                        @endphp
                                        <span class="px-2.5 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full border {{ $approvalClass }}">
                                            {!! $approvalIcon !!}
                                            {{ $displayText }}
                                        </span>
                                    </td>

                                    <!-- Status Column -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $executionStatus = '-';
                                            $executionClass = 'text-gray-500';
                                            $isExpired = $pr->isExpired();

                                            if ($pr->status === 'Approved' || $pr->status === 'PO Created') {
                                                $poStatus = $pr->po_status;
                                                
                                                if ($isExpired && $poStatus !== 'Complete PO') {
                                                    $executionStatus = 'Expired';
                                                    $executionClass = 'bg-gray-100 text-gray-800 border-gray-200 border';
                                                } else {
                                                    $executionStatus = match($poStatus) {
                                                        'Waiting PO' => 'Waiting PO',
                                                        'Partial PO' => 'Partial PO',
                                                        'Complete PO' => 'Complete PO',
                                                        default => '-'
                                                    };
                                                    
                                                    $executionClass = match($executionStatus) {
                                                        'Waiting PO' => 'bg-green-50 text-green-600 border-green-200 border', 
                                                        'Partial PO' => 'bg-blue-50 text-blue-600 border-blue-200 border',
                                                        'Complete PO' => 'bg-blue-100 text-blue-800 border-blue-200 border',
                                                        'Expired' => 'bg-gray-100 text-gray-800 border-gray-200 border',
                                                        default => 'text-gray-500'
                                                    };
                                                }
                                            }
                                        @endphp
                                        
                                        @if($executionStatus !== '-')
                                            <span class="px-2.5 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full {{ $executionClass }}">
                                                {{ $executionStatus }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                        <a href="{{ route('pr.show', $pr) }}" class="text-primary-600 hover:text-primary-900 bg-primary-50 px-3 py-1.5 rounded-md hover:bg-primary-100 transition-colors">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="mt-2 block text-sm font-medium text-gray-900">Belum ada data pengajuan.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                @if(($viewMode ?? 'pr') === 'items' && $items)
                     {{ $items->withQueryString()->links() }}
                @elseif($prs)
                     {{ $prs->withQueryString()->links() }}
                @endif
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
