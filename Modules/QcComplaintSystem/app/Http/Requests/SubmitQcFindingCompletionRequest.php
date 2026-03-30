<?php

namespace Modules\QcComplaintSystem\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQcFindingCompletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'completion_note' => 'required|string',
            'completion_files' => 'required|array|min:1|max:10',
            'completion_files.*' => 'required|file|max:10240',
        ];
    }
}
