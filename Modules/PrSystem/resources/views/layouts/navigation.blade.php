<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    @php
        $prRole = auth()->user()?->moduleRole('pr');
        $canSeePo = in_array($prRole, ['Purchasing', 'Finance', 'Admin'], true);
        $canSeeApproval = in_array($prRole, ['Approver', 'Admin'], true);
        $canSeeInventory = in_array($prRole, ['Purchasing', 'Admin'], true);
        $isPrAdmin = $prRole === 'Admin';
        $canSeeBudgetMonitoring = in_array($prRole, ['Approver', 'Admin', 'Finance'], true);
    @endphp
    @if(session()->has('impersonate_admin_id'))
        <div class="bg-yellow-500 text-white px-4 py-2">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span class="font-semibold">You are currently logged in as: {{ Auth::user()->name }}</span>
                </div>
                <form action="{{ route('users.leave-impersonate') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-white text-yellow-600 px-4 py-1 rounded font-medium hover:bg-yellow-50 transition">
                        Leave Impersonation
                    </button>
                </form>
            </div>
        </div>
    @endif
    
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('pr.dashboard') }}">
                        <x-prsystem::application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-prsystem::nav-link :href="route('pr.dashboard')" :active="request()->routeIs('pr.dashboard')">
                        {{ __('Dashboard') }}
                    </x-prsystem::nav-link>

                    {{-- Everyone (staff, approver, admin) sees PR List --}}
                    <x-prsystem::nav-link :href="route('pr.index')" :active="request()->routeIs('pr.*')">
                        {{ __('Daftar PR') }}
                    </x-prsystem::nav-link>

                    {{-- Purchasing, Finance, Admin see PO List --}}
                    @if($canSeePo)
                    <x-prsystem::nav-link :href="route('po.index')" :active="request()->routeIs('po.*')">
                        {{ __('Daftar PO') }}
                    </x-prsystem::nav-link>
                    @endif

                    {{-- Approver, Admin see Approval Inbox --}}
                    @if($canSeeApproval)
                    <x-prsystem::nav-link :href="route('approval.index')" :active="request()->routeIs('approval.*')">
                        {{ __('Inbox Approval') }}
                    </x-prsystem::nav-link>
                    @endif

                    {{-- Purchasing, Admin see Inventory --}}
                    @if($canSeeInventory)
                    <x-prsystem::nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*')">
                        {{ __('Inventory') }}
                    </x-prsystem::nav-link>
                    @endif

                    <div class="hidden sm:flex sm:items-center sm:ms-2">
                        <x-prsystem::dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out">
                                    <div>Capex</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-prsystem::dropdown-link :href="route('capex.index')">Daftar Capex</x-prsystem::dropdown-link>
                                @if($isPrAdmin)
                                    <div class="border-t border-gray-100"></div>
                                    <div class="px-4 py-2 text-xs text-gray-400 uppercase">Admin</div>
                                    <x-prsystem::dropdown-link :href="route('admin.capex.assets.index')">Assets (CI)</x-prsystem::dropdown-link>
                                    <x-prsystem::dropdown-link :href="route('admin.capex.budgets.index')">Budgets</x-prsystem::dropdown-link>
                                    <x-prsystem::dropdown-link :href="route('admin.capex.config.index')">Sign Config</x-prsystem::dropdown-link>
                                @endif
                            </x-slot>
                        </x-prsystem::dropdown>
                    </div>

                    @if($isPrAdmin)
                    <div class="hidden sm:flex sm:items-center sm:ms-2">
                        <x-prsystem::dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>Master Data</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="border-t border-gray-100"></div>
                                <x-prsystem::dropdown-link :href="route('master-departments.index')" :active="request()->routeIs('master-departments.*')">
                                    {{ __('Departments (Units)') }}
                                </x-prsystem::dropdown-link>
                                <x-prsystem::dropdown-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">
                                    {{ __('Approver Config') }}
                                </x-prsystem::dropdown-link>
                                <x-prsystem::dropdown-link :href="route('sub-departments.index')" :active="request()->routeIs('sub-departments.*')">
                                    {{ __('Sub Departments') }}
                                </x-prsystem::dropdown-link>
                                <x-prsystem::dropdown-link :href="route('jobs.index')" :active="request()->routeIs('jobs.*')">
                                    {{ __('Jobs (Pekerjaan)') }}
                                </x-prsystem::dropdown-link>
                                <x-prsystem::dropdown-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                                    {{ __('Users') }}
                                </x-prsystem::dropdown-link>
                                <x-prsystem::dropdown-link :href="route('global-approvers.index')" :active="request()->routeIs('global-approvers.*')">
                                    {{ __('Global Approvers') }}
                                </x-prsystem::dropdown-link>
                            </x-slot>
                        </x-prsystem::dropdown>
                    </div>

                    <x-prsystem::nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                        {{ __('Products') }}
                    </x-prsystem::nav-link>

                    <x-prsystem::nav-link :href="route('admin.budgets.index')" :active="request()->routeIs('admin.budgets.index')">
                        {{ __('Budget') }}
                    </x-prsystem::nav-link>
                    @endif

                    @if($canSeeBudgetMonitoring)
                    <x-prsystem::nav-link :href="route('admin.budgets.monitoring')" :active="request()->routeIs('admin.budgets.monitoring')">
                        {{ __('Monitoring Budget') }}
                    </x-prsystem::nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-prsystem::dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-prsystem::dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-prsystem::dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-prsystem::dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-prsystem::dropdown-link>
                        </form>
                    </x-slot>
                </x-prsystem::dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-prsystem::responsive-nav-link :href="route('pr.dashboard')" :active="request()->routeIs('pr.dashboard')">
                {{ __('Dashboard') }}
            </x-prsystem::responsive-nav-link>

            {{-- Everyone --}}
            <x-prsystem::responsive-nav-link :href="route('pr.index')" :active="request()->routeIs('pr.*')">
                {{ __('Daftar PR') }}
            </x-prsystem::responsive-nav-link>

            {{-- Purchasing, Finance, Admin --}}
            @if($canSeePo)
            <x-prsystem::responsive-nav-link :href="route('po.index')" :active="request()->routeIs('po.*')">
                {{ __('Daftar PO') }}
            </x-prsystem::responsive-nav-link>
            @endif

            {{-- Approver, Admin --}}
            @if($canSeeApproval)
            <x-prsystem::responsive-nav-link :href="route('approval.index')" :active="request()->routeIs('approval.*')">
                {{ __('Inbox Approval') }}
            </x-prsystem::responsive-nav-link>
            @endif

            {{-- Purchasing, Admin --}}
            @if($canSeeInventory)
            <x-prsystem::responsive-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*')">
                {{ __('Inventory') }}
            </x-prsystem::responsive-nav-link>
            @endif

            {{-- Admin Only --}}
            @if($isPrAdmin)
            <div class="pt-2 border-t border-gray-200 mt-2 px-4 text-xs font-semibold text-gray-500 uppercase">
                Master Data
            </div>


            
            <x-prsystem::responsive-nav-link :href="route('master-departments.index')" :active="request()->routeIs('master-departments.*')">
                {{ __('Departments') }}
            </x-prsystem::responsive-nav-link>
            
            <div class="pt-2 border-t border-gray-200 mt-2 px-4 text-xs font-semibold text-gray-500 uppercase">
                Capex
            </div>
            <x-prsystem::responsive-nav-link :href="route('capex.index')">Daftar Capex</x-prsystem::responsive-nav-link>
            @if($isPrAdmin)
                <x-prsystem::responsive-nav-link :href="route('admin.capex.assets.index')">Capex Assets</x-prsystem::responsive-nav-link>
                <x-prsystem::responsive-nav-link :href="route('admin.capex.budgets.index')">Capex Budgets</x-prsystem::responsive-nav-link>
                <x-prsystem::responsive-nav-link :href="route('admin.capex.config.index')">Sign Config</x-prsystem::responsive-nav-link>
            @endif

            <x-prsystem::responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                {{ __('Users') }}
            </x-prsystem::responsive-nav-link>

            <x-prsystem::responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                {{ __('Products') }}
            </x-prsystem::responsive-nav-link>

            <x-prsystem::responsive-nav-link :href="route('admin.budgets.index')" :active="request()->routeIs('admin.budgets.index')">
                {{ __('Budget') }}
            </x-prsystem::responsive-nav-link>
            @endif

            @if($canSeeBudgetMonitoring)
            <x-prsystem::responsive-nav-link :href="route('admin.budgets.monitoring')" :active="request()->routeIs('admin.budgets.monitoring')">
                {{ __('Monitoring Budget') }}
            </x-prsystem::responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-prsystem::responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-prsystem::responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-prsystem::responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-prsystem::responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
