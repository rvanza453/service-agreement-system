<?php

namespace Modules\ServiceAgreementSystem\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUspkSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'work_type' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'sub_department_id' => 'required|exists:sub_departments,id',
            'block_id' => 'required|exists:blocks,id',
            'job_id' => 'required|exists:jobs,id',
            'uspk_budget_activity_id' => 'nullable|exists:uspk_budget_activities,id',
            'estimated_value' => 'required|numeric|min:0',
            'estimated_duration' => 'nullable|integer|min:1',

            // Tender pembanding (1-3)
            'tenders' => 'required|array|min:1|max:3',
            'tenders.*.contractor_id' => 'required|exists:contractors,id',
            'tenders.*.tender_value' => 'required|numeric|min:0',
            'tenders.*.tender_duration' => 'nullable|integer|min:1',
            'tenders.*.description' => 'nullable|string',
            'tenders.*.is_selected' => 'boolean',
            'tenders.*.attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'tenders.required' => 'Minimal harus ada 1 tender pembanding.',
            'tenders.min' => 'Minimal harus ada 1 tender pembanding.',
            'tenders.max' => 'Maksimal 3 tender pembanding.',
            'tenders.*.contractor_id.required' => 'Kontraktor harus dipilih untuk setiap tender.',
            'tenders.*.tender_value.required' => 'Nilai tender harus diisi.',
        ];
    }
}
