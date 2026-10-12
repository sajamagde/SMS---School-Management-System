<?php

namespace App\Http\Requests\Announcements;

use App\Enums\AnnouncementTarget;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAnnouncementRequest extends FormRequest
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
        return [
            'title'             => 'required|string|max:255',
            'content'           => 'required|string',
            
            'target_audience'   => 'nullable|array',
            'target_audience.*' => ['integer', new Enum(AnnouncementTarget::class)],
            
            'grade_ids'          => 'nullable|array',
            'grade_ids.*'        => 'integer|exists:grades,id',
            
            'section_ids'        => 'nullable|array',
            'section_ids.*'      => 'integer|exists:sections,id',
            
            'is_pinned'         => 'nullable|boolean',
            'expires_at'        => 'nullable|date_format:Y-m-d H:i:s|after:now', 
        ];
    }
}
