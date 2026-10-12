<?php

namespace App\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_id' => 'sometimes|required|integer|exists:grades,id',
            'subject_id' => 'sometimes|required|integer|exists:subjects,id',
            'term' => 'sometimes|required|integer|in:1,2',
            'exam_date' => 'sometimes|required|date|after_or_equal:today',
            'start_time' => 'sometimes|required_with:end_time|date_format:H:i',
            'end_time' => 'sometimes|required_with:start_time|date_format:H:i|after:start_time',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['grade_id', 'subject_id', 'term', 'exam_date', 'start_time', 'end_time'])) {
                $validator->errors()->add('exam', 'يجب إرسال حقل واحد على الأقل لتعديل الامتحان.');
            }
        });
    }
}
