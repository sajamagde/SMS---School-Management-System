<?php

namespace App\Http\Requests\Attendance;

use App\Enums\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendances' => [
                'required',
                'array',
                'min:1',
            ],

            'attendances.*.student_id' => [
                'required',
                'integer',
                'distinct',
                'exists:students,id',
            ],

            'attendances.*.status' => [
                'required',
                Rule::enum(AttendanceStatus::class),
            ],
        ];
    }
}