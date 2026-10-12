<?php

namespace App\Http\Requests\Alerts;

use App\Enums\StudentAlertType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class GetStudentAlertsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'nullable',
                new Enum(StudentAlertType::class),
            ],
        ];
    }
}