<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'start_date' => [
                'sometimes',
                'required',
                'date',
            ],

            'end_date' => [
                'sometimes',
                'required',
                'date',
                'after:start_date',
            ],

            'term_1' => [
                'sometimes',
                'required',
                'array',
            ],

            'term_1.name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'term_1.start_date' => [
                'sometimes',
                'required',
                'date',
            ],

            'term_1.end_date' => [
                'sometimes',
                'required',
                'date',
                'after:term_1.start_date',
            ],

            'term_2' => [
                'sometimes',
                'required',
                'array',
            ],

            'term_2.name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'term_2.start_date' => [
                'sometimes',
                'required',
                'date',
            ],

            'term_2.end_date' => [
                'sometimes',
                'required',
                'date',
                'after:term_2.start_date',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny([
                'name',
                'start_date',
                'end_date',
                'term_1',
                'term_2',
            ])) {
                $validator->errors()->add(
                    'academic_year',
                    'يجب إرسال حقل واحد على الأقل لتعديل السنة الدراسية.'
                );
            }
        });
    }
}