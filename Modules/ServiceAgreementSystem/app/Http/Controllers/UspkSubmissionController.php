<?php

namespace Modules\ServiceAgreementSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ServiceAgreementSystem\Http\Requests\StoreUspkSubmissionRequest;
use Modules\ServiceAgreementSystem\Models\UspkSubmission;
use Modules\ServiceAgreementSystem\Services\ContractorService;
use Modules\ServiceAgreementSystem\Services\UspkSubmissionService;
use Modules\ServiceAgreementSystem\Models\Department;
use Modules\ServiceAgreementSystem\Models\SubDepartment;
use Modules\ServiceAgreementSystem\Models\Block;
use Modules\ServiceAgreementSystem\Models\Job;
use Modules\ServiceAgreementSystem\Models\UspkBudgetActivity;

class UspkSubmissionController extends Controller
{
    public function __construct(
        protected UspkSubmissionService $uspkService,
        protected ContractorService $contractorService
    ) {}

    public function index(Request $request)
    {
        $status = $request->get('status');
        $submissions = $this->uspkService->getAll($status);
        return view('serviceagreementsystem::uspk.index', compact('submissions', 'status'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $contractors = $this->contractorService->getActive();
        $jobs = Job::orderBy('name')->get();

        return view('serviceagreementsystem::uspk.create', compact('departments', 'contractors', 'jobs'));
    }

    public function store(StoreUspkSubmissionRequest $request)
    {
        $data = $request->except('tenders');
        $tenders = $this->processTenders($request);

        $this->uspkService->store($data, $tenders);

        return redirect()->route('sas.uspk.index')->with('success', 'USPK berhasil dibuat sebagai draft.');
    }

    public function show(UspkSubmission $uspk)
    {
        $uspk = $this->uspkService->findById($uspk->id);
        return view('serviceagreementsystem::uspk.show', compact('uspk'));
    }

    public function edit(UspkSubmission $uspk)
    {
        if (!$uspk->isEditable()) {
            return redirect()->route('sas.uspk.show', $uspk)->with('error', 'USPK tidak dapat diedit.');
        }

        $uspk->load(['tenders.contractor']);
        $departments = Department::orderBy('name')->get();
        $contractors = $this->contractorService->getActive();
        $jobs = Job::orderBy('name')->get();
        $subDepartments = SubDepartment::where('department_id', $uspk->department_id)->get();
        $blocks = Block::where('sub_department_id', $uspk->sub_department_id)->get();

        return view('serviceagreementsystem::uspk.edit', compact('uspk', 'departments', 'contractors', 'jobs', 'subDepartments', 'blocks'));
    }

    public function update(StoreUspkSubmissionRequest $request, UspkSubmission $uspk)
    {
        if (!$uspk->isEditable()) {
            return redirect()->route('sas.uspk.show', $uspk)->with('error', 'USPK tidak dapat diedit.');
        }

        $data = $request->except('tenders');
        $tenders = $this->processTenders($request);

        $this->uspkService->update($uspk, $data, $tenders);

        return redirect()->route('sas.uspk.show', $uspk)->with('success', 'USPK berhasil diperbarui.');
    }

    public function destroy(UspkSubmission $uspk)
    {
        $this->uspkService->delete($uspk);
        return redirect()->route('sas.uspk.index')->with('success', 'USPK berhasil dihapus.');
    }

    /**
     * Submit USPK (draft → submitted)
     */
    public function submit(UspkSubmission $uspk)
    {
        try {
            $this->uspkService->submit($uspk);
            return redirect()->route('sas.uspk.show', $uspk)->with('success', 'USPK berhasil disubmit.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * API: Get sub departments by department
     */
    public function getSubDepartments(int $departmentId)
    {
        $subDepartments = SubDepartment::where('department_id', $departmentId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($subDepartments);
    }

    /**
     * API: Get blocks by sub department
     */
    public function getBlocks(int $subDepartmentId)
    {
        $blocks = Block::where('sub_department_id', $subDepartmentId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($blocks);
    }

    /**
     * API: Get budget activities by block
     */
    public function getBudgetActivities(int $blockId)
    {
        $budgets = UspkBudgetActivity::where('block_id', $blockId)
            ->where('is_active', true)
            ->where('year', now()->year)
            ->with('job')
            ->get();

        return response()->json($budgets);
    }

    /**
     * Process tender data from request including file uploads
     */
    protected function processTenders(Request $request): array
    {
        $tenders = [];
        $tenderData = $request->input('tenders', []);

        foreach ($tenderData as $index => $tender) {
            if (empty($tender['contractor_id'])) {
                continue;
            }

            $tenderEntry = [
                'contractor_id' => $tender['contractor_id'],
                'tender_value' => $tender['tender_value'] ?? 0,
                'tender_duration' => $tender['tender_duration'] ?? null,
                'description' => $tender['description'] ?? null,
                'is_selected' => isset($tender['is_selected']) ? (bool) $tender['is_selected'] : false,
            ];

            // Handle file upload
            if ($request->hasFile("tenders.{$index}.attachment")) {
                $file = $request->file("tenders.{$index}.attachment");
                $tenderEntry['attachment_path'] = $file->store('uspk/tenders', 'public');
            }

            $tenders[] = $tenderEntry;
        }

        return $tenders;
    }
}
