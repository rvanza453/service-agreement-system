<x-serviceagreementsystem::layouts.master :title="'Pengajuan USPK'">
    @push('actions')
        <a href="{{ route('sas.uspk.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Buat USPK Baru
        </a>
    @endpush

    {{-- Filter Tabs --}}
    <div class="d-flex gap-2 flex-wrap mb-4">
        <a href="{{ route('sas.uspk.index') }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-secondary' }}">Semua</a>
        <a href="{{ route('sas.uspk.index', ['status' => 'draft']) }}" class="btn btn-sm {{ $status === 'draft' ? 'btn-primary' : 'btn-secondary' }}">Draft</a>
        <a href="{{ route('sas.uspk.index', ['status' => 'submitted']) }}" class="btn btn-sm {{ $status === 'submitted' ? 'btn-primary' : 'btn-secondary' }}">Submitted</a>
        <a href="{{ route('sas.uspk.index', ['status' => 'in_review']) }}" class="btn btn-sm {{ $status === 'in_review' ? 'btn-primary' : 'btn-secondary' }}">In Review</a>
        <a href="{{ route('sas.uspk.index', ['status' => 'approved']) }}" class="btn btn-sm {{ $status === 'approved' ? 'btn-primary' : 'btn-secondary' }}">Approved</a>
        <a href="{{ route('sas.uspk.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ $status === 'rejected' ? 'btn-primary' : 'btn-secondary' }}">Rejected</a>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No. USPK</th>
                        <th>Judul Pekerjaan</th>
                        <th>Department</th>
                        <th>Blok</th>
                        <th>Estimasi Nilai</th>
                        <th>Tender</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $uspk)
                    <tr>
                        <td style="font-weight: 600; color: var(--text-primary);">
                            <a href="{{ route('sas.uspk.show', $uspk) }}" style="color: var(--accent); text-decoration: none;">
                                {{ $uspk->uspk_number }}
                            </a>
                        </td>
                        <td>{{ Str::limit($uspk->title, 40) }}</td>
                        <td>{{ $uspk->department->name ?? '-' }}</td>
                        <td>{{ $uspk->block->name ?? '-' }}</td>
                        <td style="font-weight: 600;">Rp {{ number_format($uspk->estimated_value, 0, ',', '.') }}</td>
                        <td>
                            <span style="color: var(--accent);">{{ $uspk->tenders->count() }} kontraktor</span>
                        </td>
                        <td><span class="badge badge-{{ $uspk->status }}">{{ ucfirst(str_replace('_', ' ', $uspk->status)) }}</span></td>
                        <td>{{ $uspk->created_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <div class="d-flex gap-2" style="justify-content: flex-end;">
                                <a href="{{ route('sas.uspk.show', $uspk) }}" class="btn btn-secondary btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($uspk->isEditable())
                                <a href="{{ route('sas.uspk.edit', $uspk) }}" class="btn btn-primary btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('sas.uspk.destroy', $uspk) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus USPK ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fas fa-file-signature"></i>
                                <p>Belum ada pengajuan USPK.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($submissions->hasPages())
        <div class="pagination-wrapper">
            {{ $submissions->links() }}
        </div>
        @endif
    </div>
</x-serviceagreementsystem::layouts.master>
