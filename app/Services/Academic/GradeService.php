<?php

namespace App\Services\Academic;

use App\Models\Grade;

class GradeService
{
    public function getAllGrades()
    {
        return Grade::query()
            ->with([
                'sections:id,grade_id,name,is_open'
            ])
            ->get([
                'id',
                'name',
            ]);
    }
}
