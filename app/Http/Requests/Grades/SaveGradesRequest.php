<?php

namespace App\Http\Requests\Grades;

use Illuminate\Foundation\Http\FormRequest;

class SaveGradesRequest extends FormRequest
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

            'academic_term_id' => [
                'required',
                'integer',
                'exists:academic_terms,id',
            ],

            'grade_id' => [
                'required',
                'integer',
                'exists:grades,id',
            ],

            'section_id' => [
                'required',
                'integer',
                'exists:sections,id',
            ],

            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],

            'grades' => [
                'required',
                'array',
                'min:1',
            ],

            'grades.*.student_id' => [
                'required',
                'integer',
                'exists:students,id',
                'distinct',
            ],

            'grades.*.score' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'العام الدراسي مطلوب.',
            'academic_year_id.integer' => 'العام الدراسي غير صالح.',
            'academic_year_id.exists' => 'العام الدراسي غير موجود.',

            'academic_term_id.required' => 'الفصل الدراسي مطلوب.',
            'academic_term_id.integer' => 'الفصل الدراسي غير صالح.',
            'academic_term_id.exists' => 'الفصل الدراسي غير موجود.',

            'grade_id.required' => 'الصف مطلوب.',
            'grade_id.integer' => 'الصف غير صالح.',
            'grade_id.exists' => 'الصف غير موجود.',

            'section_id.required' => 'الشعبة مطلوبة.',
            'section_id.integer' => 'الشعبة غير صالحة.',
            'section_id.exists' => 'الشعبة غير موجودة.',

            'subject_id.required' => 'المادة مطلوبة.',
            'subject_id.integer' => 'المادة غير صالحة.',
            'subject_id.exists' => 'المادة غير موجودة.',

            'grades.required' => 'العلامات مطلوبة.',
            'grades.array' => 'صيغة العلامات غير صحيحة.',
            'grades.min' => 'يجب إرسال علامة طالب واحد على الأقل.',

            'grades.*.student_id.required' => 'معرف الطالب مطلوب.',
            'grades.*.student_id.integer' => 'معرف الطالب غير صالح.',
            'grades.*.student_id.exists' => 'الطالب غير موجود.',
            'grades.*.student_id.distinct' => 'لا يمكن تكرار الطالب ضمن نفس الطلب.',

            'grades.*.score.numeric' => 'العلامة يجب أن تكون رقمًا.',
            'grades.*.score.min' => 'العلامة لا يمكن أن تكون أقل من 0.',
            'grades.*.score.max' => 'العلامة لا يمكن أن تتجاوز 100.',
        ];
    }
}