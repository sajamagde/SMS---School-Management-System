<?php

namespace App\Http\Controllers\Grades;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grades\GetStudentResultsRequest;
use App\Http\Resources\Grades\StudentResultResource;
use App\Services\Grades\StudentResultService;
use Illuminate\Http\JsonResponse;

class ResultController extends Controller
{
    public function __construct(
        private readonly StudentResultService $resultService
    ) {}

    public function indexStudent(GetStudentResultsRequest $request): JsonResponse
    {
        $student = $request->user()->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'حساب الطالب غير موجود.',
            ], 404);
        }

        $result = $this->resultService->getResults(
            student: $student,
            academicYearId: $request->integer('academic_year_id'),
            academicTermId: $request->integer('academic_term_id'),
        );

        return response()->json([
            'success' => true,
            'message' => 'تم جلب النتائج بنجاح.',
            'data' => new StudentResultResource($result),
        ]);
    }
    public function indexParent(GetStudentResultsRequest $request): JsonResponse
    {
        $parent = $request->user()->parentProfile;

        if (!$parent) {
            return response()->json([
                'success' => false,
                'message' => 'حساب ولي الأمر غير موجود.',
            ], 404);
        }

        $student = $parent->students()
            ->where('id', $request->header('X-Student-Id'))
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'الطالب غير موجود أو غير مرتبط بولي الأمر.',
            ], 404);
        }

        $result = $this->resultService->getResults(
            student: $student,
            academicYearId: $request->integer('academic_year_id'),
            academicTermId: $request->integer('academic_term_id'),
        );

        return response()->json([
            'success' => true,
            'message' => 'تم جلب النتائج بنجاح.',
            'data' => new StudentResultResource($result),
        ]);
    }
}
