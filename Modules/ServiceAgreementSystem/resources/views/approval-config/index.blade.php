<x-serviceagreementsystem::layouts.master :title="'Daftar Konfigurasi Approval'">

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Konfigurasi Approval USPK</h2>
        <a href="{{ route('sas.approval-configs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Konfigurasi
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div style="background: var(--success-bg); color: var(--success); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                        <th style="padding: 12px 16px; font-weight: 600;">Department</th>
                        <th style="padding: 12px 16px; font-weight: 600;">Role Approval</th>
                        <th style="padding: 12px 16px; font-weight: 600;">Level</th>
                        <th style="padding: 12px 16px; font-weight: 600;">Minimum Value (Rp)</th>
                        <th style="padding: 12px 16px; font-weight: 600; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($configs as $config)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 12px 16px;">
                                @if($config->department)
                                    {{ $config->department->name }}
                                @else
                                    <span style="background: var(--warning-bg); color: var(--warning); padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Global (Semua Dept)</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px;">{{ $config->role_name }}</td>
                            <td style="padding: 12px 16px;">Level {{ $config->level }}</td>
                            <td style="padding: 12px 16px;">
                                {{ $config->min_value ? number_format($config->min_value, 0, ',', '.') : '-' }}
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <a href="{{ route('sas.approval-configs.edit', $config) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('sas.approval-configs.destroy', $config) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus konfigurasi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 24px; text-align: center; color: var(--text-muted);">
                                Belum ada konfigurasi approval.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-serviceagreementsystem::layouts.master>
