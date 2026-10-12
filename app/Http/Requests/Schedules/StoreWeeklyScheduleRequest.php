<?php

namespace App\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreWeeklyScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'term' => [
                'required',
                'integer',
                'in:1,2',
            ],

            'schedule' => [
                'required',
                'array',
            ],

            'schedule.*.day_of_week' => [
                'required',
                'integer',
                'between:0,4',
            ],

            'schedule.*.period_number' => [
                'required',
                'integer',
                'between:1,7',
            ],

            'schedule.*.subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],

            'schedule.*.teacher_id' => [
                'required',
                'integer',
                'exists:teachers,id',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $schedule = $this->input('schedule', []);

            $slots = [];

            foreach ($schedule as $index => $lesson) {
                if (
                    isset($lesson['day_of_week']) &&
                    isset($lesson['period_number'])
                ) {
                    $slot = $lesson['day_of_week']
                        . '-'
                        . $lesson['period_number'];

                    if (isset($slots[$slot])) {
                        $validator->errors()->add(
                            "schedule.$index.period_number",
                            'لا يمكن إضافة أكثر من حصة في نفس اليوم والحصة.'
                        );
                    }

                    $slots[$slot] = true;
                }
            }
        });
    }
}
