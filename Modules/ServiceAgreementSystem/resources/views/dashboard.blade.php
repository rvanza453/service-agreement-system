<x-serviceagreementsystem::layouts.master :title="'Dashboard'">
    {{-- Stats Grid --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-value">{{ $stats['total_uspk'] }}</div>
            <div class="stat-label">Total USPK</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;">
                <i class="fas fa-pencil-alt"></i>
            </div>
            <div class="stat-value">{{ $stats['draft'] }}</div>
            <div class="stat-label">Draft</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div class="stat-value">{{ $stats['submitted'] }}</div>
            <div class="stat-label">Submitted</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                <i class="fas fa-search"></i>
            </div>
            <div class="stat-value">{{ $stats['in_review'] }}</div>
            <div class="stat-label">In Review</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value">{{ $stats['approved'] }}</div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-value">{{ $stats['rejected'] }}</div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>

    {{-- Recent USPK --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">USPK Terbaru</div>
            <a href="{{ route('sas.uspk.index') }}" class="btn btn-secondary btn-sm">
                Lihat Semua <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No. USPK</th>
                        <th>Judul</th>
                        <th>Department</th>
                        <th>Pengaju</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentUspk as $uspk)
                    <tr>
                        <td style="font-weight: 600; color: var(--text-primary);">{{ $uspk->uspk_number }}</td>
                        <td>{{ $uspk->title }}</td>
                        <td>{{ $uspk->department->name ?? '-' }}</td>
                        <td>{{ $uspk->submitter->name ?? '-' }}</td>
                        <td><span class="badge badge-{{ $uspk->status }}">{{ ucfirst(str_replace('_', ' ', $uspk->status)) }}</span></td>
                        <td>{{ $uspk->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>Belum ada pengajuan USPK.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-serviceagreementsystem::layouts.master>
