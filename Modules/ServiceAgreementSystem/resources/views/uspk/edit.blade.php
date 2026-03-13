<x-serviceagreementsystem::layouts.master :title="'Edit USPK'">
    @push('actions')
        <a href="{{ route('sas.uspk.show', $uspk) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    @endpush

    <form action="{{ route('sas.uspk.update', $uspk) }}" method="POST" enctype="multipart/form-data" id="uspkForm">
        @csrf @method('PUT')

        {{-- Informasi Pekerjaan --}}
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-info-circle" style="color: var(--accent); margin-right: 8px;"></i> Informasi Pekerjaan</div>
                <span class="text-muted" style="font-size: 12px;">{{ $uspk->uspk_number }}</span>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label required">Judul Pekerjaan</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $uspk->title) }}" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Jenis Pekerjaan</label>
                        <input type="text" name="work_type" class="form-control" value="{{ old('work_type', $uspk->work_type) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lokasi Pekerjaan</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location', $uspk->location) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $uspk->description) }}</textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Estimasi Nilai (Rp)</label>
                        <input type="number" name="estimated_value" class="form-control" value="{{ old('estimated_value', $uspk->estimated_value) }}" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estimasi Durasi (Hari)</label>
                        <input type="number" name="estimated_duration" class="form-control" value="{{ old('estimated_duration', $uspk->estimated_duration) }}" min="1">
                    </div>
                </div>
            </div>
        </div>

        {{-- Lokasi & Budget --}}
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-map-marker-alt" style="color: var(--success); margin-right: 8px;"></i> Lokasi & Budget</div>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Department</label>
                        <select name="department_id" id="department_id" class="form-control" required>
                            <option value="">-- Pilih Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $uspk->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Afdeling</label>
                        <select name="sub_department_id" id="sub_department_id" class="form-control" required>
                            <option value="">-- Pilih Afdeling --</option>
                            @foreach($subDepartments as $sd)
                                <option value="{{ $sd->id }}" {{ old('sub_department_id', $uspk->sub_department_id) == $sd->id ? 'selected' : '' }}>
                                    {{ $sd->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Blok</label>
                        <select name="block_id" id="block_id" class="form-control" required>
                            <option value="">-- Pilih Blok --</option>
                            @foreach($blocks as $b)
                                <option value="{{ $b->id }}" {{ old('block_id', $uspk->block_id) == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} {{ $b->code ? '(' . $b->code . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Aktivitas (Job)</label>
                        <select name="job_id" id="job_id" class="form-control" required>
                            <option value="">-- Pilih Aktivitas --</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}" {{ old('job_id', $uspk->job_id) == $job->id ? 'selected' : '' }}>
                                    {{ $job->code ? $job->code . ' - ' : '' }}{{ $job->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Budget Aktivitas</label>
                    <select name="uspk_budget_activity_id" id="uspk_budget_activity_id" class="form-control">
                        <option value="">-- Pilih Budget (opsional) --</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Tender Pembanding --}}
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-balance-scale" style="color: var(--warning); margin-right: 8px;"></i> Tender Pembanding</div>
                <button type="button" id="addTender" class="btn btn-primary btn-sm" onclick="addTenderRow()">
                    <i class="fas fa-plus"></i> Tambah Tender
                </button>
            </div>
            <div class="card-body">
                <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 16px;">
                    <i class="fas fa-info-circle"></i> Minimal 1, maksimal 3 tender pembanding.
                </p>

                <div id="tendersContainer">
                    @foreach($uspk->tenders as $index => $tender)
                    <div class="tender-row" data-index="{{ $index }}" style="background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <div style="font-weight: 600; font-size: 14px; color: var(--accent);">
                                <i class="fas fa-user-tie"></i> Tender #{{ $index + 1 }}
                            </div>
                            <div class="d-flex align-center gap-2">
                                <label style="font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                    <input type="radio" name="selected_tender" value="{{ $index }}" {{ $tender->is_selected ? 'checked' : '' }} style="accent-color: var(--success);">
                                    <span style="color: var(--success); font-weight: 600;">Tender Diajukan</span>
                                </label>
                                @if($index > 0)
                                <button type="button" onclick="removeTenderRow({{ $index }})" class="btn btn-danger btn-sm" style="padding: 4px 10px;">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        </div>

                        <input type="hidden" name="tenders[{{ $index }}][is_selected]" value="{{ $tender->is_selected ? '1' : '0' }}" id="tender_selected_{{ $index }}">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Kontraktor</label>
                                <select name="tenders[{{ $index }}][contractor_id]" class="form-control" required>
                                    <option value="">-- Pilih Kontraktor --</option>
                                    @foreach($contractors as $c)
                                        <option value="{{ $c->id }}" {{ $tender->contractor_id == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }} {{ $c->company_name ? '(' . $c->company_name . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label required">Nilai Tender (Rp)</label>
                                <input type="number" name="tenders[{{ $index }}][tender_value]" class="form-control" value="{{ $tender->tender_value }}" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Durasi (Hari)</label>
                                <input type="number" name="tenders[{{ $index }}][tender_duration]" class="form-control" value="{{ $tender->tender_duration }}" min="1">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Lampiran</label>
                                <input type="file" name="tenders[{{ $index }}][attachment]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                @if($tender->attachment_path)
                                    <div style="margin-top: 4px; font-size: 11px; color: var(--text-muted);">
                                        <i class="fas fa-paperclip"></i> File sudah ada
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label">Keterangan</label>
                            <textarea name="tenders[{{ $index }}][description]" class="form-control" rows="2">{{ $tender->description }}</textarea>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
            <a href="{{ route('sas.uspk.show', $uspk) }}" class="btn btn-secondary btn-lg">Batal</a>
        </div>
    </form>

    @push('scripts')
    <script>
        let tenderCount = {{ $uspk->tenders->count() }};
        const maxTenders = 3;
        const contractorsData = @json($contractors);

        function addTenderRow() {
            if (tenderCount >= maxTenders) {
                alert('Maksimal 3 tender pembanding.');
                return;
            }
            const index = tenderCount;
            const container = document.getElementById('tendersContainer');

            let contractorOptions = '<option value="">-- Pilih Kontraktor --</option>';
            contractorsData.forEach(c => {
                const company = c.company_name ? ` (${c.company_name})` : '';
                contractorOptions += `<option value="${c.id}">${c.name}${company}</option>`;
            });

            const html = `
                <div class="tender-row" data-index="${index}" style="background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                        <div style="font-weight: 600; font-size: 14px; color: var(--accent);"><i class="fas fa-user-tie"></i> Tender #${index + 1}</div>
                        <div class="d-flex align-center gap-2">
                            <label style="font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <input type="radio" name="selected_tender" value="${index}" style="accent-color: var(--success);">
                                <span style="color: var(--success); font-weight: 600;">Tender Diajukan</span>
                            </label>
                            <button type="button" onclick="removeTenderRow(${index})" class="btn btn-danger btn-sm" style="padding: 4px 10px;"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <input type="hidden" name="tenders[${index}][is_selected]" value="0" id="tender_selected_${index}">
                    <div class="form-row">
                        <div class="form-group"><label class="form-label required">Kontraktor</label><select name="tenders[${index}][contractor_id]" class="form-control" required>${contractorOptions}</select></div>
                        <div class="form-group"><label class="form-label required">Nilai Tender (Rp)</label><input type="number" name="tenders[${index}][tender_value]" class="form-control" step="0.01" min="0" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Durasi (Hari)</label><input type="number" name="tenders[${index}][tender_duration]" class="form-control" min="1"></div>
                        <div class="form-group"><label class="form-label">Lampiran</label><input type="file" name="tenders[${index}][attachment]" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
                    </div>
                    <div class="form-group mb-0"><label class="form-label">Keterangan</label><textarea name="tenders[${index}][description]" class="form-control" rows="2"></textarea></div>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
            tenderCount++;
            updateAddButton();
        }

        function removeTenderRow(index) {
            const row = document.querySelector(`.tender-row[data-index="${index}"]`);
            if (row) { row.remove(); tenderCount--; updateAddButton(); }
        }

        function updateAddButton() {
            document.getElementById('addTender').style.display = tenderCount >= maxTenders ? 'none' : '';
        }

        document.getElementById('uspkForm').addEventListener('submit', function() {
            document.querySelectorAll('[id^="tender_selected_"]').forEach(el => el.value = '0');
            const sel = document.querySelector('input[name="selected_tender"]:checked');
            if (sel) {
                const f = document.getElementById(`tender_selected_${sel.value}`);
                if (f) f.value = '1';
            }
        });

        // Cascade dropdowns
        document.getElementById('department_id').addEventListener('change', function() {
            const id = this.value;
            const sub = document.getElementById('sub_department_id');
            const blk = document.getElementById('block_id');
            sub.innerHTML = '<option value="">-- Memuat... --</option>';
            blk.innerHTML = '<option value="">-- Pilih Blok --</option>';
            if (!id) { sub.innerHTML = '<option value="">-- Pilih Afdeling --</option>'; return; }
            fetch(`/sas/api/sub-departments/${id}`).then(r => r.json()).then(data => {
                let o = '<option value="">-- Pilih Afdeling --</option>';
                data.forEach(s => o += `<option value="${s.id}">${s.name}</option>`);
                sub.innerHTML = o;
            });
        });

        document.getElementById('sub_department_id').addEventListener('change', function() {
            const id = this.value;
            const blk = document.getElementById('block_id');
            blk.innerHTML = '<option value="">-- Memuat... --</option>';
            if (!id) { blk.innerHTML = '<option value="">-- Pilih Blok --</option>'; return; }
            fetch(`/sas/api/blocks/${id}`).then(r => r.json()).then(data => {
                let o = '<option value="">-- Pilih Blok --</option>';
                data.forEach(b => { const c = b.code ? ` (${b.code})` : ''; o += `<option value="${b.id}">${b.name}${c}</option>`; });
                blk.innerHTML = o;
            });
        });

        updateAddButton();
    </script>
    @endpush
</x-serviceagreementsystem::layouts.master>
