<?php

namespace App\Http\Requests\Grades;

use Illuminate\Foundation\Http\FormRequest;

class GetStudentResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'academic_term_id' => [
                'required',
                'integer',
                'exists:academic_terms,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'العام الدراسي مطلوب.',
            'academic_year_id.integer' => 'العام الدراسي غير صالح.',
            'academic_year_id.exists' => 'العام الدراسي غير موجود.',

            'academic_term_id.required' => 'الفصل الدراسي مطلوب.',
            'academic_term_id.integer' => 'الفصل الدراسي غير صالح.',
            'academic_term_id.exists' => 'الفصل الدراسي غير موجود.',
        ];
    }
}