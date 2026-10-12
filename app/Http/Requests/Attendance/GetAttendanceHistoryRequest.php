<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class GetAttendanceHistoryRequest extends FormRequest
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
           

            'from' => [
                'required',
                'date',
            ],

            'to' => [
                'required',
                'date',
                'after_or_equal:from',
            ],
        ];
    }
}
