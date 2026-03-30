<?php

namespace Modules\QcComplaintSystem\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\QcComplaintSystem\Models\QcFinding;

class QcFindingRepository
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = QcFinding::with([
            'reporter',
            'pic',
            'creator',
            'department',
            'department.site',
            'subDepartment',
            'block',
            'completionSubmitter',
            'completionApprover',
            'completionEvidences.uploader',
            'approvalSteps.approver',
            'approvalSteps.actor',
        ])->latest();

        $this->applyFilters($query, $filters);

        return $query->paginate(15)->withQueryString();
    }

    public function statusCounts(array $filters = []): array
    {
        $query = QcFinding::query();
        $this->applyFilters($query, $filters);

        $results = $query->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'open'      => (int) ($results[QcFinding::STATUS_OPEN] ?? 0),
            'in_review' => (int) ($results[QcFinding::STATUS_IN_REVIEW] ?? 0),
            'closed'    => (int) ($results[QcFinding::STATUS_CLOSED] ?? 0),
        ];
    }

    public function categoryBreakdown(array $filters = []): array
    {
        $query = QcFinding::query();
        $this->applyFilters($query, $filters);

        $rows = $query
            ->selectRaw('kategori, sub_kategori, count(*) as total')
            ->whereNotNull('kategori')
            ->groupBy('kategori', 'sub_kategori')
            ->get();

        $hierarchy = QcFinding::categoryHierarchy();
        $result = [];

        foreach ($rows as $row) {
            $categoryKey = (string) $row->kategori;
            if ($categoryKey === '') {
                continue;
            }

            if (!isset($result[$categoryKey])) {
                $result[$categoryKey] = [
                    'key' => $categoryKey,
                    'label' => $hierarchy[$categoryKey]['label'] ?? ucfirst($categoryKey),
                    'total' => 0,
                    'subs' => [],
                ];
            }

            $count = (int) $row->total;
            $result[$categoryKey]['total'] += $count;

            $subKey = (string) ($row->sub_kategori ?? '');
            if ($subKey === '') {
                continue;
            }

            $subLabel = $hierarchy[$categoryKey]['subs'][$subKey]['label']
                ?? ucfirst(str_replace('_', ' ', $subKey));

            $result[$categoryKey]['subs'][] = [
                'key' => $subKey,
                'label' => $subLabel,
                'total' => $count,
            ];
        }

        $items = array_values($result);
        usort($items, fn (array $a, array $b) => $b['total'] <=> $a['total']);

        return $items;
    }

    public function findById(int $id): QcFinding
    {
        return QcFinding::with([
            'reporter',
            'pic',
            'creator',
            'department',
            'subDepartment',
            'block',
            'completionSubmitter',
            'completionApprover',
            'completionEvidences.uploader',
            'approvalSteps.approver',
            'approvalSteps.actor',
        ])->findOrFail($id);
    }

    public function create(array $data): QcFinding
    {
        return QcFinding::create($data);
    }

    public function summaryBySite(array $filters = []): Collection
    {
        $query = QcFinding::query()
            ->join('departments', 'departments.id', '=', 'qc_findings.department_id')
            ->join('sites', 'sites.id', '=', 'departments.site_id');

        $this->applyFilters($query, $filters);

        return $query
            ->selectRaw(
                'sites.id as site_id, sites.name as site_name, count(*) as total_findings, '
                . 'sum(case when qc_findings.status = ? then 1 else 0 end) as open_total, '
                . 'sum(case when qc_findings.status = ? then 1 else 0 end) as in_review_total',
                [QcFinding::STATUS_OPEN, QcFinding::STATUS_IN_REVIEW]
            )
            ->groupBy('sites.id', 'sites.name')
            ->orderBy('sites.name')
            ->get();
    }

    public function summaryByDepartment(array $filters = []): Collection
    {
        $query = QcFinding::query()
            ->join('departments', 'departments.id', '=', 'qc_findings.department_id')
            ->join('sites', 'sites.id', '=', 'departments.site_id');

        $this->applyFilters($query, $filters);

        return $query
            ->selectRaw(
                'departments.id as department_id, departments.name as department_name, '
                . 'sites.id as site_id, sites.name as site_name, count(*) as total_findings, '
                . 'sum(case when qc_findings.status = ? then 1 else 0 end) as open_total, '
                . 'sum(case when qc_findings.status = ? then 1 else 0 end) as in_review_total',
                [QcFinding::STATUS_OPEN, QcFinding::STATUS_IN_REVIEW]
            )
            ->groupBy('departments.id', 'departments.name', 'sites.id', 'sites.name')
            ->orderBy('sites.name')
            ->orderBy('departments.name')
            ->get();
    }

    public function update(QcFinding $finding, array $data): QcFinding
    {
        $finding->update($data);

        return $finding->fresh([
            'reporter',
            'pic',
            'creator',
            'department',
            'subDepartment',
            'block',
            'completionSubmitter',
            'completionApprover',
            'completionEvidences.uploader',
            'approvalSteps.approver',
            'approvalSteps.actor',
        ]);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } elseif (!empty($filters['exclude_closed'])) {
            $query->where('status', '!=', QcFinding::STATUS_CLOSED);
        }

        if (!empty($filters['site_id'])) {
            $query->whereHas('department', function ($builder) use ($filters) {
                $builder->where('site_id', (int) $filters['site_id']);
            });
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', (int) $filters['department_id']);
        }

        if (!empty($filters['sub_department_id'])) {
            $query->where('sub_department_id', (int) $filters['sub_department_id']);
        }

        if (!empty($filters['block_id'])) {
            $query->where('block_id', (int) $filters['block_id']);
        }

        if (!empty($filters['urgency'])) {
            $query->where('urgency', $filters['urgency']);
        }

        if (!empty($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        if (!empty($filters['sub_kategori'])) {
            $query->where('sub_kategori', $filters['sub_kategori']);
        }

        if (!empty($filters['needs_resubmission'])) {
            $query->where('needs_resubmission', true);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('finding_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('finding_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($builder) use ($keyword) {
                $builder->where('finding_number', 'like', "%{$keyword}%")
                    ->orWhere('title', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%")
                    ->orWhere('reporter_name', 'like', "%{$keyword}%")
                    ->orWhereHas('department', fn ($q) => $q->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('subDepartment', fn ($q) => $q->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('block', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
            });
        }
    }
}
