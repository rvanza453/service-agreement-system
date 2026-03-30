<x-qccomplaintsystem::layouts.master :title="'Daftar Aduan Kebun QC'">
    <style>
        .finding-list-shell {
            display:grid;
            gap:14px;
            font-family: 'Manrope', sans-serif;
        }
        .finding-filter {
            border:1px solid #d5e4df; border-radius:16px; background:#fff; padding:14px;
            box-shadow:0 14px 26px rgba(15,23,42,0.06);
        }
        .finding-filter-grid {
            display:grid; grid-template-columns: repeat(6, minmax(0,1fr)); gap:8px;
        }
        .finding-filter .field label { font-size:11px; }
        .finding-filter .input, .finding-filter .select { font-size:13px; }
        .finding-headline {
            border:1px solid #d5e4df; border-radius:16px; padding:16px;
            background:
                radial-gradient(circle at 0% 0%, rgba(15,118,110,.16), transparent 45%),
                linear-gradient(180deg, #ffffff, #f7fcfa);
            display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;
            box-shadow:0 14px 24px rgba(15,23,42,0.06);
        }
        .dashboard-grid { display:grid; grid-template-columns: minmax(0, 1fr) 320px; gap:14px; align-items:start; }
        .dashboard-left { display:grid; gap:12px; }
        .summary-strip { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:10px; }
        .summary-box {
            border:1px solid #dbe7e1;
            background:linear-gradient(180deg, #ffffff, #f8fcfb);
            border-radius:13px;
            padding:11px;
            box-shadow:0 10px 18px rgba(15,23,42,0.05);
        }
        .summary-box .k { font-size:10px; text-transform:uppercase; color:#64748b; font-weight:700; }
        .summary-box .v { font-size:20px; font-weight:800; }
        .pie-panel {
            width:100%; flex-shrink:0; border:1px solid #dbe7e1; background:#fff; border-radius:14px; padding:14px;
            display:flex; flex-direction:column; justify-content:center;
            box-shadow:0 12px 20px rgba(15,23,42,0.05);
        }
        .pie-stack { display:grid; gap:12px; }
        .pie-panel .pie-title { font-size:10px; text-transform:uppercase; font-weight:700; color:#64748b; margin-bottom:6px; }
        #statusPieChart {
            margin:8px auto 10px;
            width:170px !important;
            height:170px !important;
            max-height:170px;
        }
        #categoryPieChart {
            margin:8px auto 10px;
            width:170px !important;
            height:170px !important;
            max-height:170px;
        }
        .table-wrap {
            border:1px solid #d5e4df;
            border-radius:16px;
            overflow-x:auto;
            overflow-y:hidden;
            background:#fff;
            -webkit-overflow-scrolling: touch;
            box-shadow:0 16px 28px rgba(15,23,42,0.06);
        }
        .finding-table { width:100%; border-collapse:collapse; min-width: 860px; }
        .finding-table th, .finding-table td { border-bottom:1px solid #e2e8f0; padding:10px; vertical-align:top; }
        .finding-table th { background:linear-gradient(180deg, #f8fcfb, #eef7f3); color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:.04em; }
        .finding-table td { font-size:13px; }
        .row-title { font-weight:800; margin-bottom:3px; font-size:15px; }
        .row-sub { font-size:11px; color:#64748b; }
        .flag-chip { display:inline-block; border-radius:999px; padding:3px 8px; font-size:11px; font-weight:800; }
        .flag-revision { background:#fee2e2; color:#b91c1c; }
        .flag-high { background:#fef3c7; color:#92400e; }
        .row-closed { background:linear-gradient(90deg, rgba(34,197,94,.18), rgba(34,197,94,.08)); }
        .row-alert-open { background:linear-gradient(90deg, rgba(245,158,11,.09), transparent 25%); }
        .row-alert-resub { background:linear-gradient(90deg, rgba(220,38,38,.09), transparent 30%); }
        @media (max-width: 1100px) {
            .finding-filter-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .dashboard-grid { grid-template-columns: 1fr; }
            .pie-panel { width: 100%; }
            .summary-strip { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .finding-table { min-width: 760px; }
        }
    </style>

    @php
        $countOpen = $findings->getCollection()->where('status', 'open')->count();
        $countReview = $findings->getCollection()->where('status', 'in_review')->count();
        $countResub = $findings->getCollection()->where('needs_resubmission', true)->count();
        $countHigh = $findings->getCollection()->whereIn('urgency', ['high', 'hight'])->count();
    @endphp

    <div class="finding-list-shell">
        <div class="finding-headline">
            <div>
                <div style="font-size:12px; color:#64748b; text-transform:uppercase; font-weight:700;">Monitoring</div>
                <h2 style="margin:4px 0 0; font-size:22px;">Daftar Temuan QC</h2>
                <div class="text-muted">Urutan tabel dibuat agar lebih cepat screening laporan kritis dan temuan yang perlu aksi ulang. Default daftar ini mengecualikan status Closed.</div>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('qc.dashboard') }}" class="btn"><i class="fas fa-chart-pie"></i> Dashboard Summary</a>
                @if(in_array(auth()->user()?->moduleRole('qc'), ['QC Admin', 'QC Officer']))
                    <a href="{{ route('qc.findings.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Lapor Temuan Baru</a>
                @endif
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-left">
                <div class="summary-strip">
                    <div class="summary-box"><div class="k">Open</div><div class="v">{{ $countOpen }}</div></div>
                    <div class="summary-box"><div class="k">In Review</div><div class="v">{{ $countReview }}</div></div>
                    <div class="summary-box"><div class="k">Perlu Submit Ulang</div><div class="v" style="color:#b91c1c;">{{ $countResub }}</div></div>
                    <div class="summary-box"><div class="k">Urgensi Tinggi</div><div class="v" style="color:#92400e;">{{ $countHigh }}</div></div>
                </div>

                <div class="finding-filter">
                    <form method="GET" action="{{ route('qc.findings.index') }}" class="finding-filter-grid">
                        <div class="field">
                            <label>Site</label>
                            <select name="site_id" id="site_id" class="select" @disabled(!$isHoUser)>
                                <option value="">Semua Site</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" @selected((string)($filters['site_id'] ?? '') === (string)$site->id)>{{ $site->name }}</option>
                                @endforeach
                            </select>
                            @if(!$isHoUser)
                                <input type="hidden" name="site_id" value="{{ $filters['site_id'] }}">
                            @endif
                        </div>
                        <div class="field">
                            <label>Department</label>
                            <select name="department_id" id="department_id" class="select">
                                <option value="">Semua Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @selected((string)($filters['department_id'] ?? '') === (string)$department->id)>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Afdeling</label>
                            <select name="sub_department_id" id="sub_department_id" class="select">
                                <option value="">Semua Afdeling</option>
                                @foreach($subDepartments as $subDepartment)
                                    <option value="{{ $subDepartment->id }}" @selected((string)($filters['sub_department_id'] ?? '') === (string)$subDepartment->id)>{{ $subDepartment->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Blok</label>
                            <select name="block_id" id="block_id" class="select">
                                <option value="">Semua Blok</option>
                                @foreach($blocks as $block)
                                    <option value="{{ $block->id }}" @selected((string)($filters['block_id'] ?? '') === (string)$block->id)>{{ $block->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Status</label>
                            <select name="status" class="select">
                                <option value="">Semua (termasuk Closed)</option>
                                @foreach($statusOptions as $status)
                                    <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ $status === 'in_review' ? 'IN REVIEW' : strtoupper($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Urgensi</label>
                            <select name="urgency" class="select">
                                <option value="">Semua</option>
                                @foreach($urgencyOptions as $urgency)
                                    <option value="{{ $urgency }}" @selected(($filters['urgency'] ?? null) === $urgency)>{{ \Modules\QcComplaintSystem\Models\QcFinding::urgencyLabel($urgency) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Tanggal Dari</label>
                            <input type="date" name="date_from" class="input" value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                        <div class="field">
                            <label>Tanggal Sampai</label>
                            <input type="date" name="date_to" class="input" value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                        <div class="field">
                            <label>Kategori</label>
                            <select name="kategori" id="kategori" class="select">
                                <option value="">Semua Kategori</option>
                                @foreach(\Modules\QcComplaintSystem\Models\QcFinding::categoryHierarchy() as $key => $cat)
                                    <option value="{{ $key }}" @selected(($filters['kategori'] ?? null) === $key)>{{ $cat['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Sub Kategori</label>
                            <select name="sub_kategori" id="sub_kategori" class="select">
                                <option value="">Semua Sub Kategori</option>
                            </select>
                        </div>
                        <div class="field" style="grid-column: span 2;">
                            <label>Kata Kunci</label>
                            <input type="text" class="input" name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="Cari nomor/judul/lokasi/pelapor">
                        </div>
                        <div class="field" style="display:flex; justify-content:flex-end; align-items:flex-end;">
                            <label style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; color:#475569; margin:0;">
                                <input type="checkbox" name="needs_resubmission" value="1" @checked(($filters['needs_resubmission'] ?? null) == 1)>
                                Hanya yang Perlu Submit Ulang
                            </label>
                        </div>
                        <div class="field" style="display:flex; justify-content:flex-end; align-items:flex-end; gap:4px;">
                            <a href="{{ route('qc.findings.index') }}" class="btn">Reset</a>
                        </div>
                        <div class="field" style="display:flex; justify-content:flex-end; align-items:flex-end; gap:8px;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Terapkan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="pie-stack">
                <div class="pie-panel">
                    <div class="pie-title">Progress Status (Filter Aktif)</div>
                    <canvas id="statusPieChart"></canvas>
                </div>
                <div class="pie-panel">
                    <div class="pie-title">Sebaran Kategori (Filter Aktif)</div>
                    <canvas id="categoryPieChart"></canvas>
                </div>
            </div>
        </div>

        <div class="table-wrap">
            <table class="finding-table">
                <thead>
                    <tr>
                        <th>No / Judul</th>
                        <th>Tanggal</th>
                        <th>Lokasi</th>
                        <th>Pelapor / PIC</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($findings as $finding)
                        @php
                            $statusLabel = $finding->status === 'in_review' ? 'IN REVIEW' : strtoupper($finding->status);
                            $approvalProgress = $finding->approvalSteps->count() > 0
                                ? $finding->approvalSteps->where('status', 'approved')->count() . '/' . $finding->approvalSteps->count()
                                : '-';
                            $isHighPriority = in_array(strtolower((string) $finding->urgency), ['high', 'hight'], true);
                            $resolvedPicIds = collect(array_map('intval', (array) ($finding->pic_user_ids ?? [])));
                            if (!empty($finding->pic_user_id)) {
                                $resolvedPicIds->push((int) $finding->pic_user_id);
                            }
                            $picNames = $resolvedPicIds
                                ->filter()
                                ->unique()
                                ->values()
                                ->map(fn ($id) => $picNameMap[$id] ?? null)
                                ->filter()
                                ->values();
                            $rowClass = $finding->status === 'closed'
                                ? 'row-closed'
                                : ($finding->needs_resubmission
                                    ? 'row-alert-resub'
                                    : ($finding->status === 'open' && $isHighPriority ? 'row-alert-open' : ''));
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td>
                                <div class="row-title">{{ $finding->finding_number }} - {{ $finding->title }}</div>
                                <div class="row-sub">{{ $finding->location ?: 'Tanpa detail lokasi tambahan' }}</div>
                                @if($finding->kategori)
                                    <span style="display:inline-block;margin-top:4px;border-radius:999px;background:#e0f2fe;color:#0369a1;font-size:11px;font-weight:700;padding:2px 8px;">
                                        @php
                                            $catHierarchy = \Modules\QcComplaintSystem\Models\QcFinding::categoryHierarchy();
                                            $catLabel = isset($catHierarchy[$finding->kategori]) ? $catHierarchy[$finding->kategori]['label'] : ucfirst($finding->kategori);
                                            $subLabel = ($finding->sub_kategori && isset($catHierarchy[$finding->kategori]['subs'][$finding->sub_kategori])) ? ' - ' . $catHierarchy[$finding->kategori]['subs'][$finding->sub_kategori]['label'] : '';
                                            $codeLabel = $finding->kategori_code ? ' ('.$finding->kategori_code.')' : '';
                                        @endphp
                                        {{ $catLabel }}{{ $subLabel }}{{ $codeLabel }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ optional($finding->finding_date)->format('d M Y') ?? '-' }}</td>
                            <td>
                                <div class="row-sub">{{ $finding->department?->site?->name ?? '-' }}</div>
                                <div>{{ $finding->department?->name ?? '-' }} / {{ $finding->subDepartment?->name ?? '-' }} / {{ $finding->block?->name ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="row-sub">Pelapor</div>
                                <div>{{ $finding->reporter?->name ?? $finding->reporter_name ?? '-' }}</div>
                                <div class="row-sub" style="margin-top:4px;">PIC: {{ $picNames->isNotEmpty() ? $picNames->join(', ') : '-' }}</div>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:6px;">
                                    <span class="badge badge-{{ $finding->status }}">{{ $statusLabel }}</span>
                                    <span class="badge badge-{{ $finding->urgency }}">{{ \Modules\QcComplaintSystem\Models\QcFinding::urgencyLabel($finding->urgency) }}</span>
                                </div>
                                @if($finding->needs_resubmission)
                                    <span class="flag-chip flag-revision">Perlu Submit Ulang</span>
                                @elseif($isHighPriority)
                                    <span class="flag-chip flag-high">Perlu Atensi Cepat</span>
                                @endif
                                <div class="row-sub" style="margin-top:6px;">Progress approval: {{ $approvalProgress }}</div>
                            </td>
                            <td>
                                <a href="{{ route('qc.findings.show', $finding) }}" class="btn btn-primary btn-sm">Buka Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted">Belum ada data temuan yang sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $findings->links() }}
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const departmentSelect = document.getElementById('department_id');
            const subDepartmentSelect = document.getElementById('sub_department_id');
            const blockSelect = document.getElementById('block_id');
            const kategoriSelect = document.getElementById('kategori');
            const subKategoriSelect = document.getElementById('sub_kategori');
            const categoryHierarchy = @json(\Modules\QcComplaintSystem\Models\QcFinding::categoryHierarchy());
            const selectedSubKategori = @json($filters['sub_kategori'] ?? '');

            function setOptions(select, items, placeholder) {
                if (!select) return;

                select.innerHTML = `<option value="">${placeholder}</option>`;
                items.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    select.appendChild(option);
                });
            }

            function loadSubDepartments(departmentId) {
                if (!departmentId) {
                    setOptions(subDepartmentSelect, [], 'Semua Afdeling');
                    setOptions(blockSelect, [], 'Semua Blok');
                    return;
                }

                fetch(`{{ url('qc/api/sub-departments') }}/${departmentId}`)
                    .then((response) => response.json())
                    .then((data) => {
                        setOptions(subDepartmentSelect, data, 'Semua Afdeling');
                        setOptions(blockSelect, [], 'Semua Blok');
                    });
            }

            function loadBlocks(subDepartmentId) {
                if (!subDepartmentId) {
                    setOptions(blockSelect, [], 'Semua Blok');
                    return;
                }

                fetch(`{{ url('qc/api/blocks') }}/${subDepartmentId}`)
                    .then((response) => response.json())
                    .then((data) => {
                        setOptions(blockSelect, data, 'Semua Blok');
                    });
            }

            if (departmentSelect) {
                departmentSelect.addEventListener('change', function () {
                    loadSubDepartments(this.value);
                });
            }

            if (subDepartmentSelect) {
                subDepartmentSelect.addEventListener('change', function () {
                    loadBlocks(this.value);
                });
            }

            function setSubKategoriOptions(kategoriKey, selectedValue) {
                if (!subKategoriSelect) return;

                subKategoriSelect.innerHTML = '<option value="">Semua Sub Kategori</option>';

                if (!kategoriKey || !categoryHierarchy[kategoriKey]) {
                    return;
                }

                const subs = categoryHierarchy[kategoriKey].subs || {};
                Object.keys(subs).forEach((subKey) => {
                    const option = document.createElement('option');
                    option.value = subKey;
                    option.textContent = subs[subKey].label;
                    if (selectedValue && selectedValue === subKey) {
                        option.selected = true;
                    }
                    subKategoriSelect.appendChild(option);
                });
            }

            if (kategoriSelect) {
                setSubKategoriOptions(kategoriSelect.value, selectedSubKategori);
                kategoriSelect.addEventListener('change', function () {
                    setSubKategoriOptions(this.value, '');
                });
            }

            const counts = @json($statusCounts);
            const ctx = document.getElementById('statusPieChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Open', 'In Review', 'Closed'],
                        datasets: [{
                            data: [counts.open, counts.in_review, counts.closed],
                            backgroundColor: ['#f59e0b', '#3b82f6', '#22c55e'],
                            borderWidth: 2,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        cutout: '62%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { font: { size: 11, weight: '700' }, padding: 10 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct = total ? Math.round(ctx.parsed / total * 100) : 0;
                                        return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            const categoryBreakdown = @json($categoryBreakdown);
            const categoryCtx = document.getElementById('categoryPieChart');
            if (categoryCtx) {
                const labels = categoryBreakdown.map((item) => item.label);
                const values = categoryBreakdown.map((item) => item.total);
                const palette = ['#0f766e', '#16a34a', '#ea580c', '#0284c7', '#db2777', '#7c3aed', '#ca8a04', '#334155'];

                new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            backgroundColor: labels.map((_, index) => palette[index % palette.length]),
                            borderWidth: 2,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        cutout: '62%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { font: { size: 11, weight: '700' }, padding: 10 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct = total ? Math.round(ctx.parsed / total * 100) : 0;
                                        return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                    },
                                    afterLabel: function(ctx) {
                                        const item = categoryBreakdown[ctx.dataIndex];
                                        const subs = Array.isArray(item?.subs) ? item.subs : [];
                                        if (!subs.length) {
                                            return ' Sub kategori: -';
                                        }

                                        const lines = [' Sub kategori:'];
                                        subs.forEach((sub) => {
                                            lines.push(` - ${sub.label}: ${sub.total}`);
                                        });

                                        return lines;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        })();
    </script>
    @endpush
</x-qccomplaintsystem::layouts.master>
