<x-serviceagreementsystem::layouts.master :title="'Buat Pengajuan USPK'">
    @push('actions')
        <a href="{{ route('sas.uspk.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    @endpush

    <form action="{{ route('sas.uspk.store') }}" method="POST" enctype="multipart/form-data" id="uspkForm">
        @csrf

        {{-- Informasi Pekerjaan --}}
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-info-circle" style="color: var(--accent); margin-right: 8px;"></i> Informasi Pekerjaan</div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label required">Judul Pekerjaan</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Contoh: Pekerjaan Pemeliharaan Jalan Blok A1" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Jenis Pekerjaan</label>
                        <input type="text" name="work_type" class="form-control" value="{{ old('work_type') }}" placeholder="Contoh: Borongan Pemeliharaan">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lokasi Pekerjaan</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="Lokasi detail pekerjaan">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi / Uraian Pekerjaan</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan detail pekerjaan yang akan diborongkan">{{ old('description') }}</textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Estimasi Nilai (Rp)</label>
                        <input type="number" name="estimated_value" class="form-control" value="{{ old('estimated_value') }}" placeholder="0" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estimasi Durasi (Hari)</label>
                        <input type="number" name="estimated_duration" class="form-control" value="{{ old('estimated_duration') }}" placeholder="0" min="1">
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
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Afdeling / Sub Department</label>
                        <select name="sub_department_id" id="sub_department_id" class="form-control" required>
                            <option value="">-- Pilih Afdeling --</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Blok</label>
                        <select name="block_id" id="block_id" class="form-control" required>
                            <option value="">-- Pilih Blok --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Aktivitas (Job)</label>
                        <select name="job_id" id="job_id" class="form-control" required>
                            <option value="">-- Pilih Aktivitas --</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}" {{ old('job_id') == $job->id ? 'selected' : '' }}>
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
                    <div id="budgetInfo" style="margin-top: 8px; font-size: 12px; color: var(--text-muted); display: none;">
                        Sisa Budget: <span id="budgetRemaining" style="font-weight: 600; color: var(--success);">-</span>
                    </div>
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
                    <i class="fas fa-info-circle"></i> Minimal 1, maksimal 3 tender pembanding. Pilih salah satu sebagai tender yang diajukan.
                </p>

                <div id="tendersContainer">
                    {{-- Tender row 1 (default) --}}
                    <div class="tender-row" data-index="0" style="background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; margin-bottom: 16px; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <div style="font-weight: 600; font-size: 14px; color: var(--accent);">
                                <i class="fas fa-user-tie"></i> Tender #1
                            </div>
                            <div class="d-flex align-center gap-2">
                                <label style="font-size: 12px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                    <input type="radio" name="selected_tender" value="0" checked style="accent-color: var(--success);">
                                    <span style="color: var(--success); font-weight: 600;">Tender Diajukan</span>
                                </label>
                            </div>
                        </div>

                        <input type="hidden" name="tenders[0][is_selected]" value="0" id="tender_selected_0">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Kontraktor</label>
                                <select name="tenders[0][contractor_id]" class="form-control" required>
                                    <option value="">-- Pilih Kontraktor --</option>
                                    @foreach($contractors as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }} {{ $c->company_name ? '(' . $c->company_name . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label required">Nilai Tender (Rp)</label>
                                <input type="number" name="tenders[0][tender_value]" class="form-control" placeholder="0" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Durasi (Hari)</label>
                                <input type="number" name="tenders[0][tender_duration]" class="form-control" placeholder="0" min="1">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Lampiran Penawaran</label>
                                <input type="file" name="tenders[0][attachment]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label">Keterangan</label>
                            <textarea name="tenders[0][description]" class="form-control" rows="2" placeholder="Keterangan tender"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Simpan Draft
            </button>
            <a href="{{ route('sas.uspk.index') }}" class="btn btn-secondary btn-lg">Batal</a>
        </div>
    </form>

    @push('scripts')
    <script>
        let tenderCount = 1;
        const maxTenders = 3;

        // Contractors data for dynamic rows
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
                const companyInfo = c.company_name ? ` (${c.company_name})` : '';
                contractorOptions += `<option value="${c.id}">${c.name}${companyInfo}</option>`;
            });

            const html = `
                <div class="tender-row" data-index="${index}" style="background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; margin-bottom: 16px; position: relative;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                        <div style="font-weight: 600; font-size: 14px; color: var(--accent);">
                            <i class="fas fa-user-tie"></i> Tender #${index + 1}
                        </div>
                        <div class="d-flex align-center gap-2">
                            <label style="font-size: 12px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <input type="radio" name="selected_tender" value="${index}" style="accent-color: var(--success);">
                                <span style="color: var(--success); font-weight: 600;">Tender Diajukan</span>
                            </label>
                            <button type="button" onclick="removeTenderRow(${index})" class="btn btn-danger btn-sm" style="padding: 4px 10px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="tenders[${index}][is_selected]" value="0" id="tender_selected_${index}">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required">Kontraktor</label>
                            <select name="tenders[${index}][contractor_id]" class="form-control" required>
                                ${contractorOptions}
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Nilai Tender (Rp)</label>
                            <input type="number" name="tenders[${index}][tender_value]" class="form-control" placeholder="0" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Durasi (Hari)</label>
                            <input type="number" name="tenders[${index}][tender_duration]" class="form-control" placeholder="0" min="1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lampiran Penawaran</label>
                            <input type="file" name="tenders[${index}][attachment]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label">Keterangan</label>
                        <textarea name="tenders[${index}][description]" class="form-control" rows="2" placeholder="Keterangan tender"></textarea>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);
            tenderCount++;
            updateAddButton();
        }

        function removeTenderRow(index) {
            const row = document.querySelector(`.tender-row[data-index="${index}"]`);
            if (row) {
                row.remove();
                tenderCount--;
                updateAddButton();
            }
        }

        function updateAddButton() {
            const btn = document.getElementById('addTender');
            if (tenderCount >= maxTenders) {
                btn.style.display = 'none';
            } else {
                btn.style.display = '';
            }
        }

        // Handle selected tender radio
        document.getElementById('uspkForm').addEventListener('submit', function() {
            // Reset all is_selected to 0
            document.querySelectorAll('[id^="tender_selected_"]').forEach(el => el.value = '0');

            // Set the selected one to 1
            const selectedRadio = document.querySelector('input[name="selected_tender"]:checked');
            if (selectedRadio) {
                const idx = selectedRadio.value;
                const hiddenField = document.getElementById(`tender_selected_${idx}`);
                if (hiddenField) hiddenField.value = '1';
            }
        });

        // Cascade dropdown: Department → Sub Department
        document.getElementById('department_id').addEventListener('change', function() {
            const deptId = this.value;
            const subDeptSelect = document.getElementById('sub_department_id');
            const blockSelect = document.getElementById('block_id');

            subDeptSelect.innerHTML = '<option value="">-- Memuat... --</option>';
            blockSelect.innerHTML = '<option value="">-- Pilih Blok --</option>';

            if (!deptId) {
                subDeptSelect.innerHTML = '<option value="">-- Pilih Afdeling --</option>';
                return;
            }

            fetch(`/sas/api/sub-departments/${deptId}`)
                .then(r => r.json())
                .then(data => {
                    let options = '<option value="">-- Pilih Afdeling --</option>';
                    data.forEach(sd => {
                        options += `<option value="${sd.id}">${sd.name}</option>`;
                    });
                    subDeptSelect.innerHTML = options;
                });
        });

        // Cascade dropdown: Sub Department → Block
        document.getElementById('sub_department_id').addEventListener('change', function() {
            const subDeptId = this.value;
            const blockSelect = document.getElementById('block_id');

            blockSelect.innerHTML = '<option value="">-- Memuat... --</option>';

            if (!subDeptId) {
                blockSelect.innerHTML = '<option value="">-- Pilih Blok --</option>';
                return;
            }

            fetch(`/sas/api/blocks/${subDeptId}`)
                .then(r => r.json())
                .then(data => {
                    let options = '<option value="">-- Pilih Blok --</option>';
                    data.forEach(b => {
                        const code = b.code ? ` (${b.code})` : '';
                        options += `<option value="${b.id}">${b.name}${code}</option>`;
                    });
                    blockSelect.innerHTML = options;
                });
        });

        // Load budget activities when block changes
        document.getElementById('block_id').addEventListener('change', function() {
            const blockId = this.value;
            const budgetSelect = document.getElementById('uspk_budget_activity_id');
            const budgetInfo = document.getElementById('budgetInfo');

            budgetSelect.innerHTML = '<option value="">-- Pilih Budget (opsional) --</option>';
            budgetInfo.style.display = 'none';

            if (!blockId) return;

            fetch(`/sas/api/budget-activities/${blockId}`)
                .then(r => r.json())
                .then(data => {
                    let options = '<option value="">-- Pilih Budget (opsional) --</option>';
                    data.forEach(ba => {
                        const jobName = ba.job ? ba.job.name : 'Unknown';
                        const remaining = ba.budget_amount - ba.used_amount;
                        options += `<option value="${ba.id}" data-remaining="${remaining}">${jobName} - Budget: Rp ${formatNumber(ba.budget_amount)} (Sisa: Rp ${formatNumber(remaining)})</option>`;
                    });
                    budgetSelect.innerHTML = options;
                });
        });

        // Show budget remaining info
        document.getElementById('uspk_budget_activity_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const budgetInfo = document.getElementById('budgetInfo');
            const remaining = selected.getAttribute('data-remaining');

            if (remaining) {
                document.getElementById('budgetRemaining').textContent = 'Rp ' + formatNumber(remaining);
                budgetInfo.style.display = 'block';
            } else {
                budgetInfo.style.display = 'none';
            }
        });

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }
    </script>
    @endpush
</x-serviceagreementsystem::layouts.master>
