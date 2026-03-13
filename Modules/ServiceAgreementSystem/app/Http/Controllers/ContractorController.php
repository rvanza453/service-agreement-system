<?php

namespace Modules\ServiceAgreementSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ServiceAgreementSystem\Http\Requests\StoreContractorRequest;
use Modules\ServiceAgreementSystem\Models\Contractor;
use Modules\ServiceAgreementSystem\Services\ContractorService;

class ContractorController extends Controller
{
    public function __construct(
        protected ContractorService $contractorService
    ) {}

    public function index()
    {
        $contractors = $this->contractorService->getAll();
        return view('serviceagreementsystem::contractor.index', compact('contractors'));
    }

    public function create()
    {
        return view('serviceagreementsystem::contractor.create');
    }

    public function store(StoreContractorRequest $request)
    {
        $this->contractorService->store($request->validated());
        return redirect()->route('sas.contractors.index')->with('success', 'Kontraktor berhasil ditambahkan.');
    }

    public function show(Contractor $contractor)
    {
        return view('serviceagreementsystem::contractor.show', compact('contractor'));
    }

    public function edit(Contractor $contractor)
    {
        return view('serviceagreementsystem::contractor.edit', compact('contractor'));
    }

    public function update(StoreContractorRequest $request, Contractor $contractor)
    {
        $this->contractorService->update($contractor, $request->validated());
        return redirect()->route('sas.contractors.index')->with('success', 'Kontraktor berhasil diperbarui.');
    }

    public function destroy(Contractor $contractor)
    {
        $this->contractorService->delete($contractor);
        return redirect()->route('sas.contractors.index')->with('success', 'Kontraktor berhasil dihapus.');
    }
}
