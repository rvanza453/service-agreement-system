<x-prsystem::app-layout>
    @php
        $prRole = auth()->user()?->moduleRole('pr');
        $isPrAdmin = $prRole === 'Admin';

        // Find the next approval step (lowest level pending or on hold)
        $pendingApprovals = $pr->approvals->whereIn('status', ['Pending', 'On Hold'])->sortBy('level');
        $lowestLevel = $pendingApprovals->first()?->level;
        
        $canApprove = false;
        $currentApproval = null;
        
        if ($lowestLevel) {
            // Check if current user is an approver at this specific level
            $myApproval = $pendingApprovals->where('level', $lowestLevel)
                                         ->where('approver_id', auth()->id())
                                         ->first();
            
            if ($myApproval) {
                $canApprove = true;
                $currentApproval = $myApproval;
            } elseif ($isPrAdmin) {
                // Admin can approve/override (taking the first available slot at this level)
                $canApprove = true;
                $currentApproval = $pendingApprovals->first();
            }
        }
        
        // Define HO as Admin OR Global Approver
        $isHO = $isPrAdmin || \Modules\PrSystem\Models\GlobalApproverConfig::where('user_id', auth()->id())->exists();
        
        // Status checks with robust comparison
        $isApproved = trim(strtolower($pr->status)) === trim(strtolower(\Modules\PrSystem\Enums\PrStatus::APPROVED->value));
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Detail Pengajuan PR</h2>
                <div class="text-sm text-gray-500">Nomor: {{ $pr->pr_number }}</div>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $statusColor = match($pr->status) {
                        'Pending' => 'bg-yellow-100 text-yellow-800',
                        'On Hold' => 'bg-orange-100 text-orange-800',
                        'Approved' => 'bg-green-100 text-green-800',
                        'Rejected' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                @endphp
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusColor }}">
                    {{ $pr->status }}
                </span>
                
                @if($isApproved)
                    <a href="{{ route('pr.export.pdf', $pr) }}" 
                       class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export PDF
                    </a>
                @endif
            </div>
        </div>

        <!-- Details Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            @if(isset($budgetWarnings) && count($budgetWarnings) > 0)
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <div class="font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Peringatan Budget:
                    </div>
                    <ul class="list-disc list-inside text-sm mt-1">
                        @foreach($budgetWarnings as $warning)
                            <li>{!! $warning !!}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="block text-xs text-gray-400 uppercase">Pemohon</span>
                    <span class="block text-sm font-medium text-gray-800">{{ $pr->user->name }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400 uppercase">Tanggal</span>
                    <span class="block text-sm font-medium text-gray-800">{{ $pr->request_date->format('d M Y') }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400 uppercase">Jenis/Pekerjaan/Unit/Stadium/Kategori</span>
                    <span class="block text-sm font-medium text-gray-800">
                        {{ $pr->department->name ?? '-' }}
                        @if($pr->subDepartment)
                             / {{ $pr->subDepartment->name }}
                        @endif
                        @php
                            $budgetType = $pr->department->budget_type;
                            $job = $pr->items->first()->job ?? null;
                            
                            $isJob = $budgetType === \Modules\PrSystem\Enums\BudgetingType::JOB_COA;
                            $isStation = $budgetType === \Modules\PrSystem\Enums\BudgetingType::STATION;
                        @endphp

                        @if($isJob && $job)
                             / {{ $job->code ?? '' }}{{ $job->code ? '-' : '' }}{{ $job->name }}
                        @elseif($isStation && $pr->subDepartment && $pr->subDepartment->coa)
                             / {{ $pr->subDepartment->coa }}
                        @endif
                    </span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400 uppercase">Total Estimasi</span>
                    <span class="block text-sm font-medium text-gray-800">Rp {{ number_format($pr->total_estimated_cost, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400 uppercase">Lampiran File</span>
                    @if($pr->attachment_path)
                         <div class="flex items-center gap-2 mt-1">
                             <a href="{{ Storage::url($pr->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                             </a>
                             <a href="{{ route('pr.attachment.download', $pr) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download
                             </a>
                         </div>
                    @else
                         <span class="text-sm text-gray-400">-</span>
                    @endif
                </div>
            </div>
            
            <div class="pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                     <span class="block text-xs text-gray-400 uppercase mb-1">Keterangan</span>
                     <p class="text-sm text-gray-600">{{ $pr->description }}</p>
                </div>
                
                @if(isset($budgetInfo))
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-3 border-b border-gray-200 pb-2">Informasi Anggaran</h4>
                    <table class="w-full text-sm">
                        <tr>
                            <td class="text-gray-600 py-1">Total Anggaran</td>
                            <td class="text-gray-400 px-2">:</td>
                            <td class="font-medium text-gray-900 text-right">Rp {{ number_format($budgetInfo['total'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-gray-600 py-1">Actual Pengeluaran</td>
                            <td class="text-gray-400 px-2">:</td>
                            <td class="font-medium text-gray-900 text-right">Rp {{ number_format($budgetInfo['actual'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-gray-600 py-1">Permintaan Saat Ini</td>
                            <td class="text-gray-400 px-2">:</td>
                            <td class="font-medium text-gray-900 text-right">Rp {{ number_format($budgetInfo['current'], 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-t border-gray-200">
                            <td class="text-gray-800 font-bold py-2">Saldo Anggaran</td>
                            <td class="text-gray-400 px-2">:</td>
                            <td class="font-bold {{ $budgetInfo['saldo'] < 0 ? 'text-red-600' : 'text-green-600' }} text-right">Rp {{ number_format($budgetInfo['saldo'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
                @endif
            </div>
        </div>

        {{-- Hold Reply Section --}}
        @if($pr->status === 'On Hold')
            @php
                $holdApproval = $pr->approvals()
                    ->reorder()
                    ->where('status', 'On Hold')
                    ->orderBy('level', 'desc')
                    ->first();
            @endphp
            
            @if($holdApproval)
                <div class="bg-orange-50 border-2 border-orange-200 rounded-xl shadow-sm p-6 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-orange-100 rounded-lg text-orange-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                ⏸️ PR Ditunda (On Hold)
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Approver: <span class="font-semibold">{{ $holdApproval->approver->name }}</span> 
                                <span class="text-gray-400">• {{ $holdApproval->approved_at?->format('d M Y H:i') ?? '-' }}</span>
                            </p>
                            
                            <div class="mt-4 bg-white rounded-lg p-4 border border-orange-200">
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Alasan Hold:</p>
                                <p class="text-sm text-gray-800">{{ $holdApproval->remarks }}</p>
                            </div>

                            @if(auth()->id() == $pr->user_id)
                                @if($holdApproval->hold_reply)
                                    {{-- Show existing reply --}}
                                    <div class="mt-4 bg-blue-50 rounded-lg p-4 border border-blue-200">
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            <p class="text-xs font-semibold text-blue-700 uppercase">Balasan Anda</p>
                                            <span class="text-xs text-blue-500">{{ $holdApproval->replied_at->format('d M Y H:i') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-800">{{ $holdApproval->hold_reply }}</p>
                                        <div class="mt-3 flex items-center gap-2 text-sm text-blue-600">
                                            <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span class="font-medium">Menunggu review approver...</span>
                                        </div>
                                    </div>
                                @else
                                    {{-- Show reply form --}}
                                    <form method="POST" action="{{ route('pr.replyHold', $pr) }}" class="mt-4">
                                        @csrf
                                        <label for="hold_reply" class="block text-sm font-medium text-gray-700 mb-2">
                                            💬 Balasan Anda:
                                        </label>
                                        <textarea 
                                            name="hold_reply" 
                                            id="hold_reply" 
                                            rows="3" 
                                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm" 
                                            placeholder="Tulis balasan atau klarifikasi Anda disini..."
                                            required
                                        ></textarea>
                                        <div class="mt-3 flex justify-end">
                                            <button type="submit" class="px-5 py-2 bg-white-600 text-black font-bold rounded-lg hover:bg-orange-700 text-sm inline-flex items-center gap-2 transition shadow-md">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                Kirim Balasan
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            @else
                                {{-- For other users (approvers) - show reply if exists --}}
                                @if($holdApproval->hold_reply)
                                    <div class="mt-4 bg-blue-50 rounded-lg p-4 border border-blue-200">
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/><path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/></svg>
                                            <p class="text-xs font-semibold text-blue-700 uppercase">Balasan User</p>
                                            <span class="text-xs text-blue-500">{{ $holdApproval->replied_at->format('d M Y H:i') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-800">{{ $holdApproval->hold_reply }}</p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endif



        @if($canApprove)
        <form method="POST" id="approval-form">
            @csrf
        @endif

        <!-- Items Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-700 uppercase">Item Barang</h3>
                
                @php
                    $isExpired = $pr->isExpired();
                @endphp

                @if($isApproved)
                    @if($isExpired)
                        <div class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-sm font-bold border border-red-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            KADALUARSA (Expired)
                        </div>
                    @else
                        <div class="flex gap-2">
                            <button type="button" 
                                    onclick="addToCart()"
                                    id="add-to-cart-btn"
                                    disabled
                                    class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 disabled:opacity-50 disabled:cursor-not-allowed font-medium text-sm inline-flex items-center gap-2 transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                + Cart
                            </button>
                            
                            <button type="submit" 
                                    form="po-form"
                                    formaction="{{ route('po.create') }}"
                                    id="generate-po-btn" 
                                    disabled
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium text-sm inline-flex items-center gap-2 transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Generate PO
                            </button>
                        </div>
                    @endif
                @else
                    <!-- Status Not Approved: {{ $pr->status }} -->
                @endif
            </div>
            
            @if($isApproved)
                <form id="po-form" method="POST" action="{{ route('po.create') }}">
                    @csrf
                    <input type="hidden" name="pr_id" value="{{ $pr->id }}">
            @endif
            
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @if($isApproved)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" onchange="toggleAllCheckboxes(this)">
                            </th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Gudang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Original</th>
                        @if($canApprove && $isHO)
                           <th class="px-6 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider bg-blue-50">Adjust Qty</th>
                        @else
                           <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Final</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Link</th>
                        @if($isApproved)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status PO</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pr->items as $item)
                        @php
                            $hasPo = $item->hasPoGenerated();
                            $firstPo = $hasPo ? $item->getFirstPo() : null;
                        @endphp
                        <tr class="{{ $hasPo ? 'bg-gray-50' : '' }}">
                            @if($isApproved)
                                <td class="px-6 py-4 text-sm">
                                    @if(!$hasPo)
                                        <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" class="item-checkbox rounded border-gray-300 text-primary-600 focus:ring-primary-500" onchange="updateGenerateButton()">
                                    @else
                                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </td>
                            @endif

                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $item->product->code ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="font-medium">{{ $item->item_name }}</div>
                                @if($item->specification)
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $item->specification }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $item->remarks ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-semibold">
                                {{ number_format($item->current_stock ?? 0, 0, ',', '.') }} {{ $item->unit }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $item->quantity }}
                            </td>
                            
                            @if($canApprove)
                                <td class="px-6 py-4 text-sm text-gray-900 bg-blue-50">
                                    <input type="number" 
                                           name="adjusted_quantities[{{ $item->id }}]" 
                                           class="w-24 border-blue-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" 
                                           min="0" 
                                           step="1"
                                           placeholder="{{ $item->getFinalQuantity() }}"
                                           value="">
                                </td>
                            @else
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @php
                                        $finalQty = $item->getFinalQuantity();
                                        $hasAdjustment = $finalQty != $item->quantity;
                                    @endphp
                                    
                                    @if($hasAdjustment)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-400 line-through">{{ $item->quantity }}</span>
                                            <span class="font-bold text-blue-600">{{ $finalQty }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-900">{{ $finalQty }}</span>
                                    @endif
                                </td>
                            @endif

                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item->unit }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($item->price_estimation, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right">
                                @php
                                    $displayTotal = $item->subtotal;
                                    if ($isApproved) {
                                        $displayTotal = $item->getFinalQuantity() * $item->price_estimation;
                                    }
                                @endphp
                                Rp {{ number_format($displayTotal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($item->url_link)
                                    <a href="{{ $item->url_link }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 px-2 py-1 rounded border border-blue-200 hover:bg-blue-100 transition-colors" title="Buka Link Referensi">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Cek Link
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            @if($pr->status === 'Approved')
                                <td class="px-6 py-4 text-sm">
                                    @if($hasPo && $firstPo)
                                        <a href="{{ route('po.show', $firstPo) }}" class="inline-flex items-center gap-1 text-xs font-medium text-green-600 hover:text-green-800 bg-green-50 px-2 py-1 rounded border border-green-200 hover:bg-green-100 transition-colors">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            {{ $firstPo->po_number }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">Belum ada PO</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            @if($pr->status === 'Approved')
                </form>
                
                <script>
                    function toggleAllCheckboxes(source) {
                        const checkboxes = document.querySelectorAll('.item-checkbox');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = source.checked;
                        });
                        updateGenerateButton();
                    }
                    
                    function updateGenerateButton() {
                        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
                        const button = document.getElementById('generate-po-btn');
                        const cartBtn = document.getElementById('add-to-cart-btn');
                        const count = checkboxes.length;

                        button.disabled = count === 0;
                        if (cartBtn) cartBtn.disabled = count === 0;
                    }

                    async function addToCart() {
                        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
                        const items = Array.from(checkboxes).map(cb => cb.value);
                        
                        if (items.length === 0) return;

                        const btn = document.getElementById('add-to-cart-btn');
                        const originalContent = btn.innerHTML;
                        
                        // Loading state
                        btn.disabled = true;
                        btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Adding...';

                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                            const res = await fetch('{{ route("po.cart.add") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ items: items })
                            });

                            const data = await res.json();
                            
                            if (res.ok) {
                                // Trigger global event for the floating cart
                                window.dispatchEvent(new CustomEvent('cart-updated'));
                                
                                // Success state
                                btn.classList.remove('bg-yellow-500', 'hover:bg-yellow-600');
                                btn.classList.add('bg-green-500', 'hover:bg-green-600');
                                btn.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Added';
                                
                                setTimeout(() => {
                                    btn.innerHTML = originalContent;
                                    btn.classList.remove('bg-green-500', 'hover:bg-green-600');
                                    btn.classList.add('bg-yellow-500', 'hover:bg-yellow-600');
                                    btn.disabled = false;
                                }, 2000);
                            } else {
                                console.error('Server Error:', data);
                                alert('Gagal menambahkan ke keranjang: ' + (data.message || 'Error server'));
                                btn.disabled = false;
                                btn.innerHTML = originalContent;
                            }
                        } catch (error) {
                            console.error('Fetch Error:', error);
                            alert('Terjadi kesalahan jaringan.');
                            btn.disabled = false;
                            btn.innerHTML = originalContent;
                        }
                    }
                    
                    function submitPoForm() {
                        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
                        if (checkboxes.length === 0) {
                            alert('Silakan pilih minimal 1 item untuk generate PO.');
                            return;
                        }
                        document.getElementById('po-form').submit();
                    }
                </script>
            @endif
        </div>
        
        @if($canApprove)
            <!-- Approval Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6 space-y-4 mt-6">
                <h3 class="text-lg font-bold text-gray-800">Tindakan Approval</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan / Alasan (Opsional untuk Approve, Wajib untuk Reject/Hold)</label>
                    <textarea name="remarks" id="remarks-input" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-colors" placeholder="Tulis catatan disini..."></textarea>
                    <p id="remarks-error" class="hidden text-sm text-red-600 mt-1 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Mohon isi catatan/alasan terlebih dahulu.</span>
                    </p>
                </div>
                
                <div class="flex justify-end gap-3 pt-2">
                     @if($isPrAdmin)
                        <button type="button" 
                                onclick="submitFullApprove()"
                                style="background-color: #6b21a8; color: white;"
                                class="px-6 py-2.5 bg-purple-800 text-white font-bold rounded-lg hover:bg-purple-900 transition shadow-md hover:shadow-lg focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 flex items-center gap-2 mr-auto text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            FULL APPROVE
                        </button>
                     @endif
                     <button type="submit" 
                             formaction="{{ route('approval.reject', $currentApproval->id) }}"
                             onclick="return validateReject()"
                             class="px-6 py-2.5 bg-red-50 text-red-600 font-medium rounded-lg hover:bg-red-100 transition focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                         Reject PR
                     </button>
                     <button type="submit" 
                             formaction="{{ route('approval.hold', $currentApproval->id) }}"
                             onclick="return validateHold()"
                             class="px-6 py-2.5 bg-orange-50 text-orange-600 font-medium rounded-lg hover:bg-orange-100 transition focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                         Hold PR
                     </button>
                     <button type="submit" 
                             formaction="{{ route('approval.approve', $currentApproval->id) }}"
                             class="px-6 py-2.5 bg-primary-600 text-white font-bold rounded-lg hover:bg-primary-700 transition shadow-md hover:shadow-lg focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 flex items-center gap-2">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                         Approve PR
                     </button>
                </div>
            </div>
        </form>

        @if($isPrAdmin)
            <form id="full-approve-form" action="{{ route('pr.full-approve', $pr) }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="admin_password" id="full-approve-password">
            </form>
            <script>
                function submitFullApprove() {
                    const password = prompt("Masukkan password Super Admin untuk melakukan Full Approve (Semua Level):");
                    if (password === null) return;
                    document.getElementById('full-approve-password').value = password;
                    document.getElementById('full-approve-form').submit();
                }
            </script>
        @endif
        
        <script>
            function showError(message) {
                const errorEl = document.getElementById('remarks-error');
                const textarea = document.getElementById('remarks-input');
                
                errorEl.querySelector('span').innerText = message;
                errorEl.classList.remove('hidden');
                textarea.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                textarea.classList.remove('border-gray-300');
                textarea.focus();
                
                // Remove error on input
                textarea.addEventListener('input', function() {
                    errorEl.classList.add('hidden');
                    textarea.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                    textarea.classList.add('border-gray-300');
                }, { once: true });
            }

            function validateReject() {
                const remarks = document.getElementById('remarks-input').value;
                if (!remarks.trim()) {
                    showError('Mohon isi catatan/alasan untuk melakukan Reject.');
                    return false;
                }
                return true;
            }

            function validateHold() {
                const remarks = document.getElementById('remarks-input').value;
                if (!remarks.trim()) {
                    showError('Mohon isi catatan/alasan untuk menunda (Hold) pengajuan ini.');
                    return false;
                }
                return true;
            }
        </script>
        @endif

        <!-- Approval Timeline -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Riwayat Approval</h3>
            @php
                // Find the latest processed approval for the Undo button
                $latestProcessedId = $pr->approvals->where('status', '!=', 'Pending')->sortByDesc('level')->first()?->id;
            @endphp
            <div class="relative pl-6 border-l-2 border-gray-200 space-y-8">
                @foreach($pr->approvals as $approval)
                    <div class="relative">
                        <!-- Dot -->
                        <div class="absolute -left-[31px] bg-white border-2 {{ $approval->status === 'Approved' ? 'border-green-500' : ($approval->status === 'Rejected' ? 'border-red-500' : ($approval->status === 'On Hold' ? 'border-orange-500' : 'border-gray-300')) }} w-4 h-4 rounded-full"></div>
                        
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <span class="text-sm font-bold text-gray-900">{{ $approval->role_name }} ({{ $approval->approver->name }})</span>
                                <span class="block text-xs text-gray-400">Level {{ $approval->level }}</span>
                            </div>
                            <div class="mt-1 sm:mt-0 flex items-center gap-3">
                                @if($approval->status === 'Approved')
                                    <div class="text-right">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                        <span class="block text-xs text-gray-400 text-right">{{ $approval->approved_at ? $approval->approved_at->format('d M H:i') : '' }}</span>
                                    </div>
                                @elseif($approval->status === 'Rejected')
                                    <div class="text-right">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                        <span class="block text-xs text-gray-400 text-right">{{ $approval->approved_at ? $approval->approved_at->format('d M H:i') : '' }}</span>
                                    </div>
                                @elseif($approval->status === 'On Hold')
                                    <div class="text-right">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">On Hold</span>
                                        <span class="block text-xs text-gray-400 text-right">{{ $approval->approved_at ? $approval->approved_at->format('d M H:i') : '' }}</span>
                                    </div>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @endif

                                @if($isPrAdmin)
                                    @if($approval->id === $latestProcessedId)
                                        <form method="POST" action="{{ route('approval.revert', $approval->id) }}" class="inline ml-2" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan (Undo) proses approval ini? Status PR akan kembali ke posisi sebelumnya.')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-xs font-bold text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors flex items-center gap-1 shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                                Batal (Undo)
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @if($approval->remarks)
                            <div class="mt-2 text-sm text-orange-700 bg-orange-50 p-2 rounded border border-orange-100">
                                <span class="text-xs font-semibold uppercase text-orange-500">⏸ Catatan :</span>
                                "<i>{{ $approval->remarks }}</i>"
                            </div>
                        @endif
                        @if($approval->hold_reply)
                            <div class="mt-1 text-sm text-blue-700 bg-blue-50 p-2 rounded border border-blue-100 flex items-start gap-2">
                                <span class="text-xs font-semibold uppercase text-blue-500 whitespace-nowrap">💬 Balasan:</span>
                                <span><i>{{ $approval->hold_reply }}</i>
                                    @if($approval->replied_at)
                                        <span class="text-xs text-blue-400 ml-1">· {{ $approval->replied_at->format('d M H:i') }}</span>
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
