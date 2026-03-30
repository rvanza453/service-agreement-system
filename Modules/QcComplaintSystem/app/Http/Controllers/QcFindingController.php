<?php

namespace Modules\QcComplaintSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\ServiceAgreementSystem\Models\Block;
use Modules\ServiceAgreementSystem\Models\Department;
use Modules\ServiceAgreementSystem\Models\Site;
use Modules\ServiceAgreementSystem\Models\SubDepartment;
use Modules\QcComplaintSystem\Http\Requests\ApproveQcFindingCompletionRequest;
use Modules\QcComplaintSystem\Http\Requests\RejectQcFindingCompletionRequest;
use Modules\QcComplaintSystem\Http\Requests\StoreQcFindingRequest;
use Modules\QcComplaintSystem\Http\Requests\SubmitQcFindingCompletionRequest;
use Modules\QcComplaintSystem\Http\Requests\UpdateQcFindingRequest;
use Modules\QcComplaintSystem\Models\QcFinding;
use Modules\QcComplaintSystem\Services\QcFindingService;

class QcFindingController extends Controller
{
    public function __construct(
        protected QcFindingService $findingService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $isHoUser = $this->isHoUser($user);
        $forcedSiteId = $isHoUser ? null : (int) ($user?->site_id ?? 0);
        $statusProvided = $request->has('status');

        $siteFilter = $request->get('site_id');
        if ($forcedSiteId) {
            $siteFilter = $forcedSiteId;
        }

        $filters = [
            'status' => $request->get('status'),
            'urgency' => $request->get('urgency'),
            'kategori' => $request->get('kategori'),
            'sub_kategori' => $request->get('sub_kategori'),
            'site_id' => $siteFilter,
            'department_id' => $request->get('department_id'),
            'sub_department_id' => $request->get('sub_department_id'),
            'block_id' => $request->get('block_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'needs_resubmission' => $request->boolean('needs_resubmission') ? 1 : null,
            'keyword' => $request->get('keyword'),
            // Default listing shows only active findings until user explicitly picks a status filter.
            'exclude_closed' => $statusProvided ? null : 1,
        ];

        // Prevent cross-scope filtering by URL tampering for non-HO users.
        if ($forcedSiteId) {
            $filters['site_id'] = $forcedSiteId;
        }

        $findings = $this->findingService->paginate($filters);

        $picIds = $findings->getCollection()
            ->flatMap(function (QcFinding $finding) {
                $ids = array_map('intval', (array) ($finding->pic_user_ids ?? []));
                if (!empty($finding->pic_user_id)) {
                    $ids[] = (int) $finding->pic_user_id;
                }

                return $ids;
            })
            ->filter()
            ->unique()
            ->values();

        $picNameMap = User::query()
            ->whereIn('id', $picIds)
            ->pluck('name', 'id')
            ->toArray();

        $sitesQuery = Site::query()->orderBy('name');
        if ($forcedSiteId) {
            $sitesQuery->where('id', $forcedSiteId);
        }
        $sites = $sitesQuery->get(['id', 'name']);

        $departmentsQuery = Department::query()->orderBy('name');
        if ($filters['site_id']) {
            $departmentsQuery->where('site_id', (int) $filters['site_id']);
        }
        $departments = $departmentsQuery->get(['id', 'name', 'site_id']);

        $subDepartments = collect();
        if (!empty($filters['department_id'])) {
            $subDepartments = SubDepartment::query()
                ->where('department_id', (int) $filters['department_id'])
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        $blocks = collect();
        if (!empty($filters['sub_department_id'])) {
            $blocks = Block::query()
                ->where('sub_department_id', (int) $filters['sub_department_id'])
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return view('qccomplaintsystem::findings.index', [
            'findings' => $findings,
            'picNameMap' => $picNameMap,
            'filters' => $filters,
            'statusOptions' => QcFinding::statusOptions(),
            'urgencyOptions' => QcFinding::urgencyOptions(),
            'categoryOptions' => QcFinding::categoryOptions(),
            'sites' => $sites,
            'departments' => $departments,
            'subDepartments' => $subDepartments,
            'blocks' => $blocks,
            'isHoUser' => $isHoUser,
            'statusCounts' => $this->findingService->statusCounts($filters),
            'categoryBreakdown' => $this->findingService->categoryBreakdown($filters),
        ]);
    }

    public function summary(Request $request)
    {
        $user = auth()->user();
        $isHoUser = $this->isHoUser($user);
        $forcedSiteId = $isHoUser ? null : (int) ($user?->site_id ?? 0);

        $sitesQuery = Site::query()->orderBy('name');
        if ($forcedSiteId) {
            $sitesQuery->where('id', $forcedSiteId);
        }
        $sites = $sitesQuery->get(['id', 'name']);

        $selectedSiteId = $forcedSiteId ?: ($request->filled('site_id') ? (int) $request->get('site_id') : null);
        if ($selectedSiteId && !$sites->contains('id', $selectedSiteId)) {
            $selectedSiteId = $forcedSiteId ?: null;
        }

        $siteSummaryFilters = ['exclude_closed' => 1];
        if ($forcedSiteId) {
            $siteSummaryFilters['site_id'] = $forcedSiteId;
        }

        $departmentSummaryFilters = ['exclude_closed' => 1];
        if ($selectedSiteId) {
            $departmentSummaryFilters['site_id'] = $selectedSiteId;
        }

        $siteSummary = $this->findingService->summaryBySite($siteSummaryFilters);
        $departmentSummary = $this->findingService->summaryByDepartment($departmentSummaryFilters);

        return view('qccomplaintsystem::dashboard.summary', [
            'sites' => $sites,
            'isHoUser' => $isHoUser,
            'selectedSiteId' => $selectedSiteId,
            'siteSummary' => $siteSummary,
            'departmentSummary' => $departmentSummary,
            'totalActiveFindings' => (int) $siteSummary->sum('total_findings'),
            'totalOpenFindings' => (int) $siteSummary->sum('open_total'),
            'totalInReviewFindings' => (int) $siteSummary->sum('in_review_total'),
        ]);
    }

    public function create()
    {
        $authUser = auth()->user();

        return view('qccomplaintsystem::findings.create', [
            'authUser' => $authUser,
            'users' => $this->activeUsers(),
            'sites' => Site::query()->orderBy('name')->get(['id', 'name']),
            'departments' => $this->departmentsForUserScope($authUser),
            'subDepartments' => collect(),
            'blocks' => collect(),
            'urgencyOptions' => QcFinding::urgencyOptions(),
            'sourceOptions' => QcFinding::sourceOptions(),
            'categoryOptions' => QcFinding::categoryOptions(),
        ]);
    }

    public function store(StoreQcFindingRequest $request)
    {
        try {
            $payload = $this->normalizeSourceTypePayload($request->validated());
            $payload = $this->resolveLocationFromManualInput($payload);
            $finding = $this->findingService->create($payload, (int) auth()->id());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('qc.findings.show', $finding)
            ->with('success', 'Temuan QC berhasil dibuat.');
    }

    public function show(QcFinding $finding)
    {
        $finding = $this->findingService->findById($finding->id);
        $authId = (int) auth()->id();

        $picIds = collect(array_map('intval', (array) ($finding->pic_user_ids ?? [])));
        if (!empty($finding->pic_user_id)) {
            $picIds->push((int) $finding->pic_user_id);
        }

        $picNameMap = User::query()
            ->whereIn('id', $picIds->filter()->unique()->values())
            ->pluck('name', 'id')
            ->toArray();

        return view('qccomplaintsystem::findings.show', [
            'finding' => $finding,
            'picNameMap' => $picNameMap,
            'canSubmitCompletion' => $this->findingService->userCanSubmitCompletion($finding, $authId),
            'canApproveCompletion' => $this->findingService->userCanApproveFinding($finding, $authId),
            'currentApprovalStep' => $finding->currentPendingApprovalStep(),
        ]);
    }

    public function edit(QcFinding $finding)
    {
        $finding = $this->findingService->findById($finding->id);
        $authId  = (int) auth()->id();
        $authUser = auth()->user();

        // QC Officer may only edit findings they personally created
        if (!$authUser->hasModuleRole('qc', 'QC Admin')) {
            if ($finding->created_by !== $authId) {
                abort(403, 'Anda hanya dapat mengedit temuan yang Anda buat sendiri.');
            }
        }

        return view('qccomplaintsystem::findings.edit', [
            'finding' => $finding,
            'authUser' => $authUser,
            'users' => $this->activeUsers(),
            'sites' => Site::query()->orderBy('name')->get(['id', 'name']),
            'departments' => $this->departmentsForUserScope($authUser),
            'subDepartments' => SubDepartment::query()
                ->where('department_id', $finding->department_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'blocks' => Block::query()
                ->where('sub_department_id', $finding->sub_department_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'urgencyOptions' => QcFinding::urgencyOptions(),
            'sourceOptions' => QcFinding::sourceOptions(),
            'categoryOptions' => QcFinding::categoryOptions(),
        ]);
    }

    public function getSubDepartments(int $departmentId)
    {
        $query = SubDepartment::query()
            ->where('department_id', $departmentId);

        $authUser = auth()->user();
        if (!$this->isHoUser($authUser) && !empty($authUser?->site_id)) {
            $query->whereHas('department', function ($builder) use ($authUser) {
                $builder->where('site_id', (int) $authUser->site_id);
            });
        }

        $subDepartments = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($subDepartments);
    }

    public function getBlocks(int $subDepartmentId)
    {
        $query = Block::query()
            ->where('sub_department_id', $subDepartmentId);

        $authUser = auth()->user();
        if (!$this->isHoUser($authUser) && !empty($authUser?->site_id)) {
            $query->whereHas('subDepartment.department', function ($builder) use ($authUser) {
                $builder->where('site_id', (int) $authUser->site_id);
            });
        }

        $blocks = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($blocks);
    }

    public function update(UpdateQcFindingRequest $request, QcFinding $finding)
    {
        $authId   = (int) auth()->id();
        $authUser = auth()->user();

        // QC Officer may only update findings they personally created
        if (!$authUser->hasModuleRole('qc', 'QC Admin')) {
            if ($finding->created_by !== $authId) {
                abort(403, 'Anda hanya dapat mengedit temuan yang Anda buat sendiri.');
            }
        }

        try {
            $payload = $this->normalizeSourceTypePayload($request->validated());
            $payload = $this->resolveLocationFromManualInput($payload);
            $this->findingService->update($finding, $payload, (int) auth()->id());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('qc.findings.show', $finding)
            ->with('success', 'Temuan QC berhasil diperbarui.');
    }

    public function destroy(QcFinding $finding)
    {
        return redirect()->route('qc.findings.index')
            ->with('error', 'Fitur hapus tidak diaktifkan untuk menjaga histori audit temuan.');
    }

    public function submitCompletion(SubmitQcFindingCompletionRequest $request, QcFinding $finding)
    {
        try {
            $this->findingService->submitCompletion($finding, $request->validated(), (int) auth()->id());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('qc.findings.show', $finding)
            ->with('success', 'Bukti penyelesaian berhasil dikirim dan menunggu approval.');
    }

    public function approveCompletion(ApproveQcFindingCompletionRequest $request, QcFinding $finding)
    {
        try {
            $updated = $this->findingService->approveCompletion(
                $finding,
                (int) auth()->id(),
                $request->validated()['approval_note'] ?? null
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('qc.findings.show', $finding)
            ->with('success', $updated->status === QcFinding::STATUS_CLOSED
                ? 'Approval level final selesai. Temuan ditutup (closed).'
                : 'Approval level Anda berhasil. Menunggu approval level berikutnya.');
    }

    public function rejectCompletion(RejectQcFindingCompletionRequest $request, QcFinding $finding)
    {
        try {
            $this->findingService->rejectCompletion(
                $finding,
                (int) auth()->id(),
                $request->validated()['rejected_note']
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('qc.findings.show', $finding)
            ->with('success', 'Penyelesaian ditolak. PIC wajib melengkapi dan submit ulang bukti penyelesaian.');
    }

    private function activeUsers()
    {
        return User::query()->orderBy('name')->get(['id', 'name']);
    }

    private function departmentsForUserScope(?User $user)
    {
        $query = Department::query()->orderBy('name');

        if (!$this->isHoUser($user) && !empty($user?->site_id)) {
            $query->where('site_id', (int) $user->site_id);
        }

        return $query->get(['id', 'name', 'site_id']);
    }

    private function isHoUser(?User $user): bool
    {
        $siteName = strtolower((string) $user?->site?->name);

        return in_array($siteName, ['head office', 'ho'], true);
    }

    private function normalizeSourceTypePayload(array $payload): array
    {
        if (($payload['source_type'] ?? null) === 'other') {
            $payload['source_type'] = trim((string) ($payload['source_type_custom'] ?? ''));
        }

        unset($payload['source_type_custom']);

        return $payload;
    }

    private function resolveLocationFromManualInput(array $payload): array
    {
        $departmentId = (int) ($payload['department_id'] ?? 0);
        $subDepartmentName = trim((string) ($payload['sub_department_name'] ?? ''));
        $blockName = trim((string) ($payload['block_name'] ?? ''));

        $department = Department::query()->findOrFail($departmentId);

        $subDepartment = SubDepartment::query()->firstOrCreate(
            [
                'department_id' => $department->id,
                'name' => $subDepartmentName,
            ],
            [
                'coa' => null,
            ]
        );

        $block = Block::query()->firstOrCreate(
            [
                'sub_department_id' => $subDepartment->id,
                'name' => $blockName,
            ],
            [
                'code' => null,
                'is_active' => true,
            ]
        );

        $payload['sub_department_id'] = $subDepartment->id;
        $payload['block_id'] = $block->id;

        unset($payload['sub_department_name'], $payload['block_name']);

        return $payload;
    }
}
