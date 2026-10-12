<?php

namespace App\Services\Academic;

use App\Models\Section;

class SectionService
{

    public function getSectionsByGrade($grade)
    {

        $query = Section::query();

        if ($grade->grade_id) {
            $query->where('grade_id', $grade->grade_id);
        }

        return $query->get()->map(function ($section) {
            return [
                'id' => $section->id,
                'name' => $section->name,
            ];
        });
    }
    public function updateStatus(int $sectionId, bool $isOpen): Section
    {
        $section = Section::query()->find($sectionId);

        if (!$section) {
            throw new \RuntimeException('الشعبة غير موجودة.');
        }

        if ($section->is_open === $isOpen) {
            return $section;
        }

        $section->update([
            'is_open' => $isOpen,
        ]);

        return $section->fresh();
    }
}
