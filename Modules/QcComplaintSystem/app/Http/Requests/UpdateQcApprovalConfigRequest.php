<?php

namespace Modules\QcComplaintSystem\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQcApprovalConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approver_user_ids' => 'required|array|min:1',
            'approver_user_ids.*' => 'required|distinct|exists:users,id',
        ];
    }
}
