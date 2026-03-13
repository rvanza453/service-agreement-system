<?php

namespace Modules\ServiceAgreementSystem\Repositories;

use Modules\ServiceAgreementSystem\Models\Contractor;

class ContractorRepository
{
    public function getAll()
    {
        return Contractor::latest()->paginate(15);
    }

    public function getActive()
    {
        return Contractor::where('is_active', true)->orderBy('name')->get();
    }

    public function findById(int $id): Contractor
    {
        return Contractor::findOrFail($id);
    }

    public function create(array $data): Contractor
    {
        return Contractor::create($data);
    }

    public function update(Contractor $contractor, array $data): Contractor
    {
        $contractor->update($data);
        return $contractor->fresh();
    }

    public function delete(Contractor $contractor): void
    {
        $contractor->delete();
    }
}
