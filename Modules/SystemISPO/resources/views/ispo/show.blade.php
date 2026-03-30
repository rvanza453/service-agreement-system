@extends('layouts.app')

@section('content')
<form action="{{ route('ispo.bulkUpdate', $document->id) }}" method="POST" enctype="multipart/form-data" id="bulkForm" class="w-full flex-1 flex flex-col">
    @csrf
    
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center z-20 pb-4 border-b border-gray-200/50 dark:border-gray-800/50 transition-all duration-300">
        <div class="flex items-center gap-4 mb-4 sm:mb-0">
            <a href="{{ route('ispo.index') }}" class="p-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-200 focus:outline-none transition-all shadow-sm group" title="Back to Index">
                 <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight flex items-center gap-3">
                    Audit Checklist Workspace
                    <a href="{{ route('ispo.admin.items.index') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100/80 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-semibold transition-colors border border-gray-200 dark:border-gray-650" title="Manage Master Hierarchy Data (Principles, Criteria, etc)">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Edit Master Data
                    </a>
                </h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-semibold border border-indigo-100 dark:border-indigo-800">Site: {{ $document->site->name }}</span>
                    <span class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-semibold border border-gray-200 dark:border-gray-700">Audit Year: {{ $document->year }}</span>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="px-4 py-2.5 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ Auth::user()->moduleRole('ispo') === 'ISPO Admin' ? 'bg-indigo-500' : 'bg-amber-500' }}"></span>
                Mode: {{ Auth::user()->moduleRole('ispo') === 'ISPO Admin' ? 'Data Entry (Admin)' : 'Review (Auditor)' }}
            </div>
            
            <button type="submit" class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-indigo-500/20 transition-transform transform active:scale-95 focus:outline-none">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                <span>Save Progress</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 flex items-center justify-between shadow-sm mb-6 max-w-2xl">
            <div class="flex items-center gap-3">
                <span class="bg-emerald-100 text-emerald-600 rounded-full p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </span>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            <button @click="show = false" type="button" class="text-emerald-500 hover:text-emerald-700 focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 flex flex-col mt-2" style="height: calc(100vh - 190px);">
        <div class="flex-1 overflow-auto custom-scrollbar rounded-2xl">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 relative" style="min-width: 1500px">
                <thead class="bg-gray-50/95 dark:bg-gray-800/95 backdrop-blur-xl border-b border-gray-200 dark:border-gray-700 sticky top-0 z-30 shadow-sm">
                <tr>
                    <th scope="col" class="px-4 py-4 text-left text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 sticky left-0 bg-gray-50 dark:bg-gray-800 z-40 w-12 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">No</th>
                    <th scope="col" class="px-4 py-4 text-left text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 w-32 group hover:bg-gray-100 dark:hover:bg-gray-700 transition"><div class="flex items-center justify-between">Prinsip <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg></div></th>
                    <th scope="col" class="px-4 py-4 text-left text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 w-32 group hover:bg-gray-100 dark:hover:bg-gray-700 transition"><div class="flex items-center justify-between">Kriteria <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg></div></th>
                    <th scope="col" class="px-4 py-4 text-left text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 w-40">Indikator</th>
                    <th scope="col" class="px-4 py-4 text-left text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 w-48">Parameter</th>
                    <th scope="col" class="px-4 py-4 text-left text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 w-64">Verifikasi</th>
                    
                    <!-- HR Inputs -->
                    <th scope="col" class="bg-indigo-50/50 dark:bg-indigo-900/10 px-4 py-4 text-left text-[11px] font-bold text-indigo-800 dark:text-indigo-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 w-36"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-indigo-500"></span> Kelengkapan PT</span></th>
                    <th scope="col" class="bg-indigo-50/50 dark:bg-indigo-900/10 px-4 py-4 text-left text-[11px] font-bold text-indigo-800 dark:text-indigo-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 min-w-[300px]">Catatan PT</th>
                    <th scope="col" class="bg-indigo-50/50 dark:bg-indigo-900/10 px-4 py-4 text-left text-[11px] font-bold text-indigo-800 dark:text-indigo-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 w-48">Dokumen <span class="text-gray-400 font-normal normal-case ml-1">(PDF/JPG)</span></th>

                    <!-- Auditor Inputs -->
                    <th scope="col" class="bg-amber-50/50 dark:bg-amber-900/10 px-4 py-4 text-left text-[11px] font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 w-36"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Penilaian Auditor</span></th>
                    <th scope="col" class="bg-amber-50/50 dark:bg-amber-900/10 px-4 py-4 text-left text-[11px] font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 min-w-[300px]">Catatan Auditor</th>
                    
                    <th scope="col" class="px-4 py-4 text-center text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">Log</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700/50 text-xs">
                @php
                    $prevPrinId = null;
                    $prevCritId = null;
                    $prevIndId = null;
                @endphp
                @foreach($rows as $index => $row)
                    @php
                        $verifier = $row['verifier'];
                        $entry = $entries[$verifier->id] ?? null;
                        
                        // HR Data
                        $status = $entry ? $entry->status : '';
                        $notes = $entry ? $entry->notes : '';
                        
                        // Audit Data
                        $auditStatus = $entry ? $entry->audit_status : '';
                        $auditNotes = $entry ? $entry->audit_notes : '';

                        $rowBg = $index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50/50 dark:bg-gray-800/50';
                    @endphp
                    <tr class="{{ $rowBg }} hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group" id="row-{{ $verifier->id }}">
                        <td class="px-4 py-3 whitespace-nowrap border-r border-gray-100 dark:border-gray-700 text-center sticky left-0 bg-inherit z-20 text-gray-400 font-mono text-[10px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">{{ str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}</td>
                        
                        <!-- Hierarchy Columns -->
                        <td class="px-4 py-3 border-r border-gray-100 dark:border-gray-700 align-top {{ $prevPrinId !== $row['principle']->id ? 'border-t-4 border-t-gray-200 dark:border-t-gray-700' : '' }}">
                            @if($prevPrinId !== $row['principle']->id)
                                <div class="font-bold text-gray-900 dark:text-gray-100 mb-1">{{ $row['principle']->code }}</div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-3 leading-relaxed" title="{{ $row['principle']->name }}">{{ $row['principle']->name }}</div>
                                @php $prevPrinId = $row['principle']->id; @endphp
                            @endif
                        </td>
                        <td class="px-4 py-3 border-r border-gray-100 dark:border-gray-700 align-top">
                            @if($prevCritId !== $row['criteria']->id)
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-4 leading-relaxed" title="{{ $row['criteria']->name }}"><span class="font-bold text-gray-700 dark:text-gray-300">{{ $row['criteria']->code }}</span> &mdash; {{ $row['criteria']->name }}</div>
                                @php $prevCritId = $row['criteria']->id; @endphp
                            @endif
                        </td>
                        <td class="px-4 py-3 border-r border-gray-100 dark:border-gray-700 align-top">
                             @if($prevIndId !== $row['indicator']->id)
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-4 leading-relaxed" title="{{ $row['indicator']->name }}"><span class="font-bold text-gray-700 dark:text-gray-300">{{ $row['indicator']->code }}</span> &mdash; {{ $row['indicator']->name }}</div>
                                @php $prevIndId = $row['indicator']->id; @endphp
                             @endif
                        </td>
                        <td class="px-4 py-3 border-r border-gray-100 dark:border-gray-700 align-top">
                            @if($row['parameter'])
                                 <div class="text-[11px] text-gray-600 dark:text-gray-400 leading-relaxed">{{ $row['parameter']->name }}</div>
                            @else
                                 <span class="text-gray-300 dark:text-gray-600 italic">No specific parameter</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 border-r border-gray-100 dark:border-gray-700 align-top">
                             <div class="text-gray-800 dark:text-gray-200 font-medium leading-relaxed">{{ $verifier->name }}</div>
                        </td>

                        <!-- HR Inputs (Indigo Tint) -->
                        <td class="px-3 py-3 border-r border-indigo-100/50 dark:border-gray-700 align-top bg-indigo-50/20 dark:bg-indigo-900/5">
                            <div class="relative">
                                <select name="items[{{ $verifier->id }}][status]" class="hr-input w-full appearance-none rounded-lg border-gray-200 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-xs py-2 pl-3 pr-8 dark:bg-gray-700 dark:text-white transition-all bg-white dark:bg-gray-800 disabled:opacity-50 disabled:bg-gray-50 dark:disabled:bg-gray-800/50">
                                    <option value="" class="text-gray-400">- Select Status -</option>
                                    <option value="Tersedia" {{ $status == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                                    <option value="Tidak Tersedia" {{ $status == 'Tidak Tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                                    <option value="Not Applicable" {{ $status == 'Not Applicable' ? 'selected' : '' }}>Not Applicable</option>
                                </select>
                                <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400 hr-input-icon transition-opacity">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3 border-r border-indigo-100/50 dark:border-gray-700 align-top bg-indigo-50/20 dark:bg-indigo-900/5">
                            <textarea name="items[{{ $verifier->id }}][notes]" rows="2" class="hr-input w-full rounded-lg border-gray-200 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-xs py-2 px-3 dark:bg-gray-700 dark:text-white transition-all bg-white dark:bg-gray-800 resize-none disabled:opacity-50 disabled:bg-gray-50 dark:disabled:bg-gray-800/50" placeholder="Add detailed notes here...">{{ $notes }}</textarea>
                        </td>
                        <td class="px-3 py-3 align-top border-r border-indigo-100/50 dark:border-gray-700 bg-indigo-50/20 dark:bg-indigo-900/5">
                            <div class="flex flex-col space-y-3">
                                <div class="relative group/file">
                                    <input type="file" id="file-{{ $verifier->id }}" multiple class="hr-input absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10 file:cursor-pointer">
                                    <div class="flex items-center justify-center gap-2 w-full px-3 py-2 border border-dashed border-indigo-300 dark:border-indigo-700 rounded-lg bg-indigo-50/50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 text-xs font-medium group-hover/file:bg-indigo-100 dark:group-hover/file:bg-indigo-900/40 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        Upload Files
                                    </div>
                                </div>
                                 
                                 <!-- Pending Queue -->
                                 <div id="queue-list-{{ $verifier->id }}" class="space-y-1.5"></div>
                                 
                                 @if($entry && $entry->attachments->count() > 0)
                                    <div class="space-y-1.5" id="attachments-list-{{ $verifier->id }}">
                                        @foreach($entry->attachments as $att)
                                            <div class="flex justify-between items-center bg-white dark:bg-gray-800 px-2.5 py-1.5 rounded-md border border-gray-100 dark:border-gray-700 shadow-sm group/att" id="att-{{ $att->id }}">
                                                <div class="flex items-center gap-1.5 overflow-hidden">
                                                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="text-[10px] text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline truncate" title="{{ $att->file_name }}">
                                                        {{ $att->file_name }}
                                                    </a>
                                                </div>
                                                <button type="button" onclick="deleteAttachment({{ $att->id }})" class="hr-input text-gray-400 hover:text-red-500 font-bold ml-2 opacity-0 group-hover/att:opacity-100 transition-opacity focus:opacity-100" title="Remove File">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </td>

                        <!-- Auditor Inputs (Amber Tint) -->
                        <td class="px-3 py-3 border-r border-amber-100/50 dark:border-gray-700 align-top bg-amber-50/20 dark:bg-amber-900/5">
                            <div class="relative">
                                <select name="items[{{ $verifier->id }}][audit_status]" class="auditor-input w-full appearance-none rounded-lg border-gray-200 dark:border-gray-600 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-xs py-2 pl-3 pr-8 dark:bg-gray-700 dark:text-white transition-all bg-white dark:bg-gray-800 disabled:opacity-50 disabled:bg-gray-50 dark:disabled:bg-gray-800/50" disabled>
                                    <option value="" class="text-gray-400">- Select Assessment -</option>
                                    <option value="Sesuai" {{ $auditStatus == 'Sesuai' ? 'selected' : '' }}>Sesuai (Done)</option>
                                    <option value="Tidak Sesuai" {{ $auditStatus == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai (Non-Conformity)</option>
                                    <option value="OFI" {{ $auditStatus == 'OFI' ? 'selected' : '' }}>OFI (Opportunity For Improvement)</option>
                                </select>
                                <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3 border-r border-amber-100/50 dark:border-gray-700 align-top bg-amber-50/20 dark:bg-amber-900/5">
                            <textarea name="items[{{ $verifier->id }}][audit_notes]" rows="2" class="auditor-input w-full rounded-lg border-gray-200 dark:border-gray-600 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-xs py-2 px-3 dark:bg-gray-700 dark:text-white transition-all bg-white dark:bg-gray-800 resize-none disabled:opacity-50 disabled:bg-gray-50 dark:disabled:bg-gray-800/50" placeholder="Auditor evaluation..." disabled>{{ $auditNotes }}</textarea>
                        </td>
                        
                        <!-- Controls -->
                        <td class="px-4 py-3 align-top text-center bg-gray-50/30 dark:bg-gray-900/30">
                            <!-- History Button -->
                             <button type="button" onclick="openHistory({{ $entry ? $entry->id : 0 }})" class="p-2 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none ring-2 ring-transparent focus:ring-indigo-500/30" title="View Audit History log">
                                <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</form>

<!-- History Modal -->
<div id="historyModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 dark:bg-black bg-opacity-75 dark:bg-opacity-60 transition-opacity" aria-hidden="true" onclick="closeHistoryModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-indigo-600 px-4 py-3 sm:px-6">
                 <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Riwayat Perubahan & Revisi</h3>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[70vh] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 dark:scrollbar-thumb-gray-600 scrollbar-track-transparent">
                <div id="historyContent" class="space-y-4">
                    <!-- Loaded via JS -->
                    <div class="text-center text-gray-500">Loading...</div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t dark:border-gray-700">
                <button type="button" onclick="closeHistoryModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentRole = '{{ Auth::user()->moduleRole('ispo') === 'ISPO Admin' ? 'admin' : 'auditor' }}';
    let fileQueue = new Map(); // Map<verifierId, Array<File>>

    function updateRole() {
        const isAdmin = currentRole === 'admin';
        const isAuditor = currentRole === 'auditor';

        // Toggle HR (admin) inputs
        document.querySelectorAll('.hr-input').forEach(el => {
            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') {
                el.disabled = !isAdmin;
                if (el.type === 'file') {
                    const wrapper = el.closest('.group\\/file');
                    if (wrapper) wrapper.style.display = isAdmin ? 'block' : 'none';
                }
            }
            if (el.tagName === 'BUTTON') {
                el.style.display = isAdmin ? 'inline-block' : 'none';
            }
        });

        // Toggle Auditor inputs
        document.querySelectorAll('.auditor-input').forEach(el => {
            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') {
                el.disabled = !isAuditor;
            }
        });
    }

    // Initialize on page load
    updateRole();

    // Handle File Selection into Queue
    document.addEventListener('change', function(e) {
        if (e.target.type === 'file' && e.target.classList.contains('hr-input')) {
            const fileInput = e.target;
            const id = fileInput.id.replace('file-', '');
            if (fileInput.files.length > 0) {
                if (!fileQueue.has(id)) fileQueue.set(id, []);
                const currentFiles = fileQueue.get(id);
                for (let i = 0; i < fileInput.files.length; i++) {
                    currentFiles.push(fileInput.files[i]);
                }
                renderFileQueue(id);
                fileInput.value = '';
            }
        }
    });

    function renderFileQueue(id) {
        const queueContainer = document.getElementById('queue-list-' + id);
        if (!queueContainer) return;
        queueContainer.innerHTML = '';
        if (fileQueue.has(id)) {
            const files = fileQueue.get(id);
            files.forEach((file, index) => {
                const div = document.createElement('div');
                div.className = "flex justify-between items-center bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded border border-indigo-100 dark:border-indigo-800 mt-1";
                div.innerHTML = `
                    <span class="text-[10px] text-indigo-700 dark:text-indigo-300 truncate w-24" title="${file.name}">${file.name}</span>
                    <button type="button" onclick="removeQueuedFile('${id}', ${index})" class="text-red-500 hover:text-red-700 font-bold ml-1" title="Remove">&times;</button>
                `;
                queueContainer.appendChild(div);
            });
        }
    }

    function removeQueuedFile(id, index) {
        if (fileQueue.has(id)) {
            fileQueue.get(id).splice(index, 1);
            renderFileQueue(id);
        }
    }

    // Handle Form Submission (AJAX)
    document.getElementById('bulkForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span>Saving...</span>';

        const formData = new FormData(this);

        // Append queued files
        fileQueue.forEach((files, id) => {
            files.forEach(file => {
                formData.append(`items[${id}][files][]`, file);
            });
        });

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                return response.json();
            }
        })
        .then(data => {
            if (data && data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error saving:', error);
            alert('Terjadi kesalahan saat menyimpan data.');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });

    // Delete attachment
    function deleteAttachment(id) {
        if (!confirm('Hapus file ini?')) return;
        fetch(`/ispo/attachment/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('att-' + id)?.remove();
            } else {
                alert('Gagal menghapus file.');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // History Modal
    function openHistory(entryId) {
        if (!entryId) {
            alert('Belum ada riwayat untuk item ini.');
            return;
        }
        document.getElementById('historyModal').classList.remove('hidden');
        const container = document.getElementById('historyContent');
        container.innerHTML = '<div class="text-center py-4">Loading history...</div>';

        fetch(`/ispo/history/${entryId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.history.length > 0) {
                    let html = '';
                    data.history.forEach(item => {
                        const date = new Date(item.created_at).toLocaleString('id-ID');
                        const isAuditor = item.role === 'auditor';
                        const badgeColor = isAuditor ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-300';
                        const borderColor = isAuditor ? 'border-amber-400 dark:border-amber-500/50' : 'border-indigo-400 dark:border-indigo-500/50';
                        const dotColor = isAuditor ? 'bg-amber-400 dark:bg-amber-500' : 'bg-indigo-400 dark:bg-indigo-500';
                        const roleLabel = isAuditor ? 'AUDITOR UPDATE' : 'ADMIN UPDATE';

                        let filesHtml = '<div class="text-[10px] text-gray-400 italic">No files attached</div>';
                        let attachments = [];
                        try {
                            if (typeof item.attachments_snapshot === 'string') {
                                attachments = JSON.parse(item.attachments_snapshot);
                            } else if (Array.isArray(item.attachments_snapshot)) {
                                attachments = item.attachments_snapshot;
                            }
                        } catch(e) { /* ignore parse error */ }

                        if (attachments && attachments.length > 0) {
                            filesHtml = '<div class="mt-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Files attached:</div><ul class="list-disc pl-4 text-xs">';
                            attachments.forEach(f => {
                                filesHtml += `<li><a href="/storage/${f.file_path}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">${f.file_name}</a></li>`;
                            });
                            filesHtml += '</ul>';
                        }

                        html += `
                            <div class="relative pl-6 border-l-2 ${borderColor} pb-4">
                                <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full ${dotColor} border-2 border-white dark:border-gray-800"></div>
                                <div class="bg-gray-50 dark:bg-gray-800/80 rounded-lg p-3 text-sm shadow-sm border border-gray-100 dark:border-gray-700">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="px-2 py-0.5 rounded font-bold ${badgeColor} uppercase tracking-wide text-[10px]">${roleLabel}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">${date}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 mt-2">
                                        <div>
                                            <div class="font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase mb-1">Input PT</div>
                                            <div class="text-gray-800 dark:text-gray-300 text-xs"><span class="font-bold dark:text-gray-200">Status:</span> <span class="dark:text-gray-300">${item.status || '-'}</span></div>
                                            <div class="text-gray-800 dark:text-gray-300 text-xs mt-0.5"><span class="font-bold dark:text-gray-200">Catatan:</span> <span class="dark:text-gray-300">${item.notes || '-'}</span></div>
                                            <div class="mt-2 text-gray-600 dark:text-gray-400">
                                                ${filesHtml}
                                            </div>
                                        </div>
                                        <div class="border-l border-gray-200 dark:border-gray-700 pl-3">
                                            <div class="font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase mb-1">Penilaian Auditor</div>
                                            <div class="text-gray-800 dark:text-gray-300 text-xs"><span class="font-bold dark:text-gray-200">Status:</span> <span class="dark:text-gray-300">${item.audit_status || '-'}</span></div>
                                            <div class="text-gray-800 dark:text-gray-300 text-xs mt-0.5"><span class="font-bold dark:text-gray-200">Catatan:</span> <span class="dark:text-gray-300">${item.audit_notes || '-'}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<div class="text-center py-4 text-gray-500">Belum ada riwayat untuk item ini.</div>';
                }
            })
            .catch(err => {
                console.error('Error fetching history:', err);
                container.innerHTML = '<div class="text-center py-4 text-red-500">Gagal memuat riwayat.</div>';
            });
    }

    function closeHistoryModal() {
        document.getElementById('historyModal').classList.add('hidden');
    }
</script>
@endsection
