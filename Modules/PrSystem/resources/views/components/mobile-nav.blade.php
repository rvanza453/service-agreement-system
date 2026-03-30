<!-- Mobile Bottom Navigation -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 z-30 shadow-lg">
    <div class="flex justify-around items-center h-16 px-2">
        <a href="{{ route('pr.dashboard') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('pr.dashboard') ? 'text-indigo-600' : 'text-gray-600' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            <span class="text-xs mt-1 font-medium">Dashboard</span>
        </a>
        <a href="{{ route('pr.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('pr.*') ? 'text-indigo-600' : 'text-gray-600' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span class="text-xs mt-1 font-medium">PR</span>
        </a>
        <a href="{{ route('po.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('po.*') ? 'text-indigo-600' : 'text-gray-600' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <span class="text-xs mt-1 font-medium">PO</span>
        </a>
        @hasanyrole('Approver|Admin')
        <a href="{{ route('approval.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('approval.*') ? 'text-indigo-600' : 'text-gray-600' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-xs mt-1 font-medium">Approval</span>
        </a>
        @endhasanyrole
        <button onclick="toggleMobileDrawer()" class="flex flex-col items-center justify-center flex-1 py-2 text-gray-600 hover:text-indigo-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <span class="text-xs mt-1 font-medium">Menu</span>
        </button>
    </div>
</nav>

<!-- Mobile Drawer -->
<div x-show="mobileDrawerOpen" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="md:hidden fixed inset-0 z-40" 
     style="display: none;">
    <div @click="mobileDrawerOpen = false" class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div x-show="mobileDrawerOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="absolute left-0 top-0 bottom-0 w-80 max-w-[85vw] bg-white shadow-2xl overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800">Menu</h3>
                <button @click="mobileDrawerOpen = false" class="p-2 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-2">
                @hasanyrole('Purchasing|Admin|Warehouse')
                <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span class="font-medium">Inventory</span>
                </a>
                @endhasanyrole
                @role('Admin')
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="font-medium">Users</span>
                </a>
                <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span class="font-medium">Products</span>
                </a>
                @endrole
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="font-medium">Profile</span>
                </a>
                <div class="pt-4 mt-4 border-t border-gray-200">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-red-50 text-red-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span class="font-medium">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
