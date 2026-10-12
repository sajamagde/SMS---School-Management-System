<?php

namespace App\Http\Controllers\Grades;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grades\GetGradeBookRequest;
use App\Http\Requests\Grades\SaveGradesRequest;
use App\Services\Grades\SupervisorGradeService;
use Illuminate\Http\JsonResponse;

class GradeController extends Controller
{
    public function __construct(
        private readonly SupervisorGradeService $gradeService
    ) {}

    public function index(GetGradeBookRequest $request): JsonResponse
    {
        $data = $this->gradeService->getGradeBook(
            academicYearId: $request->integer('academic_year_id'),
            academicTermId: $request->integer('academic_term_id'),
            gradeId: $request->integer('grade_id'),
            sectionId: $request->integer('section_id'),
            subjectId: $request->integer('subject_id'),
        );

        return response()->json([
            'success' => true,
            'message' => 'تم جلب درجات الطلاب بنجاح.',
            'data' => $data,
        ]);
    }
    public function update(SaveGradesRequest $request): JsonResponse
{
    $this->gradeService->saveGrades(
        academicYearId: $request->integer('academic_year_id'),
        academicTermId: $request->integer('academic_term_id'),
        gradeId: $request->integer('grade_id'),
        sectionId: $request->integer('section_id'),
        subjectId: $request->integer('subject_id'),
        grades: $request->input('grades'),
    );

    return response()->json([
        'success' => true,
        'message' => 'تم حفظ الدرجات بنجاح.',
    ]);
}
}