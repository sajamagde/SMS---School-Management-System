<?php

namespace App\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'availabilities' => 'required|array',
            'availabilities.*.day_of_week' => 'required|integer|between:0,4',
            'availabilities.*.period_number' => 'required|integer|between:1,7',
            'availabilities.*.availability_status' => 'sometimes|integer|in:0,1,2',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $seen = [];

            foreach ($this->input('availabilities', []) as $index => $slot) {
                if (! isset($slot['day_of_week'], $slot['period_number'])) {
                    continue;
                }

                $key = $slot['day_of_week'].':'.$slot['period_number'];
                if (isset($seen[$key])) {
                    $validator->errors()->add(
                        "availabilities.$index.period_number",
                        'تم إدخال نفس اليوم والحصة أكثر من مرة.'
                    );
                }

                $seen[$key] = true;
            }
        });
    }
}
