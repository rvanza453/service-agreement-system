<x-prsystem::app-layout>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold text-gray-800">Inbox Approval Pending</h2>

        {{-- PR Approvals --}}
        <h3 class="text-lg font-bold text-gray-600 mt-6 mb-4">Purchase Requests</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($approvals as $approval)
                @php
                    $isHold = $approval->status === 'On Hold';
                    $hasReply = $isHold && $approval->hold_reply !== null;
                    $borderColor = $isHold ? ($hasReply ? 'border-blue-400' : 'border-orange-400') : 'border-yellow-400';
                @endphp
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden border-l-4 {{ $borderColor }}">
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-5">
                            <div>
                                <span class="text-xs font-semibold text-gray-400 uppercase">PR Number</span>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-bold text-gray-800">{{ $approval->purchaseRequest->pr_number }}</h3>

                                    @if($isHold)
                                        @if($hasReply)
                                            <span class="bg-blue-100 text-blue-700 text-[10px] px-2 py-0.5 rounded-full font-bold border border-blue-200 uppercase inline-flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/><path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/></svg>
                                                Replied
                                            </span>
                                        @else
                                            <span class="animate-pulse bg-orange-100 text-orange-700 text-[10px] px-2 py-0.5 rounded-full font-bold border border-orange-200 uppercase">On Hold</span>
                                        @endif
                                    @endif
                                </div>
                                <span class="text-[10px] text-gray-400 font-medium">Diajukan: {{ $approval->purchaseRequest->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                        
                         <div class="space-y-3 mb-5">
                            <div>
                                <span class="text-xs text-gray-400 block mb-1">Items Requested</span>
                                <div class="text-sm font-medium text-gray-800 bg-gray-50 p-2 rounded-md">
                                    @php
                                        $itemNames = $approval->purchaseRequest->items->pluck('item_name');
                                        $displayItems = $itemNames->take(2)->implode(', ');
                                        $remainingCount = $itemNames->count() - 2;
                                    @endphp
                                    {{ $displayItems }}
                                    @if($remainingCount > 0)
                                        <span class="text-gray-500 text-xs italic">+ {{ $remainingCount }} more</span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-xs text-gray-400 block">Pemohon</span>
                                    <span class="text-sm font-medium">{{ $approval->purchaseRequest->user->name }}</span>
                                    <div class="text-xs text-gray-500">{{ $approval->purchaseRequest->department->name ?? '-' }}</div>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-400 block">Total Estimasi</span>
                                    <span class="text-lg font-bold text-gray-900">Rp {{ number_format($approval->purchaseRequest->total_estimated_cost, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-2 pt-4 border-t border-gray-100">
                            <a href="{{ route('pr.show', $approval->purchaseRequest) }}" class="flex-1 text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition">
                                Detail & Approval
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-6 text-gray-400 text-sm">
                    Tidak ada PR approval pending.
                </div>
            @endforelse
        </div>

        {{-- Capex Approvals --}}
        @if(isset($capexApprovals) && $capexApprovals->count() > 0)
            <div class="border-t pt-6">
                <div class="flex items-center gap-2 mb-4">
                    <h3 class="text-lg font-bold text-gray-600">Capex Requests</h3>
                    <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-0.5 rounded-full font-bold">Important</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($capexApprovals as $approval)
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden border-l-4 border-indigo-400">
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-5">
                                    <div>
                                        <span class="text-xs font-semibold text-gray-400 uppercase">Capex Number</span>
                                        <h3 class="text-lg font-bold text-gray-800">{{ $approval->capexRequest->capex_number }}</h3>
                                        <span class="text-[10px] text-gray-400 font-medium">Date: {{ $approval->capexRequest->created_at->format('d M Y') }}</span>
                                    </div>
                                    <span class="bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs px-3 py-1.5 rounded-full font-bold shadow-sm">
                                        Step {{ $approval->column_index }}
                                    </span>
                                </div>
                                
                                <div class="space-y-3 mb-5">
                                    <div>
                                        <span class="text-xs text-gray-400 block mb-1">Asset / Budget</span>
                                        <div class="text-sm font-medium text-gray-800 bg-gray-50 p-2 rounded-md">
                                            {{ $approval->capexRequest->capexBudget->capexAsset->name }}
                                            <div class="text-xs text-gray-500">{{ $approval->capexRequest->capexBudget->budget_code }}</div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-xs text-gray-400 block">Requester</span>
                                            <span class="text-sm font-medium">{{ $approval->capexRequest->user->name }}</span>
                                            <div class="text-xs text-gray-500">{{ $approval->capexRequest->department->name ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-400 block">Amount</span>
                                            <span class="text-lg font-bold text-gray-900">Rp {{ number_format($approval->capexRequest->amount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex gap-2 pt-4 border-t border-gray-100">
                                    <a href="{{ route('capex.show', $approval->capexRequest) }}" class="flex-1 text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition">
                                        Review & Sign
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-prsystem::app-layout>
