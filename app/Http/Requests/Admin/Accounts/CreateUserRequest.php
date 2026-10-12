<?php

namespace App\Http\Requests\Admin\Accounts;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = $this->baseRules();

        return match ($this->input('role_name')) {

            'student' => array_merge(
                $rules,
                $this->studentRules()
            ),

            'teacher' => array_merge(
                $rules,
                $this->teacherRules()
            ),

            'supervisor' => array_merge(
                $rules,
                $this->supervisorRules()
            ),

            'parent' => array_merge(
                $rules,
                $this->parentRules()
            ),

            default => $rules
        };
    }

    private function baseRules(): array
    {
        return [

            'role_name' => [
                'required',
                'string',
                Rule::exists('roles', 'name')
            ],

            'first_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}]+(?:\s[\p{L}]+)*$/u'
            ],

            'last_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}]+(?:\s[\p{L}]+)*$/u'
            ],

            'phone' => [
                'required',
                'string',
                'regex:/^09[0-9]{8}$/',
                Rule::unique('users', 'phone')
            ],

            'password' => [
                'required',
                'string',
                'min:6',
            ],
        ];
    }
    // Parent rules
    private function parentRules(): array
    {
        return [
            'address' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    // Student rules
    private function studentRules(): array
    {
        return [

            'mother_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}]+(?:\s[\p{L}]+)*$/u'
            ],

            'birth_date' => [
                'required',
                'date',
            ],

            'address' => [
                'required',
                'string'
            ],

            'parent_id' => [
                'nullable',
                Rule::exists('parents', 'id')
            ],

            'grade_id' => [
                'required',
                Rule::exists('grades', 'id'),
            ],
        ];
    }

    //Teacher rules
    private function teacherRules(): array
    {
        return [

            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id')
            ],

            'section_ids.*' => [
                'integer',
                Rule::exists('sections', 'id')
            ],
        ];
    }
    //Supervisor rules
    private function supervisorRules(): array
    {
        return [

            'grade_id' => [
                'required',
                Rule::exists('grades', 'id')
            ],
        ];
    }


    public function messages(): array
    {
        return [

            'phone.regex' =>
            'Phone number must start with 09 and contain exactly 10 digits.',

            'birth_date.before_or_equal' =>
            'Student age must be at least 11 years old.',

            'first_name.regex' =>
            'First name must contain letters only.',

            'last_name.regex' =>
            'Last name must contain letters only.',

            'mother_name.regex' =>
            'Mother name must contain letters only.',
        ];
    }
}
