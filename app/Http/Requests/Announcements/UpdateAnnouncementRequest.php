<?php

namespace App\Http\Requests\Announcements;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\AnnouncementTarget;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'title'             => 'sometimes|string|max:255',
            'content'           => 'sometimes|string',
            
            'target_audience'   => 'sometimes|array',
            'target_audience.*' => ['integer', Rule::in(array_column(AnnouncementTarget::cases(), 'value'))],
            
            'is_pinned'         => 'sometimes|boolean',
            'expires_at'        => 'sometimes|date|after:now',
            
            'grade_ids'         => 'sometimes|array',
            'grade_ids.*'       => 'integer|exists:grades,id',
            
            'section_ids'       => 'sometimes|array',
            'section_ids.*'     => 'integer|exists:sections,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'العنوان طويل جداً، الحد الأقصى 255 حرفاً.',
            'expires_at.after' => 'تاريخ انتهاء الإعلان يجب أن يكون في المستقبل.',
            'grade_ids.*.exists' => 'أحد الصفوف المحددة غير موجود في النظام.',
            'section_ids.*.exists' => 'إحدى الشعب المحددة غير موجودة في النظام.',
            'target_audience.*.in' => 'فئة الجمهور المستهدف المحددة غير صالحة.',
        ];
    }
}