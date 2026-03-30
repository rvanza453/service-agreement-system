<x-serviceagreementsystem::layouts.master :title="'Edit Konfigurasi Approval'">

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Edit Konfigurasi Approval</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('sas.approval-configs.update', $approval_config) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="department_id" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 500;">Department <span style="color: var(--text-muted); font-size: 12px; font-weight: normal;">(Kosongkan untuk Global)</span></label>
                <select name="department_id" id="department_id" class="form-control" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; transition: border-color 0.2s;">
                    <option value="">Semua Department (Global)</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $approval_config->department_id) == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }} (Site {{ $dept->site_id }})
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="role_name" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 500;">Role Approval <span style="color: var(--danger);">*</span></label>
                <select name="role_name" id="role_name" class="form-control" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; outline: none;">
                    <option value="">-- Pilih Role --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ old('role_name', $approval_config->role_name) == $role ? 'selected' : '' }}>
                            {{ $role }}
                        </option>
                    @endforeach
                </select>
                @error('role_name')
                    <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="level" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 500;">Level Approval <span style="color: var(--danger);">*</span></label>
                    <input type="number" name="level" id="level" class="form-control" value="{{ old('level', $approval_config->level) }}" min="1" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; outline: none;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">1 adalah level pertama yang akan approve.</div>
                    @error('level')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="min_value" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 500;">Minimum Value (Rp)</label>
                    <input type="number" name="min_value" id="min_value" class="form-control" value="{{ old('min_value', $approval_config->min_value) }}" min="0" step="0.01" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; outline: none;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Kosongkan jika selalu diperlukan di level ini.</div>
                    @error('min_value')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 12px; justify-content: flex-end;">
                <a href="{{ route('sas.approval-configs.index') }}" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Perbarui Konfigurasi
                </button>
            </div>
        </form>
    </div>
</div>
</x-serviceagreementsystem::layouts.master>
