<?php

namespace Modules\QcComplaintSystem\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveQcFindingCompletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approval_note' => 'nullable|string',
        ];
    }
}
