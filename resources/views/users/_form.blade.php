@csrf

<div class="card-body" style="display:grid; gap:16px;">
    <div class="field">
        <label>Nama Lengkap</label>
        <input type="text" name="name" class="input" value="{{ old('name', $user->name) }}" required>
    </div>

    <div class="field">
        <label>Email</label>
        <input type="email" name="email" class="input" value="{{ old('email', $user->email) }}" required>
    </div>

    <div class="field">
        <label>Password {{ $isEdit ? '(Kosongkan jika tidak diubah)' : '' }}</label>
        <input type="password" name="password" class="input" {{ $isEdit ? '' : 'required' }}>
    </div>

    <div class="field">
        <label>Global Role (Spatie, opsional)</label>
        <select name="global_role" class="select">
            <option value="">Tanpa Role Global</option>
            @foreach($spatieRoles as $role)
                <option value="{{ $role }}" @selected(old('global_role', $selectedGlobalRole) === $role)>{{ $role }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label>Role Per Module</label>
        <div style="display:grid; gap:12px;">
            @foreach($moduleRoleConfig as $moduleKey => $moduleConfig)
                <div style="display:grid; gap:6px; border:1px solid #e5e7eb; border-radius:10px; padding:10px 12px;">
                    <strong>{{ $moduleConfig['label'] ?? strtoupper($moduleKey) }}</strong>
                    <select name="module_roles[{{ $moduleKey }}]" class="select">
                        <option value="">Tidak di-set</option>
                        @foreach(($moduleConfig['roles'] ?? []) as $moduleRole)
                            <option value="{{ $moduleRole }}" @selected(old('module_roles.' . $moduleKey, $selectedModuleRoles[$moduleKey] ?? null) === $moduleRole)>
                                {{ $moduleRole }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
        <small style="color:#64748b;">Role per module ini untuk kebutuhan ERP multi-module, jadi 1 user bisa beda role di setiap modul.</small>
    </div>
</div>

<div class="card-footer" style="display:flex; gap:10px; justify-content:flex-end; padding-top:14px;">
    <a href="{{ route('accounts.index') }}" class="btn">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Simpan Perubahan' : 'Buat Akun' }}</button>
</div>
