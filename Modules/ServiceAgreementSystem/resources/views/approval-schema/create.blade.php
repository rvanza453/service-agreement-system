<x-serviceagreementsystem::layouts.master :title="'Tambah Skema Approval'">
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <h1 style="font-size: 24px; font-weight: 700; color: var(--text-primary);">Buat Skema Approval Baru</h1>
            <p class="text-muted" style="font-size: 14px;">Tentukan alur persetujuan untuk departemen terkait.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sas.approval-schemas.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" form="schemaForm" class="btn btn-primary">Simpan Skema</button>
        </div>
    </div>

    <form id="schemaForm" action="{{ route('sas.approval-schemas.store') }}" method="POST">
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-info-circle" style="margin-right: 8px; color: var(--accent);"></i> Informasi Skema</h2>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label required">Nama Skema</label>
                        <input type="text" name="name" id="name" class="form-control" required placeholder="Cth: Approval Regional Sumatera" value="{{ old('name') }}">
                    </div>
                    <div class="form-group" style="display: flex; align-items: flex-end; padding-bottom: 4px;">
                        <label class="d-flex align-center gap-2" style="cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" checked style="width: 18px; height: 18px; cursor: pointer;">
                            <span style="font-size: 14px; font-weight: 500;">Status Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label for="description" class="form-label">Deskripsi (Opsional)</label>
                    <textarea id="description" name="description" class="form-control" rows="3" placeholder="Berikan penjelasan singkat tentang kegunaan skema ini">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-building" style="margin-right: 8px; color: var(--accent);"></i> Pilih Departemen</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4" style="font-size: 13px;">Pilih departemen yang akan menggunakan alur persetujuan ini.</p>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
                    @foreach($departments as $dept)
                    <label class="d-flex align-center gap-2" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer; transition: var(--transition);">
                        <input type="checkbox" name="departments[]" value="{{ $dept->id }}" class="dept-checkbox" {{ is_array(old('departments')) && in_array($dept->id, old('departments')) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                        <span style="font-size: 13px; font-weight: 500; color: var(--text-secondary);">{{ $dept->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-between align-center">
                <h2 class="card-title"><i class="fas fa-layer-group" style="margin-right: 8px; color: var(--accent);"></i> Tahapan Persetujuan (Steps)</h2>
                <button type="button" id="add-step-btn" class="btn btn-secondary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Tahap
                </button>
            </div>
            <div class="card-body" style="background: #fafafa;">
                <div id="steps-container" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Steps will be dynamically injected here -->
                </div>
            </div>
        </div>
    </form>

    <!-- User Options Template for JS -->
    <script id="user-options-template" type="text/template">
        <option value="">-- Pilih Approver --</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->position ?? 'Staff' }})</option>
        @endforeach
    </script>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('steps-container');
            const addButton = document.getElementById('add-step-btn');
            const userOptions = document.getElementById('user-options-template').innerHTML;
            
            let stepCount = 0;

            function addStep(level = null, selectedUserId = null) {
                stepCount++;
                const currentLevel = level || stepCount;
                
                const stepHtml = `
                    <div class="step-item card" style="border-left: 4px solid var(--accent); margin-bottom: 0;" data-index="${stepCount-1}">
                        <div class="card-body" style="padding: 16px; display: flex; align-items: center; gap: 20px;">
                            <div style="flex: 0 0 40px; height: 40px; background: var(--accent-light); color: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px;">
                                ${currentLevel}
                            </div>
                            
                            <div style="flex: 1; display: grid; grid-template-columns: 100px 1fr; gap: 20px;">
                                <div class="form-group mb-0">
                                    <label class="form-label" style="font-size: 11px;">Level</label>
                                    <input type="number" name="steps[${stepCount-1}][level]" value="${currentLevel}" required min="1" class="form-control step-level" style="padding: 8px 12px;">
                                </div>
                                
                                <div class="form-group mb-0">
                                    <label class="form-label" style="font-size: 11px;">Approver (Orang Spesifik)</label>
                                    <select name="steps[${stepCount-1}][user_id]" required class="form-control user-select" style="padding: 8px 12px;">
                                        ${userOptions}
                                    </select>
                                </div>
                            </div>

                            <button type="button" class="btn btn-danger btn-sm remove-step-btn" style="flex: 0 0 36px; padding: 0; height: 36px; width: 36px; justify-content: center;" title="Hapus Tahap">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                container.insertAdjacentHTML('beforeend', stepHtml);
                
                if (selectedUserId) {
                    const selects = container.querySelectorAll('.user-select');
                    selects[selects.length - 1].value = selectedUserId;
                }
                
                updateIndices();
            }

            function updateIndices() {
                const items = container.querySelectorAll('.step-item');
                items.forEach((item, idx) => {
                    item.dataset.index = idx;
                    item.querySelector('.step-level').name = `steps[${idx}][level]`;
                    item.querySelector('.user-select').name = `steps[${idx}][user_id]`;
                });
            }

            container.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.remove-step-btn');
                if (removeBtn) {
                    removeBtn.closest('.step-item').remove();
                    updateIndices();
                }
            });

            addButton.addEventListener('click', () => addStep());

            if (container.children.length === 0) {
                addStep(1);
            }
        });
    </script>
    <style>
        .dept-checkbox:checked + span {
            color: var(--accent) !important;
        }
        .dept-checkbox:checked {
            accent-color: var(--accent);
        }
    </style>
    @endpush
</x-serviceagreementsystem::layouts.master>
