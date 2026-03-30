<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 z-[9999] shadow-lg">
    @php
        $prRole = auth()->user()?->moduleRole('pr');
        $canSeeApproval = in_array($prRole, ['Approver', 'Admin'], true);
    @endphp

    <div class="flex justify-around items-center h-16">
        <a href="{{ route('pr.dashboard') }}" class="flex flex-col items-center justify-center flex-1 text-gray-600 hover:text-primary-600 {{ request()->routeIs('pr.dashboard') ? 'text-primary-600' : '' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[10px] mt-1">Home</span>
        </a>
        
        <a href="{{ route('pr.index') }}" class="flex flex-col items-center justify-center flex-1 text-gray-600 hover:text-primary-600 {{ request()->routeIs('pr.*') ? 'text-primary-600' : '' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <span class="text-[10px] mt-1">PR</span>
        </a>

        @if($canSeeApproval)
        <a href="{{ route('approval.index') }}" class="flex flex-col items-center justify-center flex-1 text-gray-600 hover:text-primary-600 {{ request()->routeIs('approval.*') ? 'text-primary-600' : '' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-[10px] mt-1">Approval</span>
        </a>
        @endif

        <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center flex-1 text-gray-600 hover:text-primary-600 {{ request()->routeIs('profile.*') ? 'text-primary-600' : '' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-[10px] mt-1">Profil</span>
        </a>
    </div>
</nav>
