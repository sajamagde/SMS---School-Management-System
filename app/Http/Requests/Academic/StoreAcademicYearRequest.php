<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:academic_years,name',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],

            'term_1' => [
                'required',
                'array',
            ],

            'term_1.name' => [
                'required',
                'string',
                'max:255',
            ],

            'term_1.start_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],

            'term_1.end_date' => [
                'required',
                'date',
                'after:term_1.start_date',
                'before_or_equal:end_date',
            ],

            'term_2' => [
                'required',
                'array',
            ],

            'term_2.name' => [
                'required',
                'string',
                'max:255',
            ],

            'term_2.start_date' => [
                'required',
                'date',
                'after:term_1.end_date',
                'before_or_equal:end_date',
            ],

            'term_2.end_date' => [
                'required',
                'date',
                'after:term_2.start_date',
                'before_or_equal:end_date',
            ],
        ];
    }
}