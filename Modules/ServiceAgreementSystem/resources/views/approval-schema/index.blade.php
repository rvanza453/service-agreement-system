<x-serviceagreementsystem::layouts.master :title="'Daftar Skema Approval'">
    <div class="card">
        <div class="card-header">
            <div>
                <h1 class="card-title">Daftar Skema Approval</h1>
                <p class="text-muted" style="font-size: 13px; margin-top: 4px;">Manajemen rute persetujuan berlapis untuk pemrosesan USPK berdasarkan departemen.</p>
            </div>
            <a href="{{ route('sas.approval-schemas.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Skema
            </a>
        </div>
        
        <div class="card-body" style="padding: 0;">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Skema</th>
                            <th>Departemen</th>
                            <th>Jumlah Tahap</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schemas as $schema)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--text-primary);">{{ $schema->name }}</div>
                                @if($schema->description)
                                    <div class="text-muted" style="font-size: 11px; margin-top: 2px;">{{ \Str::limit($schema->description, 50) }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($schema->departments->take(3) as $dept)
                                        <span class="badge" style="background: var(--accent-light); color: var(--accent);">{{ $dept->name }}</span>
                                    @endforeach
                                    @if($schema->departments->count() > 3)
                                        <span class="badge" style="background: var(--bg-primary); color: var(--text-muted);">+{{ $schema->departments->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-draft">{{ $schema->steps->count() }} Tahap</span>
                            </td>
                            <td>
                                @if($schema->is_active)
                                    <span class="badge badge-approved">Aktif</span>
                                @else
                                    <span class="badge badge-rejected">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="d-flex justify-between" style="justify-content: flex-end; gap: 12px;">
                                    <a href="{{ route('sas.approval-schemas.edit', $schema) }}" class="btn btn-secondary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('sas.approval-schemas.destroy', $schema) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus skema ini?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-folder-open"></i>
                                    <p class="mb-2">Belum ada Skema Approval</p>
                                    <p class="text-muted">Buat skema baru untuk mengatur rute persetujuan USPK.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-serviceagreementsystem::layouts.master>
