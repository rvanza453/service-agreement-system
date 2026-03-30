<x-prsystem::app-layout>
    @php
        $hasWetFile = (bool) $capex->signed_file_path;
    @endphp
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Detail Capex Request</h2>
                <div class="text-sm text-gray-500">Nomor: {{ $capex->capex_number }}</div>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $statusColor = match($capex->status) {
                        'Pending' => 'bg-yellow-100 text-yellow-800',
                        'On Hold' => 'bg-orange-100 text-orange-800',
                        'Approved' => 'bg-green-100 text-green-800',
                        'Rejected' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                @endphp
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusColor }}">
                    {{ $capex->status }}
                </span>
                
                @if($capex->current_step >= 6 || $capex->status == 'Approved')
                    <a href="{{ route('capex.print', $capex) }}" target="_blank" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 text-sm font-medium transition inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print PDF
                    </a>

                    @if($hasWetFile && auth()->user()->hasRole('Admin') && !$capex->is_verified)
                        <form action="{{ route('capex.verify', $capex) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium shadow animate-pulse transition" onclick="return confirm('Verify dan auto-create PR?')">
                                Verify & Create PR
                            </button>
                        </form>
                    @elseif($capex->is_verified)
                        <span class="bg-green-100 text-green-800 px-3 py-1 text-xs rounded-full border border-green-200 font-bold">✓ Verified</span>
                        @if($capex->pr_id)
                            <span class="bg-gray-100 text-gray-800 px-3 py-1 text-xs rounded-full border border-gray-200">PR Created</span>
                        @endif
                    @elseif(!$hasWetFile && auth()->user()->hasRole('Admin'))
                        <span class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium text-center">
                            Menunggu Upload TTD Basah
                        </span>
                    @endif
                @endif
            </div>
        </div>

        <!-- Details Card -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-sm space-y-4">
                <h4 class="font-bold border-b pb-2">Request Info</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="text-gray-500">Requester</div>
                    <div class="font-medium">{{ $capex->user->name }}</div>
                    
                    <div class="text-gray-500">Department</div>
                    <div class="font-medium">{{ $capex->department->name }}</div>
                    
                    <div class="text-gray-500">Budget Item</div>
                    <div class="font-medium">{{ $capex->capexBudget->budget_code }} - {{ $capex->capexBudget->capexAsset->name }}</div>

                    <div class="text-gray-500">Status Anggaran</div>
                    <div>
                        @if($capex->code_budget_ditanam)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                ✓ Dianggarkan (Budgeted){{ $capex->capexBudget->pta_amount > 0 ? ' + PTA' : '' }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                ⚠ Tidak Dianggarkan (Unbudgeted){{ $capex->capexBudget->pta_amount > 0 ? ' + PTA' : '' }}
                            </span>
                        @endif
                    </div>

                    <div class="text-gray-500">Amount</div>
                    <div class="font-bold text-lg">Rp {{ number_format($capex->amount, 0) }}</div>

                    <div class="text-gray-500">Asset Code</div>
                    <div class="font-mono font-bold">{{ $capex->capexBudget->capexAsset->code }}</div>

                    <div class="text-gray-500">Dokumen Pendukung</div>
                    <div>
                        @if($capex->supporting_document_path)
                            <div class="flex items-center gap-2">
                                <a href="{{ Storage::url($capex->supporting_document_path) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-md transition">
                                    Lihat PDF
                                </a>
                                <a href="{{ Storage::url($capex->supporting_document_path) }}" download class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md transition">
                                    Download PDF
                                </a>
                            </div>
                        @else
                            <span class="text-xs text-gray-400">Tidak ada lampiran</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-gray-500 text-sm mb-1">Description</div>
                    <p class="text-gray-800 bg-gray-50 p-3 rounded">{{ $capex->description }}</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h4 class="font-bold border-b pb-2 mb-4">Justifikasi (Kuesioner)</h4>
                <div class="space-y-4 h-64 overflow-y-auto pr-2">
                    @php
                        $questions = [
                            1 => 'Apa yang biasa dipakai selama ini?',
                            2 => 'Mengapa pengeluaran diperlukan?',
                            3 => 'Dapatkah pengeluaran ditunda pada tahun depan? jika tidak, mengapa?',
                            4 => 'Apa konsekuensi jika pengeluaran di tolak?',
                            5 => 'Mungkinkah ada dampak buruk pada operasi yang ada?',
                            6 => 'Berapa lama proyek berlangsung? Kapan proyek tersebut selesai?'
                        ];
                    @endphp
                    @foreach($questions as $idx => $q)
                        <div>
                            <p class="text-xs font-bold text-indigo-600">{{ $idx }}. {{ $q }}</p>
                            <p class="text-sm text-gray-800 mt-1 pl-4 border-l-2 border-indigo-100">{{ $capex->questionnaire_answers[$idx] ?? '-' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Informasi Anggaran Card -->
        @php
            $budgetAwal = ($capex->capexBudget->amount ?? 0) + ($capex->capexBudget->pta_amount ?? 0);
            $usulan     = $capex->amount;
            
            // Capex yang disetujui sebelumnya: Kalkulasi total pengajuan capex dari budget ini sebelum capex saat ini dibuat
            $capexSebelumnya = 0;
            if ($capex->code_budget_ditanam && $capex->capexBudget) {
                $capexSebelumnya = \Modules\PrSystem\Models\CapexRequest::where('capex_budget_id', $capex->capex_budget_id)
                                ->where('id', '<', $capex->id)
                                ->where('status', '!=', 'Rejected')
                                ->sum('amount');
            }
            
            // Saldo Anggaran yang dapat dipakai (sehingga capex ini bisa diajukan)
            $saldoDapatDipakai = $capex->code_budget_ditanam ? ($budgetAwal - $capexSebelumnya) : 0;
            
            // Over / Under setelah usulan ini
            $overUnder = $capex->code_budget_ditanam ? ($saldoDapatDipakai - $usulan) : (0 - $usulan);
            $sisaAkhir = $overUnder;
        @endphp
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h4 class="font-bold border-b pb-2 mb-4 text-gray-800">Informasi Anggaran</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <div class="text-gray-500 mb-1">Anggaran yang Disetujui</div>
                    <div class="font-bold text-gray-800">Rp {{ number_format($budgetAwal, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-gray-500 mb-1">Capex yang Disetujui Sebelumnya</div>
                    <div class="font-bold text-red-600">Rp {{ number_format($capexSebelumnya, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-gray-500 mb-1">Saldo Anggaran yang Dapat Dipakai</div>
                    <div class="font-bold text-blue-600 border-b-2 border-dashed border-gray-200 pb-2">Rp {{ number_format($saldoDapatDipakai, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-gray-500 mb-1">Nilai Usulan Pembelian</div>
                    <div class="font-bold text-orange-600">Rp {{ number_format($usulan, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-gray-500 mb-1">Over / Under</div>
                    <div class="font-bold text-gray-800">Rp {{ number_format($overUnder, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-gray-500 mb-1">Sisa Saldo Anggaran</div>
                    <div class="font-bold text-green-600 text-lg">Rp {{ number_format($sisaAkhir, 0, ',', '.') }}</div>
                </div>
            </div>
            @if(!$capex->code_budget_ditanam)
            <div class="mt-4 px-3 py-2 bg-yellow-50 text-yellow-800 text-xs rounded border border-yellow-200">
                Capex ini ditandai sebagai <b>Tidak Dianggarkan (Unbudgeted)</b>. Kalkulasi saldo budget tidak mengurangi nilai sisa budget aktual.
            </div>
            @endif
        </div>

        @if($canApprove && $capex->status != 'Rejected')
            <!-- Approval Actions (Bottom Card) -->
            <div class="bg-white rounded-xl shadow-sm p-6 space-y-4 mt-6">
                <h3 class="text-lg font-bold text-gray-800">Tindakan Approval</h3>
                <form method="POST" id="approval-form">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan / Alasan (Opsional untuk Approve, Wajib untuk Reject/Hold)</label>
                        <textarea name="remarks" id="remarks-input" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-colors" placeholder="Tulis catatan disini..."></textarea>
                        <p id="remarks-error" class="hidden text-sm text-red-600 mt-1 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Mohon isi catatan/alasan terlebih dahulu.</span>
                        </p>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-2">
                         <button type="submit" 
                                 formaction="{{ route('capex.reject', $capex) }}"
                                 onclick="return validateReject()"
                                 class="px-6 py-2.5 bg-red-50 text-red-600 font-medium rounded-lg hover:bg-red-100 transition focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                             Reject Capex
                         </button>
                         <button type="submit" 
                                 formaction="{{ route('capex.hold', $capex) }}"
                                 onclick="return validateHold()"
                                 class="px-6 py-2.5 bg-orange-50 text-orange-600 font-medium rounded-lg hover:bg-orange-100 transition focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                             Hold Capex
                         </button>
                         <button type="submit" 
                                 formaction="{{ route('capex.approve', $capex) }}"
                                 class="px-6 py-2.5 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition shadow-md hover:shadow-lg focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center gap-2">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                             Approve & Sign
                         </button>
                    </div>
                </form>
            </div>

            <script>
                function showError(message) {
                    const errorEl = document.getElementById('remarks-error');
                    const textarea = document.getElementById('remarks-input');
                    
                    errorEl.querySelector('span').innerText = message;
                    errorEl.classList.remove('hidden');
                    textarea.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                    textarea.classList.remove('border-gray-300');
                    textarea.focus();
                    
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
                    return confirm('Apakah Anda yakin ingin MELAKUKAN REJECT pada Capex ini? Tindakan ini tidak dapat dibatalkan.');
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
        @elseif($capex->status == 'On Hold')
             <div class="bg-orange-50 border-2 border-orange-200 rounded-xl shadow-sm p-6 mt-6">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-orange-100 rounded-lg text-orange-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            ⏸️ Capex Ditunda (On Hold)
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Menunggu tindak lanjut. Silakan hubungi approver terkait.
                        </p>
                    </div>
                </div>
             </div>
        @elseif(!$canApprove && $capex->status == 'Pending')
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mt-6 text-center">
                <p class="text-green-800 text-sm italic font-medium">Menunggu giliran approval (Level {{ $capex->current_step }}).</p>
            </div>
        @endif

        <!-- Approval Timeline -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Riwayat Approval (Digital)</h3>
            <div class="relative pl-6 border-l-2 border-gray-200 space-y-8">
                @for($i = 1; $i <= 5; $i++)
                    @php
                        $stepApproval = $capex->approvals->where('column_index', $i)->first();
                        $status = $stepApproval->status ?? ($i < $capex->current_step ? 'Approved' : 'Pending');
                        
                        $isCurrent = $i == $capex->current_step && $capex->status == 'Pending';
                        $isPast = $i < $capex->current_step || $capex->status == 'Approved';
                        
                        // Determine Approver Label
                        $stepConfig = $departmentConfigs[$i] ?? null;
                        $approverLabel = 'Approver';
                        if ($stepConfig) {
                            if ($stepConfig->approver_user_id) {
                                $approverName = $stepConfig->approver->name ?? 'User #' . $stepConfig->approver_user_id;
                                $approverPosition = $stepConfig->approver->position ?? '';
                                $approverLabel = $approverPosition ? $approverName . ' (' . $approverPosition . ')' : $approverName;
                            } elseif ($stepConfig->approver_role) {
                                $approverLabel = $stepConfig->approver_role;
                            }
                        }
                    @endphp

                    <div class="relative pb-8">
                        @if($i < 5) 
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 {{ $isPast ? 'bg-green-600' : 'bg-gray-200' }}" aria-hidden="true"></span>
                        @endif
                        <div class="relative flex space-x-3">
                            <div>
                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white {{ $status == 'Approved' ? 'bg-green-500' : ($status == 'Rejected' ? 'bg-red-500' : ($isCurrent ? 'bg-green-600 animate-pulse' : 'bg-gray-200')) }}">
                                    @if($status == 'Approved')
                                       <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                           <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                       </svg>
                                    @else
                                        <span class="text-white text-xs font-bold">{{ $i }}</span>
                                    @endif
                                </span>
                            </div>
                            <div class="min-w-0 flex-1 pt-1.5 ">
                                <p class="text-sm font-medium text-gray-900">
                                    Approver {{ $i }} ({{ $approverLabel }})
                                </p>
                                <div class="mt-1">
                                    @if($status === 'Approved')
                                        <div class="flex flex-col items-start gap-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                            @if($stepApproval && $stepApproval->approver)
                                                <span class="text-xs text-gray-500">by {{ $stepApproval->approver->name }}</span>
                                                <span class="text-[10px] text-gray-400">{{ $stepApproval->signed_at ? $stepApproval->signed_at->format('d M H:i') : '' }}</span>
                                            @endif
                                        </div>
                                    @elseif($status === 'Rejected')
                                        <div class="flex flex-col items-start gap-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                            @if($stepApproval && $stepApproval->approver)
                                                <span class="text-xs text-gray-500">by {{ $stepApproval->approver->name }}</span>
                                            @endif
                                            @if($stepApproval && $stepApproval->remarks)
                                                <p class="text-xs text-red-600 font-medium">Reason: {{ $stepApproval->remarks }}</p>
                                            @endif
                                        </div>
                                    @elseif($status === 'On Hold')
                                        <div class="flex flex-col items-start gap-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">On Hold</span>
                                            @if($stepApproval && $stepApproval->approver)
                                                <span class="text-xs text-gray-500">by {{ $stepApproval->approver->name }}</span>
                                            @endif
                                            @if($stepApproval && $stepApproval->remarks)
                                                <p class="text-xs text-orange-600 font-medium">Note: {{ $stepApproval->remarks }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-500">Pending</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
                
                <!-- Manual Step (Wet Signature) -->
                @php
                    $allDigitalDone = $capex->current_step > 5 || $capex->status === 'Approved';
                    $dotWetColor    = $capex->is_verified
                        ? 'border-green-500 bg-green-500'
                        : ($hasWetFile ? 'border-green-500 bg-green-100' : 'border-gray-300 bg-gray-100');
                @endphp
                <div class="relative">
                    <div class="absolute -left-[31px] border-2 {{ $dotWetColor }} w-4 h-4 rounded-full"></div>

                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="flex-1">
                            <span class="text-sm font-bold {{ $capex->is_verified ? 'text-green-700' : 'text-gray-600' }}">
                                Manual Signature (Deputy COO &amp; CEO)
                            </span>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Offline process after all digital approvals.
                                Cetak PDF, dapatkan tanda tangan basah, lalu upload dokumen yang sudah ditandatangani.
                            </p>

                            @if($hasWetFile)
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <a href="{{ asset('storage/' . $capex->signed_file_path) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-green-50 text-green-700 border border-green-200 rounded-lg hover:bg-green-100 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View File
                                    </a>
                                    <a href="{{ asset('storage/' . $capex->signed_file_path) }}" download
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-100 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Download
                                    </a>

                                    @if(auth()->user()->hasRole('Admin') && !$capex->is_verified)
                                        <form action="{{ route('capex.verify', $capex) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    onclick="return confirm('Verifikasi dokumen dan buat PR otomatis?')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-green-600 text-white border border-green-600 rounded-lg hover:bg-green-700 transition shadow-sm animate-pulse">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Verify &amp; Create PR
                                            </button>
                                        </form>
                                    @elseif($capex->is_verified)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-green-100 text-green-700 border border-green-300 rounded-lg">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Verified
                                        </span>
                                        @if($capex->pr_id)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-green-50 text-green-700 border border-green-200 rounded-lg">PR Created</span>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Upload button / status on right -->
                        <div class="flex-shrink-0">
                            @if($capex->is_verified)
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">✓ Verified</span>
                            @elseif($allDigitalDone)
                                @if (auth()->user()->hasRole('Admin'))
                                    @if($hasWetFile)
                                        <form action="{{ route('capex.upload', $capex) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-1">
                                            @csrf
                                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-300 rounded-lg hover:bg-yellow-100 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                Re-upload
                                                <input type="file" name="signed_file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="this.form.submit()" required>
                                            </label>
                                        </form>
                                    @else
                                        <form action="{{ route('capex.upload', $capex) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition cursor-pointer shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                Upload Dokumen Basah
                                                <input type="file" name="signed_file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="this.form.submit()" required>
                                            </label>
                                        </form>
                                    @endif
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-50 text-blue-600 border border-blue-200">Menunggu Upload</span>
                                @endif
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-400">Menunggu Digital</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
