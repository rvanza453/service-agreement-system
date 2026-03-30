<x-serviceagreementsystem::layouts.master :title="'Detail USPK'">
    @push('actions')
        @if($uspk->isEditable())
            <a href="{{ route('sas.uspk.edit', $uspk) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('sas.uspk.submit', $uspk) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin mensubmit USPK ini?')">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-paper-plane"></i> Submit USPK
                </button>
            </form>
        @endif
        <a href="{{ route('sas.uspk.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    @endpush

    {{-- Header Info --}}
    <div class="card mb-4">
        <div class="card-header">
            <div>
                <div class="card-title" style="font-size: 18px;">{{ $uspk->uspk_number }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    Dibuat oleh {{ $uspk->submitter->name ?? '-' }} · {{ $uspk->created_at->format('d M Y H:i') }}
                </div>
            </div>
            <span class="badge badge-{{ $uspk->status }}" style="font-size: 13px; padding: 6px 14px;">
                {{ ucfirst(str_replace('_', ' ', $uspk->status)) }}
            </span>
        </div>
        <div class="card-body">
            <div style="font-size: 20px; font-weight: 700; margin-bottom: 8px;">{{ $uspk->title }}</div>

            @if($uspk->description)
                <p style="color: var(--text-secondary); margin-bottom: 16px;">{{ $uspk->description }}</p>
            @endif

            <div class="form-row" style="margin-top: 20px;">
                <div>
                    <div class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Department</div>
                    <div style="font-weight: 600;">{{ $uspk->department->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Afdeling</div>
                    <div style="font-weight: 600;">{{ $uspk->subDepartment->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Blok</div>
                    <div style="font-weight: 600;">{{ $uspk->block->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Aktivitas</div>
                    <div style="font-weight: 600;">{{ $uspk->job->name ?? '-' }}</div>
                </div>
            </div>

            <div class="form-row" style="margin-top: 16px;">
                <div>
                    <div class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Jenis Pekerjaan</div>
                    <div style="font-weight: 600;">{{ $uspk->work_type ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Lokasi</div>
                    <div style="font-weight: 600;">{{ $uspk->location ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Estimasi Nilai</div>
                    <div style="font-weight: 700; color: var(--accent); font-size: 16px;">Rp {{ number_format($uspk->estimated_value, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Durasi</div>
                    <div style="font-weight: 600;">{{ $uspk->estimated_duration ? $uspk->estimated_duration . ' hari' : '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tender Pembanding --}}
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-balance-scale" style="color: var(--warning); margin-right: 8px;"></i> Perbandingan Tender</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kontraktor</th>
                        <th>Perusahaan</th>
                        <th>Nilai Tender</th>
                        <th>Durasi</th>
                        <th>Keterangan</th>
                        <th>Lampiran</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($uspk->tenders as $index => $tender)
                    <tr style="{{ $tender->is_selected ? 'background: rgba(16, 185, 129, 0.05);' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td style="font-weight: 600; color: var(--text-primary);">{{ $tender->contractor->name ?? '-' }}</td>
                        <td>{{ $tender->contractor->company_name ?? '-' }}</td>
                        <td style="font-weight: 600;">Rp {{ number_format($tender->tender_value, 0, ',', '.') }}</td>
                        <td>{{ $tender->tender_duration ? $tender->tender_duration . ' hari' : '-' }}</td>
                        <td>{{ $tender->description ?? '-' }}</td>
                        <td>
                            @if($tender->attachment_path)
                                <a href="{{ asset('storage/' . $tender->attachment_path) }}" target="_blank" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-download"></i> File
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($tender->is_selected)
                                <span class="badge badge-approved"><i class="fas fa-check" style="margin-right: 4px;"></i> Dipilih</span>
                            @else
                                <span class="badge badge-draft">Pembanding</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada tender.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Approval Timeline --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-clipboard-check" style="color: var(--success); margin-right: 8px;"></i> Approval Timeline</div>
        </div>
        <div class="card-body">
            @if($uspk->approvals->count() > 0)
                <div class="timeline">
                    @foreach($uspk->approvals as $approval)
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $approval->status }}"></div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <div>
                                    <span class="timeline-name">{{ $approval->approver->name ?? 'Unknown' }}</span>
                                    <span class="badge badge-{{ $approval->status }}" style="margin-left: 8px;">
                                        {{ ucfirst($approval->status) }}
                                    </span>
                                </div>
                                <span class="timeline-date">
                                    {{ $approval->approved_at ? $approval->approved_at->format('d M Y H:i') : 'Menunggu' }}
                                </span>
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted);">Level {{ $approval->level }} · {{ $approval->role_name }}</div>
                            @if($approval->comment)
                                <div class="timeline-comment">
                                    <i class="fas fa-quote-left" style="font-size: 10px; opacity: 0.5;"></i>
                                    {{ $approval->comment }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Approval form for current approver --}}
                @php
                    $pendingApproval = $uspk->approvals->where('user_id', auth()->id())->where('status', 'pending')->first();
                @endphp

                @if($pendingApproval)
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                    <h4 style="font-size: 14px; margin-bottom: 16px;">
                        <i class="fas fa-gavel" style="color: var(--accent);"></i> Tindakan Anda
                    </h4>

                    <div class="form-group">
                        <label class="form-label">Komentar</label>
                        <textarea id="approvalComment" class="form-control" rows="3" placeholder="Masukkan komentar (wajib untuk reject)"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display: flex; align-items: center; justify-content: space-between;">
                            <span>Pilih Kontraktor Pemenang (Opsional)</span>
                            <span class="badge badge-draft" style="font-size: 10px; font-weight: normal;">Hanya untuk Approver Tertentu</span>
                        </label>
                        <select id="winningContractor" class="form-control">
                            <option value="">-- Biarkan Default / Ikuti Pilihan Sebelumnya --</option>
                            @foreach($uspk->tenders as $tender)
                                <option value="{{ $tender->id }}" {{ $tender->is_selected ? 'selected' : '' }}>
                                    {{ $tender->contractor->name ?? 'Unknown' }} - Rp {{ number_format($tender->tender_value, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-muted mt-1" style="font-size: 11px;">
                            Jika tidak dipilih, sistem akan menggunakan kontraktor yang sudah ditandai "Dipilih" saat pengajuan awal.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <form action="{{ route('sas.uspk.approve', $uspk) }}" method="POST" style="display: inline;">
                            @csrf
                            <input type="hidden" name="comment" id="approveComment">
                            <input type="hidden" name="selected_tender_id" id="approveTenderId">
                            <button type="submit" class="btn btn-success" onclick="
                                document.getElementById('approveComment').value = document.getElementById('approvalComment').value;
                                document.getElementById('approveTenderId').value = document.getElementById('winningContractor').value;
                            ">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </form>
                        <form action="{{ route('sas.uspk.reject', $uspk) }}" method="POST" style="display: inline;" onsubmit="return validateReject()">
                            @csrf
                            <input type="hidden" name="comment" id="rejectComment">
                            <button type="submit" class="btn btn-danger" onclick="document.getElementById('rejectComment').value = document.getElementById('approvalComment').value">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            @else
                <div class="empty-state" style="padding: 30px;">
                    <i class="fas fa-clock"></i>
                    <p>Belum ada proses approval. USPK harus disubmit terlebih dahulu.</p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function validateReject() {
            const comment = document.getElementById('approvalComment').value;
            if (!comment.trim()) {
                alert('Komentar wajib diisi untuk reject.');
                return false;
            }
            return confirm('Yakin ingin me-reject USPK ini?');
        }
    </script>
    @endpush
</x-serviceagreementsystem::layouts.master>
