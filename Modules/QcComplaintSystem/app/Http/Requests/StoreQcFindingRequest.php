<?php

namespace Modules\QcComplaintSystem\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\QcComplaintSystem\Models\QcFinding;

class StoreQcFindingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sourceOptions = array_merge(QcFinding::sourceOptions(), ['other']);

        return [
            'finding_date' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'finding_attachments' => 'nullable|array|max:10',
            'finding_attachments.*' => 'file|max:20480',
            'source_type' => 'required|in:' . implode(',', $sourceOptions),
            'source_type_custom' => 'nullable|required_if:source_type,other|string|max:100',
            'department_id' => 'required|exists:departments,id',
            'sub_department_name' => 'required|string|max:100',
            'block_name' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
            'urgency' => 'required|in:' . implode(',', QcFinding::urgencyOptions()),
            'pic_user_id' => 'nullable|exists:users,id',
            'pic_user_ids' => 'nullable|array',
            'pic_user_ids.*' => 'nullable|exists:users,id',
            'kategori' => 'nullable|in:' . implode(',', QcFinding::categoryOptions()),
            'sub_kategori' => 'nullable|string|max:100',
        ];
    }
}
