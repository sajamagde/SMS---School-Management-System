<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Services\Academic\SubjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function __construct(
        private SubjectService $subjectService
    ) {}

    public function getSubjects(): JsonResponse
    {
        $subjects = $this->subjectService->getAllSubjects();

        return response()->json([
            'message' => 'Subjects fetched successfully',
            'data' => $subjects,
        ], 200);
    }

    public function createOrAttach(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade_ids' => ['required', 'array', 'min:1'],
            'grade_ids.*' => ['integer', 'exists:grades,id'],
        ]);

        $subject = $this->subjectService
            ->createOrAttachSubject($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ المادة وربطها بالصفوف بنجاح.',
            'data' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'has_exam' => $subject->has_exam,
                'grades' => $subject->sectionSubjects
                    ->pluck('section.grade')
                    ->filter()
                    ->unique('id')
                    ->values()
                    ->map(fn ($grade) => [
                        'id' => $grade->id,
                        'name' => $grade->name,
                    ]),
            ],
        ], 200);
    }
}