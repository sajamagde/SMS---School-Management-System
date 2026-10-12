<?php

namespace App\Http\Requests\Library;

use App\Enums\BookDistributionCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveBookDistributionRequest extends FormRequest
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

            'students' => [
                'required',
                'array',
                'min:1',
            ],

            'students.*.student_id' => [
                'required',
                'integer',
                'distinct',
                'exists:students,id',
            ],

            'students.*.condition' => [
                'required',
                Rule::enum(BookDistributionCondition::class),
            ],
        ];
    }
}