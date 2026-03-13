<x-serviceagreementsystem::layouts.master :title="'Data Kontraktor'">
    @push('actions')
        <a href="{{ route('sas.contractors.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Kontraktor
        </a>
    @endpush

    <div class="card">
        <div class="card-header">
            <div class="card-title">Daftar Kontraktor</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Perusahaan</th>
                        <th>NPWP</th>
                        <th>Telepon</th>
                        <th>Bank</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contractors as $contractor)
                    <tr>
                        <td style="font-weight: 600; color: var(--text-primary);">{{ $contractor->name }}</td>
                        <td>{{ $contractor->company_name ?? '-' }}</td>
                        <td>{{ $contractor->npwp ?? '-' }}</td>
                        <td>{{ $contractor->phone ?? '-' }}</td>
                        <td>{{ $contractor->bank_name ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $contractor->is_active ? 'badge-approved' : 'badge-rejected' }}">
                                {{ $contractor->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="d-flex gap-2" style="justify-content: flex-end;">
                                <a href="{{ route('sas.contractors.show', $contractor) }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sas.contractors.edit', $contractor) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('sas.contractors.destroy', $contractor) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-hard-hat"></i>
                                <p>Belum ada data kontraktor.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contractors->hasPages())
        <div class="pagination-wrapper">
            {{ $contractors->links() }}
        </div>
        @endif
    </div>
</x-serviceagreementsystem::layouts.master>
