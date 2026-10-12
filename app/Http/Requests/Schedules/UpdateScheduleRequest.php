<?php

namespace App\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => 'sometimes|required|integer|exists:sections,id',
            'subject_id' => 'sometimes|required|integer|exists:subjects,id',
            'teacher_id' => 'sometimes|required|integer|exists:teachers,id',
            'term' => 'sometimes|required|integer|in:1,2',
            'day_of_week' => 'sometimes|required|integer|between:0,4',
            'period_number' => 'sometimes|required|integer|between:1,7',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['section_id', 'subject_id', 'teacher_id', 'term', 'day_of_week', 'period_number'])) {
                $validator->errors()->add('schedule', 'يجب إرسال حقل واحد على الأقل لتعديل الحصة.');
            }
        });
    }
}
