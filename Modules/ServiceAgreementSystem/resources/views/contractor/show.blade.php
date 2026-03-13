<x-serviceagreementsystem::layouts.master :title="'Detail Kontraktor'">
    @push('actions')
        <a href="{{ route('sas.contractors.edit', $contractor) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('sas.contractors.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    @endpush

    <div class="card">
        <div class="card-header">
            <div class="card-title">{{ $contractor->name }}</div>
            <span class="badge {{ $contractor->is_active ? 'badge-approved' : 'badge-rejected' }}">
                {{ $contractor->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group mb-0">
                    <div class="text-muted" style="font-size: 12px; margin-bottom: 4px;">Perusahaan</div>
                    <div style="font-weight: 600;">{{ $contractor->company_name ?? '-' }}</div>
                </div>
                <div class="form-group mb-0">
                    <div class="text-muted" style="font-size: 12px; margin-bottom: 4px;">NPWP</div>
                    <div style="font-weight: 600;">{{ $contractor->npwp ?? '-' }}</div>
                </div>
                <div class="form-group mb-0">
                    <div class="text-muted" style="font-size: 12px; margin-bottom: 4px;">Telepon</div>
                    <div style="font-weight: 600;">{{ $contractor->phone ?? '-' }}</div>
                </div>
                <div class="form-group mb-0">
                    <div class="text-muted" style="font-size: 12px; margin-bottom: 4px;">Email</div>
                    <div style="font-weight: 600;">{{ $contractor->email ?? '-' }}</div>
                </div>
            </div>
            <hr style="border-color: var(--border-color); margin: 20px 0;">
            <div class="form-group mb-0">
                <div class="text-muted" style="font-size: 12px; margin-bottom: 4px;">Alamat</div>
                <div>{{ $contractor->address ?? '-' }}</div>
            </div>
            <hr style="border-color: var(--border-color); margin: 20px 0;">
            <div class="form-row">
                <div class="form-group mb-0">
                    <div class="text-muted" style="font-size: 12px; margin-bottom: 4px;">Bank</div>
                    <div style="font-weight: 600;">{{ $contractor->bank_name ?? '-' }} {{ $contractor->bank_branch ? '- ' . $contractor->bank_branch : '' }}</div>
                </div>
                <div class="form-group mb-0">
                    <div class="text-muted" style="font-size: 12px; margin-bottom: 4px;">No. Rekening</div>
                    <div style="font-weight: 600;">{{ $contractor->account_number ?? '-' }}</div>
                </div>
                <div class="form-group mb-0">
                    <div class="text-muted" style="font-size: 12px; margin-bottom: 4px;">Atas Nama</div>
                    <div style="font-weight: 600;">{{ $contractor->account_holder_name ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</x-serviceagreementsystem::layouts.master>
