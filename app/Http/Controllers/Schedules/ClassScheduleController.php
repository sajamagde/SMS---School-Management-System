<?php

namespace App\Http\Controllers\Schedules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedules\StoreWeeklyScheduleRequest;
use App\Http\Resources\ClassScheduleResource;
use App\Models\ClassSchedule;
use App\Models\Student;
use App\Services\Schedules\ClassScheduleService;
use App\Services\Schedules\TeacherAvailabilityService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassScheduleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ClassScheduleService $service,
        protected TeacherAvailabilityService $availabilityService
    ) {}

    public function weeklyIndex(
        Request $request,
        int $sectionId
    ): JsonResponse {
        $this->authorize('viewAny', ClassSchedule::class);

        $validated = $request->validate([
            'term' => 'required|integer|in:1,2',
        ]);

        $schedule = $this->service->getSectionWeeklySchedule(
            $sectionId,
            $validated['term']
        );

        return response()->json([
            'success' => true,
            'data' => $schedule,
        ]);
    }
    public function sectionIndex(
        Request $request,
        int $sectionId
    ): JsonResponse {
        $this->authorize(
            'viewAny',
            ClassSchedule::class
        );

        $validated = $request->validate([
            'term' => 'required|integer|in:1,2',
        ]);

        $data = $this->service->getSectionScheduleForAdmin(
            $sectionId,
            $validated['term']
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
    public function saveWeeklySchedule(
        StoreWeeklyScheduleRequest $request,
        int $sectionId
    ): JsonResponse {
        $this->authorize('create', ClassSchedule::class);

        $validated = $request->validated();

        $schedule = $this->service->saveWeeklySchedule(
            $sectionId,
            $validated['term'],
            $validated['schedule']
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الجدول الدراسي الأسبوعي بنجاح.',
            'data' => $schedule,
        ]);
    }

    public function getTeachersBySection(
        int $sectionId
    ): JsonResponse {
        $this->authorize('create', ClassSchedule::class);

        return response()->json([
            'success' => true,
            'data' => $this->service->getTeachersBySection($sectionId),
        ]);
    }


    public function studentIndex(
        Request $request
    ): JsonResponse {
        $this->authorize(
            'viewStudentSchedule',
            ClassSchedule::class
        );

        $validated = $request->validate([
            'term' => 'required|integer|in:1,2',
            'day_of_week' => 'required|integer|between:0,4',
        ]);

        $student = $request->user()->student;

        if (! $student) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $schedule = $this->service->getStudentDailySchedule(
            $student->id,
            $validated['term'],
            $validated['day_of_week']
        );

        return response()->json([
            'success' => true,
            'data' => $schedule
        ]);
    }

    public function teacherIndex(
        Request $request
    ): JsonResponse {
        $this->authorize(
            'viewTeacherSchedule',
            ClassSchedule::class
        );

        $validated = $request->validate([
            'term' => 'required|integer|in:1,2',
            'day_of_week' => 'required|integer|between:0,4',
        ]);

        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $schedule = $this->service->getTeacherDailySchedule(
            $teacher->id,
            $validated['term'],
            $validated['day_of_week']
        );

        return response()->json([
            'success' => true,
            'data' => $schedule,
        ]);
    }

    public function parentIndex(
        Request $request
    ): JsonResponse {
        $this->authorize(
            'viewParentSchedule',
            ClassSchedule::class
        );

        $validated = $request->validate([
            'term' => 'required|integer|in:1,2',
            'day_of_week' => 'required|integer|between:0,4',
        ]);

        /** @var Student|null $selectedStudent */
        $selectedStudent = $request->attributes->get(
            'selected_student'
        );

        if (! $selectedStudent) {
            return response()->json([
                'success' => false,
                'message' =>
                'يرجى تحديد الابن أولاً لمشاهدة جدوله الدراسي.',
            ], 400);
        }

        $schedule = $this->service->getStudentDailySchedule(
            $selectedStudent->id,
            $validated['term'],
            $validated['day_of_week']
        );

        return response()->json([
            'success' => true,
            'data' => $schedule,
        ]);
    }
    public function getSectionTeacherAvailabilities(
        int $sectionId
    ): JsonResponse {
        $this->authorize('create', ClassSchedule::class);

        return response()->json([
            'success' => true,
            'data' => $this->availabilityService
                ->getSectionTeacherAvailabilities($sectionId),
        ]);
    }
    public function scheduleTimes(): JsonResponse
    {
        $this->authorize('viewAny', ClassSchedule::class);

        return response()->json([
            'success' => true,
            'data' => $this->service->getScheduleTimes(),
        ]);
    }
}
