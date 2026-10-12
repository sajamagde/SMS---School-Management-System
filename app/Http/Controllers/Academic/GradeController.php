<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Services\Academic\GradeService;
use Illuminate\Http\Request;

class GradeController extends Controller
{
     public function __construct(private GradeService $gradeService) {}

    public function getGrades()
    {
        $grades = $this->gradeService->getAllGrades();

        return response()->json([
            'message' => 'تم جلب الصفوف بنجاح',
            'data'    => $grades
        ], 200);
    }
}
