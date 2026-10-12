<?php

namespace App\Http\Requests\Alerts;

use Illuminate\Foundation\Http\FormRequest;

class GetTeacherClassesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_id' => [
                'nullable',
                'integer',
                'exists:grades,id',
            ],
        ];
    }
}