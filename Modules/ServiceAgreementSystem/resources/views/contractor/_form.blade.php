{{-- Shared form partial for Contractor create/edit --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label required">Nama PIC</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $contractor->name ?? '') }}" placeholder="Nama kontraktor / PIC" required>
    </div>
    <div class="form-group">
        <label class="form-label">Nama Perusahaan</label>
        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $contractor->company_name ?? '') }}" placeholder="PT / CV">
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">NPWP</label>
        <input type="text" name="npwp" class="form-control" value="{{ old('npwp', $contractor->npwp ?? '') }}" placeholder="Nomor NPWP">
    </div>
    <div class="form-group">
        <label class="form-label">No. Telepon</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $contractor->phone ?? '') }}" placeholder="08xxxx">
    </div>
    <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $contractor->email ?? '') }}" placeholder="email@contoh.com">
    </div>
</div>

<div class="form-group">
    <label class="form-label">Alamat</label>
    <textarea name="address" class="form-control" placeholder="Alamat lengkap">{{ old('address', $contractor->address ?? '') }}</textarea>
</div>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">Nama Bank</label>
        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $contractor->bank_name ?? '') }}" placeholder="BCA, BRI, dll">
    </div>
    <div class="form-group">
        <label class="form-label">Cabang Bank</label>
        <input type="text" name="bank_branch" class="form-control" value="{{ old('bank_branch', $contractor->bank_branch ?? '') }}" placeholder="Cabang bank">
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">No. Rekening</label>
        <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $contractor->account_number ?? '') }}" placeholder="Nomor rekening">
    </div>
    <div class="form-group">
        <label class="form-label">Nama Pemilik Rekening</label>
        <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name', $contractor->account_holder_name ?? '') }}" placeholder="Atas nama">
    </div>
</div>

@if(isset($contractor))
<div class="form-group">
    <label class="form-label">Status</label>
    <select name="is_active" class="form-control">
        <option value="1" {{ old('is_active', $contractor->is_active) ? 'selected' : '' }}>Aktif</option>
        <option value="0" {{ !old('is_active', $contractor->is_active) ? 'selected' : '' }}>Nonaktif</option>
    </select>
</div>
@endif
