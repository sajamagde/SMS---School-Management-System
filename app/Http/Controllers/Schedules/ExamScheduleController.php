<?php

namespace App\Http\Controllers\Schedules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedules\StoreExamRequest;
use App\Http\Requests\Schedules\UpdateExamRequest;
use App\Http\Resources\ExamScheduleResource;
use App\Models\ExamSchedule;
use App\Models\Student;
use App\Services\Schedules\ExamScheduleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamScheduleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected ExamScheduleService $service) {}

    public function store(StoreExamRequest $request): JsonResponse
    {
        $this->authorize('create', ExamSchedule::class);

        $exam = $this->service->createExam($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الامتحان بنجاح.',
            'data' => new ExamScheduleResource($exam),
        ], 201);
    }

    public function update(UpdateExamRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', ExamSchedule::class);

        $exam = $this->service->updateExam($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الامتحان بنجاح.',
            'data' => new ExamScheduleResource($exam),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', ExamSchedule::class);

        $this->service->deleteExam($id);

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الامتحان بنجاح.',
        ]);
    }

    public function studentExams(Request $request): JsonResponse
    {
        $this->authorize('viewStudentExams', ExamSchedule::class);

        $validated = $request->validate([
            'term' => 'required|integer|in:1,2',
        ]);

        $student = $request->user()->student;
        if (! $student) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $exams = $this->service->getStudentExams($student->id, $validated['term']);

        return response()->json([
            'success' => true,
            'data' => ExamScheduleResource::collection($exams),
        ]);
    }

    public function parentExams(Request $request): JsonResponse
    {
        $this->authorize('viewParentExams', ExamSchedule::class);

        $validated = $request->validate([
            'term' => 'required|integer|in:1,2',
        ]);

        /** @var Student|null $selectedStudent */
        $selectedStudent = $request->attributes->get('selected_student');

        if (! $selectedStudent) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى تحديد الابن أولاً لمشاهدة جدول امتحاناته.',
            ], 400);
        }

        $exams = $this->service->getStudentExams($selectedStudent->id, $validated['term']);

        return response()->json([
            'success' => true,
            'data' => ExamScheduleResource::collection($exams),
        ]);
    }
}
