<?php

namespace Modules\ServiceAgreementSystem\Services;

use Modules\ServiceAgreementSystem\Models\Contractor;
use Modules\ServiceAgreementSystem\Repositories\ContractorRepository;

class ContractorService
{
    public function __construct(
        protected ContractorRepository $contractorRepository
    ) {}

    public function getAll()
    {
        return $this->contractorRepository->getAll();
    }

    public function getActive()
    {
        return $this->contractorRepository->getActive();
    }

    public function findById(int $id): Contractor
    {
        return $this->contractorRepository->findById($id);
    }

    public function store(array $data): Contractor
    {
        return $this->contractorRepository->create($data);
    }

    public function update(Contractor $contractor, array $data): Contractor
    {
        return $this->contractorRepository->update($contractor, $data);
    }

    public function delete(Contractor $contractor): void
    {
        $this->contractorRepository->delete($contractor);
    }
}
