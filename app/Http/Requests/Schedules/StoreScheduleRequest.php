<?php

namespace App\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => 'required|integer|exists:sections,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'teacher_id' => 'required|integer|exists:teachers,id',
            'term' => 'required|integer|in:1,2',
            'day_of_week' => 'required|integer|between:0,4',
            'period_number' => 'required|integer|between:1,7',
        ];
    }
}
