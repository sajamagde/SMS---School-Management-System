<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class LibraryAcademicYearRequest extends FormRequest
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
        ];
    }
}