<x-qccomplaintsystem::layouts.master :title="'Detail Temuan QC'">
    <style>
        .finding-show {
            display: grid;
            gap: 14px;
        }

        .hero {
            border: 1px solid #d7e4dd;
            border-radius: 14px;
            background:
                radial-gradient(circle at 0% 0%, rgba(15, 118, 110, 0.12), transparent 45%),
                radial-gradient(circle at 100% 100%, rgba(30, 64, 175, 0.1), transparent 45%),
                #ffffff;
            padding: 18px;
            display: grid;
            gap: 14px;
        }

        .hero-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .hero-number {
            font-size: 13px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .hero-title {
            font-size: 26px;
            line-height: 1.2;
            margin: 0;
            letter-spacing: -0.02em;
        }

        .pill-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .meta-item {
            border: 1px solid #dbe7e1;
            border-radius: 10px;
            background: #f8fcfa;
            padding: 9px 10px;
        }

        .meta-item .label {
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .meta-item .value {
            font-weight: 700;
            font-size: 13px;
        }

        .progress-wrap {
            display: grid;
            gap: 7px;
        }

        .progress-label {
            font-size: 12px;
            color: #475569;
            font-weight: 700;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
            border: 1px solid #cfd8e3;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0f766e, #0284c7);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1.35fr 1fr;
            gap: 14px;
        }

        .section {
            border: 1px solid #d7e4dd;
            border-radius: 14px;
            background: #ffffff;
            overflow: hidden;
        }

        .section-head {
            padding: 12px 14px;
            font-weight: 800;
            border-bottom: 1px solid #dbe7e1;
            background: linear-gradient(180deg, #f8fcfa, #ffffff);
            font-size: 13px;
        }

        .section-body {
            padding: 14px;
        }

        .kv {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 8px 10px;
            margin-bottom: 10px;
        }

        .kv .k {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }

        .kv .v {
            font-size: 13px;
        }

        .photo-box {
            border: 1px dashed #b8cdc2;
            border-radius: 12px;
            padding: 12px;
            background: #f8fcfa;
        }

        .step-list {
            display: grid;
            gap: 10px;
        }

        .step-item {
            border: 1px solid #d9e4df;
            border-radius: 10px;
            padding: 10px;
            background: #ffffff;
        }

        .step-item.active {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.12);
            background: #fffbeb;
        }

        .step-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }

        .step-title {
            font-size: 13px;
            font-weight: 800;
        }

        .chip {
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .chip.pending { background: #fef3c7; color: #92400e; }
        .chip.approved { background: #dcfce7; color: #166534; }
        .chip.rejected { background: #fee2e2; color: #b91c1c; }

        .action-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .approval-action-box {
            border: 1px solid #d7dce3;
            border-radius: 12px;
            background: #f8fafc;
            padding: 14px;
        }

        .approval-action-box textarea {
            min-height: 110px;
        }

        .approval-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .btn-approve {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
            min-width: 140px;
            justify-content: center;
        }

        .btn-reject {
            background: #b91c1c;
            border-color: #b91c1c;
            color: #fff;
            min-width: 140px;
            justify-content: center;
        }

        .revision-alert {
            border: 1px solid #fca5a5;
            background: #fef2f2;
            color: #991b1b;
            border-radius: 12px;
            padding: 12px;
            font-size: 13px;
            font-weight: 700;
        }

        @media (max-width: 980px) {
            .meta-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .content-grid { grid-template-columns: 1fr; }
            .action-grid { grid-template-columns: 1fr; }
            .kv { grid-template-columns: 1fr; gap: 2px; }
        }
    </style>

    @push('actions')
        @if($finding->status === 'open' && in_array(auth()->user()?->moduleRole('qc'), ['QC Admin', 'QC Officer']))
            <a href="{{ route('qc.findings.edit', $finding) }}" class="btn"><i class="fas fa-pen"></i> Edit</a>
        @endif
    @endpush

    @php
        $statusLabel = $finding->status === 'in_review' ? 'IN REVIEW' : strtoupper($finding->status);
        $sourceLabel = $finding->source_type === 'self' ? 'Temuan Sendiri'
            : ($finding->source_type === 'worker_direct'
                ? 'Pekerja Lain (Direct)'
                : $finding->source_type);
        $totalSteps = $finding->approvalSteps->count();
        $approvedSteps = $finding->approvalSteps->where('status', 'approved')->count();
        $progressPercent = $totalSteps > 0 ? (int) round(($approvedSteps / $totalSteps) * 100) : 0;
        $resolvedPicIds = collect(array_map('intval', (array) ($finding->pic_user_ids ?? [])));
        if (!empty($finding->pic_user_id)) {
            $resolvedPicIds->push((int) $finding->pic_user_id);
        }
        $picDisplayNames = $resolvedPicIds
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($id) => $picNameMap[$id] ?? null)
            ->filter()
            ->values();
    @endphp

    <div class="finding-show">
        <section class="hero">
            <div class="hero-top">
                <div>
                    <div class="hero-number">Laporan {{ $finding->finding_number }}</div>
                    <h1 class="hero-title">{{ $finding->title }}</h1>
                </div>
                <div class="pill-row">
                    <span class="badge badge-{{ $finding->status }}">{{ $statusLabel }}</span>
                    <span class="badge badge-{{ $finding->urgency }}">{{ \Modules\QcComplaintSystem\Models\QcFinding::urgencyLabel($finding->urgency) }}</span>
                    @if($finding->needs_resubmission)
                        <span class="badge" style="background:#fee2e2;color:#b91c1c;">Perlu Perbaikan Bukti</span>
                    @endif
                </div>
            </div>

            @if($totalSteps > 0)
                <div class="progress-wrap">
                    <div class="progress-label">
                        Progress Approval: {{ $approvedSteps }} / {{ $totalSteps }} level selesai
                        @if($currentApprovalStep)
                            - Menunggu Level {{ $currentApprovalStep->level }} ({{ $currentApprovalStep->approver?->name ?? '-' }})
                        @endif
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $progressPercent }}%;"></div>
                    </div>
                </div>
            @endif

            <div class="meta-grid">
                <div class="meta-item">
                    <div class="label">Tanggal Temuan</div>
                    <div class="value">{{ optional($finding->finding_date)->format('d M Y') ?? '-' }}</div>
                </div>
                <div class="meta-item">
                    <div class="label">Pelapor</div>
                    <div class="value">{{ $finding->reporter?->name ?? $finding->reporter_name ?? '-' }}</div>
                </div>
                <div class="meta-item">
                    <div class="label">PIC</div>
                    <div class="value">{{ $picDisplayNames->isNotEmpty() ? $picDisplayNames->join(', ') : '-' }}</div>
                </div>
                <div class="meta-item">
                    <div class="label">Sumber</div>
                    <div class="value">{{ $sourceLabel }}</div>
                </div>
                <div class="meta-item">
                    <div class="label">Kategori</div>
                    <div class="value">
                        @php
                            $catHierarchy = \Modules\QcComplaintSystem\Models\QcFinding::categoryHierarchy();
                            $catLabel = $finding->kategori && isset($catHierarchy[$finding->kategori]) 
                                ? $catHierarchy[$finding->kategori]['label'] 
                                : ucfirst($finding->kategori ?: '-');
                            
                            $subLabel = '';
                            if ($finding->sub_kategori && isset($catHierarchy[$finding->kategori]['subs'][$finding->sub_kategori])) {
                                $subLabel = ' - ' . $catHierarchy[$finding->kategori]['subs'][$finding->sub_kategori]['label'];
                            }
                            
                            $codeLabel = $finding->kategori_code ? ' ('.$finding->kategori_code.')' : '';
                        @endphp
                        {{ $catLabel }}{{ $subLabel }}{{ $codeLabel }}
                    </div>
                </div>
                <div class="meta-item">
                    <div class="label">Dibuat Oleh</div>
                    <div class="value">{{ $finding->creator?->name ?? '-' }}</div>
                </div>
            </div>
        </section>

        <section class="content-grid">
            <div class="section">
                <div class="section-head">Informasi Temuan</div>
                <div class="section-body">
                    <div class="kv"><div class="k">Deskripsi</div><div class="v">{{ $finding->description ?: '-' }}</div></div>
                    <div class="kv"><div class="k">Department</div><div class="v">{{ $finding->department?->name ?? '-' }}</div></div>
                    <div class="kv"><div class="k">Afdeling</div><div class="v">{{ $finding->subDepartment?->name ?? '-' }}</div></div>
                    <div class="kv"><div class="k">Blok</div><div class="v">{{ $finding->block?->name ?? '-' }}</div></div>
                    <div class="kv" style="margin-bottom:0;"><div class="k">Detail Lokasi</div><div class="v">{{ $finding->location ?: '-' }}</div></div>

                    <div class="photo-box" style="margin-top:12px;">
                        <div class="k" style="margin-bottom:8px;">Lampiran / Bukti Temuan</div>
                        @if(!empty($finding->finding_attachments) && is_array($finding->finding_attachments))
                            <div class="flex flex-col gap-2" style="display:flex; flex-direction:column; gap:8px;">
                                @foreach($finding->finding_attachments as $attachment)
                                    <a class="btn" href="{{ asset('storage/' . $attachment) }}" target="_blank" style="width:fit-content; justify-content:flex-start;">
                                        <i class="fas fa-paperclip"></i> Lihat Lampiran {{ $loop->iteration }}
                                    </a>
                                @endforeach
                            </div>
                        @elseif($finding->finding_photo_path)
                            <a class="btn" href="{{ asset('storage/' . $finding->finding_photo_path) }}" target="_blank" style="width:fit-content;"><i class="fas fa-image"></i> Lihat Foto (Lama)</a>
                        @else
                            <div class="text-muted">Belum ada lampiran bukti temuan.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-head">Workflow Penyelesaian & Approval</div>
                <div class="section-body">
                    @if($finding->completion_submitted_at)
                        <div class="kv"><div class="k">Disubmit Oleh</div><div class="v">{{ $finding->completionSubmitter?->name ?? '-' }} pada {{ $finding->completion_submitted_at?->format('d M Y H:i') }}</div></div>
                        <div class="kv"><div class="k">Catatan Penyelesaian</div><div class="v">{{ $finding->completion_note }}</div></div>
                        <div class="photo-box" style="margin-bottom:12px;">
                            <div class="k" style="margin-bottom:8px;">File Bukti Penyelesaian</div>
                            @if($finding->completionEvidences->isNotEmpty())
                                <div style="display:grid; gap:8px;">
                                    @foreach($finding->completionEvidences as $evidence)
                                        <a class="btn" href="{{ asset('storage/' . $evidence->file_path) }}" target="_blank" style="justify-content:flex-start;">
                                            <i class="fas fa-paperclip"></i>
                                            {{ $evidence->original_name }}
                                            <span class="text-muted" style="margin-left:auto; font-size:11px;">{{ number_format(($evidence->size ?? 0) / 1024, 0, ',', '.') }} KB</span>
                                        </a>
                                    @endforeach
                                </div>
                            @elseif($finding->completion_photo_path)
                                <a class="btn" href="{{ asset('storage/' . $finding->completion_photo_path) }}" target="_blank"><i class="fas fa-image"></i> Lihat Bukti Lama</a>
                            @else
                                <div class="text-muted">Belum ada file bukti penyelesaian.</div>
                            @endif
                        </div>
                    @else
                        <div class="text-muted" style="margin-bottom:12px;">Belum ada bukti penyelesaian yang disubmit.</div>
                    @endif

                    @if($finding->completion_approved_at)
                        <div class="kv"><div class="k">Approved Oleh</div><div class="v">{{ $finding->completionApprover?->name ?? '-' }} pada {{ $finding->completion_approved_at?->format('d M Y H:i') }}</div></div>
                        <div class="kv"><div class="k">Catatan Approval Final</div><div class="v">{{ $finding->completion_approval_note ?: '-' }}</div></div>
                    @elseif($finding->completion_rejected_note)
                        <div class="kv"><div class="k">Catatan Reject</div><div class="v">{{ $finding->completion_rejected_note }}</div></div>
                    @endif

                    @if($finding->approvalSteps->isNotEmpty())
                        <div style="margin-top:12px;">
                            <div class="k" style="margin-bottom:8px;">Tahapan Approval</div>
                            <div class="step-list">
                                @foreach($finding->approvalSteps as $step)
                                    @php
                                        $isActiveStep = $currentApprovalStep && (int) $step->id === (int) $currentApprovalStep->id;
                                    @endphp
                                    <div class="step-item {{ $isActiveStep ? 'active' : '' }}">
                                        <div class="step-top">
                                            <div class="step-title">Level {{ $step->level }} - {{ $step->approver?->name ?? '-' }}</div>
                                            <span class="chip {{ $step->status }}">{{ $step->status }}</span>
                                        </div>
                                        @if($step->acted_at)
                                            <div class="text-muted" style="font-size:12px;">Diproses {{ $step->acted_at->format('d M Y H:i') }} oleh {{ $step->actor?->name ?? '-' }}</div>
                                        @endif
                                        @if($step->note)
                                            <div style="font-size:12px; margin-top:4px;">Catatan: {{ $step->note }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @if($finding->status === 'open' && $canSubmitCompletion)
        <div class="section" style="margin-top:14px;">
            <div class="section-head">{{ $finding->needs_resubmission ? 'Submit Ulang Bukti Penyelesaian' : 'Submit Penyelesaian' }}</div>
            <div class="section-body">
                @if($finding->needs_resubmission)
                    <div class="revision-alert" style="margin-bottom:10px;">
                        Laporan ini ditolak pada tahap approval. Harap lengkapi bukti penyelesaian lalu submit ulang agar masuk ke alur approval lagi.
                    </div>
                @endif
                <form method="POST" action="{{ route('qc.findings.submit-completion', $finding) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="field">
                        <label>Catatan Penyelesaian</label>
                        <textarea name="completion_note" rows="3" class="input" required></textarea>
                    </div>
                    <div class="field">
                        <label>File Bukti Penyelesaian (bisa lebih dari satu)</label>
                        <div class="file-picker-wrap">
                            <button type="button" class="btn" id="btn-add-files"><i class="fas fa-plus"></i> Pilih File</button>
                            <input type="file" id="file-picker-input" style="display:none;" multiple>
                            <input type="file" id="file-final-input" name="completion_files[]" multiple style="display:none;">
                        </div>
                        <div id="file-list" style="display:none; margin-top:8px; display:grid; gap:6px;"></div>
                        <div class="text-muted" style="margin-top:6px;">Boleh foto, PDF, dokumen, atau file lain yang relevan. Bisa pilih beberapa kali untuk tambah file. Maksimal 10 file, 10 MB per file.</div>
                    </div>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i> Submit Penyelesaian</button>
                </form>
            </div>
        </div>
    @endif

    @if($finding->hasPendingCompletionApproval() && $canApproveCompletion)
        <div class="section" style="margin-top:14px;">
            <div class="section-head">Tindakan Approval</div>
            <div class="section-body">
                @if($currentApprovalStep)
                    <div class="text-muted" style="margin-bottom:10px;">Anda sedang memproses Level {{ $currentApprovalStep->level }} dari {{ $finding->approvalSteps->count() }}.</div>
                @endif

                <form id="approval-action-form" method="POST" action="{{ route('qc.findings.approve-completion', $finding) }}">
                    @csrf
                    <div class="approval-action-box">
                        <div class="field" style="margin-bottom:0;">
                            <label>Catatan / Alasan (Opsional untuk Approve, wajib untuk Reject)</label>
                            <textarea id="approval-action-note" class="input" placeholder="Tulis catatan di sini..."></textarea>
                            <input type="hidden" name="approval_note" id="approval-note-input">
                            <input type="hidden" name="rejected_note" id="rejected-note-input">
                        </div>
                    </div>

                    <div class="approval-buttons">
                        <button type="button" class="btn btn-reject" id="btn-reject"><i class="fas fa-times"></i> Reject</button>
                        <button type="button" class="btn btn-approve" id="btn-approve"><i class="fas fa-check"></i> Approve</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Comments Section --}}
    <div style="margin-top:14px;">
        @include('qccomplaintsystem::findings._comments_section')
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                /* ── Multi-file picker ── */
                const btnAdd      = document.getElementById('btn-add-files');
                const pickerInput = document.getElementById('file-picker-input');
                const finalInput  = document.getElementById('file-final-input');
                const fileList    = document.getElementById('file-list');

                if (btnAdd && pickerInput && finalInput && fileList) {
                    const MAX_FILES = 10;
                    const MAX_BYTES = 10 * 1024 * 1024;
                    let dt = new DataTransfer();

                    function formatSize(bytes) {
                        if (bytes < 1024) return bytes + ' B';
                        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
                        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
                    }

                    function renderList() {
                        fileList.innerHTML = '';
                        if (dt.files.length === 0) {
                            fileList.style.display = 'none';
                            return;
                        }
                        fileList.style.display = 'grid';
                        Array.from(dt.files).forEach((file, idx) => {
                            const row = document.createElement('div');
                            row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:7px 10px;border:1px solid #d7e4dd;border-radius:8px;background:#f8fcfa;font-size:13px;';
                            row.innerHTML = `<i class="fas fa-paperclip" style="color:#0f766e;"></i>
                                <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${file.name}</span>
                                <span style="color:#64748b;font-size:11px;white-space:nowrap;">${formatSize(file.size)}</span>
                                <button type="button" data-idx="${idx}" style="background:none;border:none;cursor:pointer;color:#b91c1c;padding:0 4px;font-size:14px;" title="Hapus">&times;</button>`;
                            row.querySelector('button').addEventListener('click', function () {
                                const newDt = new DataTransfer();
                                Array.from(dt.files).forEach((f, i) => { if (i !== idx) newDt.items.add(f); });
                                dt = newDt;
                                syncFinal();
                                renderList();
                            });
                            fileList.appendChild(row);
                        });
                        const countMsg = document.createElement('div');
                        countMsg.style.cssText = 'font-size:12px;color:#475569;';
                        countMsg.textContent = dt.files.length + ' file dipilih';
                        fileList.appendChild(countMsg);
                    }

                    function syncFinal() {
                        const newDt = new DataTransfer();
                        Array.from(dt.files).forEach(f => newDt.items.add(f));
                        finalInput.files = newDt.files;
                    }

                    btnAdd.addEventListener('click', () => pickerInput.click());

                    pickerInput.addEventListener('change', function () {
                        let rejected = [];
                        Array.from(this.files).forEach(file => {
                            if (dt.files.length >= MAX_FILES) {
                                rejected.push(file.name + ' (batas 10 file)');
                                return;
                            }
                            if (file.size > MAX_BYTES) {
                                rejected.push(file.name + ' (lebih dari 10 MB)');
                                return;
                            }
                            dt.items.add(file);
                        });
                        this.value = '';
                        syncFinal();
                        renderList();
                        if (rejected.length) {
                            alert('File berikut tidak ditambahkan:\n' + rejected.join('\n'));
                        }
                    });

                    /* Validate at least 1 file on submit */
                    const completionForm = btnAdd.closest('form');
                    if (completionForm) {
                        completionForm.addEventListener('submit', function (e) {
                            if (dt.files.length === 0) {
                                e.preventDefault();
                                alert('Harap pilih minimal 1 file bukti penyelesaian.');
                            }
                        });
                    }
                }

                /* ── Approval action form ── */
                const form = document.getElementById('approval-action-form');
                if (!form) {
                    return;
                }

                const noteArea = document.getElementById('approval-action-note');
                const approveInput = document.getElementById('approval-note-input');
                const rejectInput = document.getElementById('rejected-note-input');
                const approveButton = document.getElementById('btn-approve');
                const rejectButton = document.getElementById('btn-reject');

                const approveAction = '{{ route('qc.findings.approve-completion', $finding) }}';
                const rejectAction = '{{ route('qc.findings.reject-completion', $finding) }}';

                approveButton.addEventListener('click', function () {
                    form.action = approveAction;
                    approveInput.value = noteArea.value;
                    rejectInput.value = '';
                    noteArea.setCustomValidity('');
                    form.submit();
                });

                rejectButton.addEventListener('click', function () {
                    const note = noteArea.value.trim();
                    if (!note) {
                        noteArea.setCustomValidity('Alasan reject wajib diisi.');
                        noteArea.reportValidity();
                        return;
                    }

                    noteArea.setCustomValidity('');
                    form.action = rejectAction;
                    approveInput.value = '';
                    rejectInput.value = note;
                    form.submit();
                });

                noteArea.addEventListener('input', function () {
                    if (noteArea.value.trim()) {
                        noteArea.setCustomValidity('');
                    }
                });
            });
        </script>
    @endpush
</x-qccomplaintsystem::layouts.master>
