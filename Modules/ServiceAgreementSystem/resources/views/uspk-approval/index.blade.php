<x-serviceagreementsystem::layouts.master :title="'Persetujuan Saya'">
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <h1 style="font-size: 24px; font-weight: 700; color: var(--text-primary);">Persetujuan Saya</h1>
            <p class="text-muted" style="font-size: 14px;">Daftar USPK yang memerlukan persetujuan dari Anda.</p>
        </div>
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
                        <th>Pengaju</th>
                        <th>Tanggal Ajuan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingUspks as $uspk)
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
                        <td>{{ $uspk->submitter->name ?? '-' }}</td>
                        <td>{{ $uspk->created_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <a href="{{ route('sas.uspk.show', $uspk) }}" class="btn btn-primary btn-sm" title="Tinjau & Proses">
                                <i class="fas fa-gavel"></i> Proses
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                <p class="mb-2" style="font-weight: 600; font-size: 16px; color: var(--text-primary);">Semua Selesai!</p>
                                <p class="text-muted">Tidak ada pengajuan USPK yang menunggu persetujuan Anda saat ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pendingUspks->hasPages())
        <div class="pagination-wrapper">
            {{ $pendingUspks->links() }}
        </div>
        @endif
    </div>
</x-serviceagreementsystem::layouts.master>
