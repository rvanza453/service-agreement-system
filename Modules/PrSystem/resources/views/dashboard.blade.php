<x-prsystem::app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Card 1 -->
            <a href="{{ route('pr.index', ['status' => \Modules\PrSystem\Enums\PrStatus::PENDING->value]) }}" 
                class="bg-white rounded-xl shadow-sm p-6 flex flex-col items-center justify-center border-b-4 border-yellow-400 hover:scale-[1.02] transition-all cursor-pointer hover:shadow-md group">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Menunggu</span>
                <span class="text-sm font-bold text-yellow-600 mb-2 group-hover:text-yellow-700">Pending Approval</span>
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-3xl font-bold text-gray-800">{{ $stats['pending_approval'] }}</span>
                </div>
            </a>

            <!-- Card 2 -->
             <a href="{{ route('pr.index', ['status' => \Modules\PrSystem\Enums\PrStatus::APPROVED->value]) }}" 
                class="bg-white rounded-xl shadow-sm p-6 flex flex-col items-center justify-center border-b-4 border-blue-400 hover:scale-[1.02] transition-all cursor-pointer hover:shadow-md group">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Menunggu</span>
                <span class="text-sm font-bold text-blue-600 mb-2 group-hover:text-blue-700">Waiting PO</span>
                 <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span class="text-3xl font-bold text-gray-800">{{ $stats['waiting_po'] }}</span>
                </div>
            </a>

            <!-- Card 3 -->
            <a href="{{ route('pr.index', ['status' => \Modules\PrSystem\Enums\PrStatus::REJECTED->value]) }}" 
                class="bg-white rounded-xl shadow-sm p-6 flex flex-col items-center justify-center border-b-4 border-red-400 hover:scale-[1.02] transition-all cursor-pointer hover:shadow-md group">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</span>
                <span class="text-sm font-bold text-red-600 mb-2 group-hover:text-red-700">Rejected</span>
                 <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span class="text-3xl font-bold text-gray-800">{{ $stats['rejected'] }}</span>
                </div>
            </a>

            <!-- Card 4 -->
            <a href="{{ route('po.index', ['status' => 'Completed']) }}" 
                class="bg-white rounded-xl shadow-sm p-6 flex flex-col items-center justify-center border-b-4 border-green-400 hover:scale-[1.02] transition-all cursor-pointer hover:shadow-md group">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</span>
                <span class="text-sm font-bold text-green-600 mb-2 group-hover:text-green-700">PO Completed</span>
                 <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-3xl font-bold text-gray-800">{{ $stats['po_completed'] }}</span>
                </div>
            </a>
        </div>

        <!-- Budget Summary Grid -->
        <h3 class="text-lg font-bold text-gray-800">Department Budget Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($departmentBudgets as $dept)
                <a href="{{ route('pr.index', ['department_id' => $dept->id]) }}" 
                    class="bg-white rounded-xl shadow-sm p-4 border-l-4 {{ $dept->remaining_budget < 0 ? 'border-red-500' : 'border-green-500' }} hover:scale-[1.01] transition-all cursor-pointer hover:shadow-md group">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 uppercase group-hover:text-gray-700 transition-colors">{{ $dept->name }}</h4>
                            <span class="text-xs text-gray-400">{{ $dept->site->name }}</span>
                        </div>
                        <span class="text-xs font-bold px-2 py-1 rounded {{ $dept->remaining_budget < 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{ $dept->remaining_budget < 0 ? 'Over Budget' : 'Safe' }}
                        </span>
                    </div>
                    <div class="mt-3">
                        <span class="block text-2xl font-bold {{ $dept->remaining_budget < 0 ? 'text-red-600' : 'text-gray-800' }}">
                            Rp {{ number_format($dept->remaining_budget, 0, ',', '.') }}
                        </span>
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Remaining Balance</span>
                            <span title="Allocated: {{ number_format($dept->calculated_budget) }} | Used: {{ number_format($dept->used_budget) }}">
                                (Alloc: {{ number_format($dept->calculated_budget, 0, ',', '.') }})
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
</x-prsystem::app-layout>
