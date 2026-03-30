<x-qccomplaintsystem::layouts.master :title="'Pengaturan Approval QC'">
    <div class="card">
        <div class="card-header">Konfigurasi Approver</div>
        <div class="card-body">
            <form method="POST" action="{{ route('qc.approval-config.update') }}">
                @csrf
                @method('PUT')

                <div class="field">
                    <label>Approver Manager QC / Penanggung Jawab Approval</label>
                    @php
                        $selectedApprovers = old('approver_user_ids', $config?->approver_user_ids ?? []);
                        if (empty($selectedApprovers)) {
                            $selectedApprovers = [''];
                        }
                    @endphp

                    <div id="approval-steps" style="display:grid; gap:10px;">
                        @foreach($selectedApprovers as $idx => $selectedApprover)
                            <div class="approval-row" style="display:grid; grid-template-columns:110px 1fr auto; gap:10px; align-items:center;">
                                <div class="text-muted" style="font-size:12px;">Level {{ $idx + 1 }}</div>
                                <select name="approver_user_ids[]" class="select" required>
                                    <option value="">Pilih User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected((string) $selectedApprover === (string) $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-sm btn-danger remove-step"><i class="fas fa-trash"></i></button>
                            </div>
                        @endforeach
                    </div>

                    <div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
                        <button type="button" id="add-step" class="btn btn-sm"><i class="fas fa-plus"></i> Tambah Level</button>
                    </div>
                    <small>Urutan baris menentukan level approval (Level 1 diproses paling awal).</small>
                </div>

                <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Simpan Konfigurasi</button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const stepsRoot = document.getElementById('approval-steps');
                const addButton = document.getElementById('add-step');

                function userOptionsHtml() {
                    return `
                        <option value="">Pilih User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    `;
                }

                function renumber() {
                    const rows = stepsRoot.querySelectorAll('.approval-row');
                    rows.forEach((row, index) => {
                        const label = row.querySelector('.text-muted');
                        if (label) {
                            label.textContent = `Level ${index + 1}`;
                        }
                    });
                }

                function addRow() {
                    const row = document.createElement('div');
                    row.className = 'approval-row';
                    row.style.display = 'grid';
                    row.style.gridTemplateColumns = '110px 1fr auto';
                    row.style.gap = '10px';
                    row.style.alignItems = 'center';
                    row.innerHTML = `
                        <div class="text-muted" style="font-size:12px;">Level 0</div>
                        <select name="approver_user_ids[]" class="select" required>${userOptionsHtml()}</select>
                        <button type="button" class="btn btn-sm btn-danger remove-step"><i class="fas fa-trash"></i></button>
                    `;
                    stepsRoot.appendChild(row);
                    renumber();
                }

                addButton.addEventListener('click', addRow);

                stepsRoot.addEventListener('click', function (event) {
                    const removeButton = event.target.closest('.remove-step');
                    if (!removeButton) {
                        return;
                    }

                    const rows = stepsRoot.querySelectorAll('.approval-row');
                    if (rows.length <= 1) {
                        return;
                    }

                    removeButton.closest('.approval-row')?.remove();
                    renumber();
                });

                renumber();
            });
        </script>
    @endpush
</x-qccomplaintsystem::layouts.master>
