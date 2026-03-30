<?php

namespace Modules\QcComplaintSystem\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectQcFindingCompletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejected_note' => 'required|string',
        ];
    }
}
