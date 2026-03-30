@extends('layouts.app')

@section('content')
<div class="space-y-6 w-full">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                ISPO Documents
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and audit all your Indonesian Sustainable Palm Oil records securely.</p>
        </div>
        <div class="flex items-center gap-3">
             <button class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter List
                </span>
            </button>
        </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Document Table Column -->
        <div class="{{ Auth::user()->moduleRole('ispo') === 'ISPO Admin' ? 'lg:col-span-2' : 'lg:col-span-3' }}">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col h-full">
                <!-- Data Grid Header -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-800/50">
                    <h3 class="font-bold text-gray-900 dark:text-white text-lg flex items-center gap-2">
                         <span class="w-2 h-2 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]"></span>
                        Document Repository
                    </h3>
                    <div class="relative w-full sm:w-auto">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" placeholder="Search PO..." class="pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 outline-none transition-all w-full sm:w-64">
                    </div>
                </div>
                
                <!-- Custom Data Grid -->
                <div class="flex-1 overflow-x-auto">
                    @if($documents->isEmpty())
                        <div class="p-12 flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center text-gray-400 mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No documents available</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">You haven't generated any ISPO audit records yet. Use the form to start a new audit cycle.</p>
                        </div>
                    @else
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 dark:bg-gray-800/80 text-gray-500 dark:text-gray-400 font-semibold border-b border-gray-100 dark:border-gray-700 uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="px-6 py-4">Ref Number</th>
                                    <th class="px-6 py-4">Site Origin</th>
                                    <th class="px-6 py-4 text-center">Audit Year</th>
                                    <th class="px-6 py-4">Validation Status</th>
                                    <th class="px-6 py-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                @foreach($documents as $doc)
                                <tr class="hover:bg-indigo-50/40 dark:hover:bg-indigo-900/20 transition-colors group">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white group-hover:text-indigo-600 transition-colors">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            {{ $doc->document_number ?? 'DRAFT_UNASSIGNED' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-gray-900 dark:text-gray-200 font-medium">{{ $doc->site->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $doc->site->code ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium font-mono text-xs">
                                            {{ $doc->year }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if(strtolower($doc->status) === 'draft')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span> Draft
                                            </span>
                                        @elseif(strtolower($doc->status) === 'submitted')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5 align-middle animate-pulse"></span> Submitted
                                            </span>
                                        @elseif(strtolower($doc->status) === 'approved' || strtolower($doc->status) === 'verified')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> {{ ucfirst($doc->status) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span> {{ ucfirst($doc->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('ispo.show', $doc->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 hover:bg-indigo-50 dark:hover:bg-indigo-900 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-200 dark:hover:border-indigo-800 transition-all shadow-sm">
                                            Edit Data
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                @if($documents->isNotEmpty())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/20 flex items-center justify-between text-sm text-gray-500">
                    <span>Showing list of generated documents</span>
                </div>
                @endif
            </div>
        </div>

        @if(Auth::user()->moduleRole('ispo') === 'ISPO Admin')
        <!-- Creation Form Card -->
        <div class="lg:col-span-1">
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg p-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl h-full flex flex-col p-6 relative overflow-hidden">
                    
                    <!-- Decorative background element -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-bl-full -mr-8 -mt-8 pointer-events-none"></div>

                    <div class="relative z-10 mb-6">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 mb-4 shadow-sm border border-indigo-100 dark:border-indigo-800/50">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Initialize Audit</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Select a site and cycle year to generate a new master ISPO compliance checklist.</p>
                    </div>

                    <form action="{{ route('ispo.store') }}" method="POST" class="relative z-10 flex flex-col flex-1 space-y-5">
                        @csrf
                        <div>
                            <label for="site_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Target Site</label>
                            <div class="relative">
                                <select name="site_id" id="site_id" class="w-full pl-3 pr-10 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 text-sm text-gray-900 dark:text-white transition-all appearance-none outline-none">
                                    <option value="" disabled selected>-- Select Operational Site --</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }} ({{ $site->code }})</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            @error('site_id') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="year" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Audit Year</label>
                            <input type="number" name="year" id="year" value="{{ date('Y') }}" min="2020" max="2030" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 text-sm text-gray-900 dark:text-white transition-all outline-none font-medium">
                            @error('year') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-4 mt-auto">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-indigo-500/30 transition-transform transform active:scale-95 focus:outline-none">
                                <span>Generate Master Document</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
