<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Services\Academic\AcademicDistributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AcademicDistributionController extends Controller
{
    public function __construct(
        private AcademicDistributionService $distributionService
    ) {}

    public function autoDistribute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'minimum' => ['required', 'integer', 'min:1'],
            'maximum' => ['required', 'integer', 'gte:minimum'],
        ]);

        try {
            $academicYearId = 1;

            $result = $this->distributionService->autoDistribute(
                $academicYearId,
                $validated['minimum'],
                $validated['maximum']
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'distributed' => $result['distributed'],
                ],
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Student auto distribution failed.', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء توزيع الطلاب.',
            ], 500);
        }
    }
    public function getStudents(): JsonResponse
    {
        $academicYearId = 1;

        $students = $this->distributionService
            ->getStudents($academicYearId);

        return response()->json([
            'success' => true,
            'data' => $students,
        ]);
    }
    public function moveStudent(
    Request $request,
    int $studentId
): JsonResponse {
    $validated = $request->validate([
        'section_id' => [
            'required',
            'integer',
            'exists:sections,id',
        ],
    ]);

    try {
        $academicYearId = 1;

        $result = $this->distributionService->moveStudent(
            $studentId,
            $validated['section_id'],
            $academicYearId
        );

        $enrollment = $result['enrollment'];

        return response()->json([
            'success' => true,
            'message' => $result['changed']
                ? 'تم نقل الطالب بنجاح.'
                : 'لم يحدث أي تغيير.',
            'data' => [
                'student_id' => $enrollment->student_id,
                'section' => $enrollment->section
                    ? [
                        'id' => $enrollment->section->id,
                        'name' => $enrollment->section->name,
                    ]
                    : null,
            ],
        ]);
    } catch (RuntimeException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 422);
    }
}
}
