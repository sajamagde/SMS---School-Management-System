<?php

namespace App\Http\Controllers\Alerts;

use App\Enums\StudentAlertType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Alerts\GetStudentAlertsRequest;
use App\Http\Requests\Alerts\CreateStudentAlertRequest;
use App\Http\Requests\Alerts\GetTeacherClassesRequest;
use App\Http\Resources\Alerts\TeacherClassResource;
use App\Http\Resources\Alerts\ParentStudentAlertDetailsResource;
use App\Http\Resources\Alerts\ParentStudentAlertResource;
use App\Http\Resources\Alerts\TeacherStudentDetailsResource;
use App\Http\Resources\Alerts\TeacherStudentResource;
use App\Models\Student;
use App\Models\StudentAlert;
use App\Services\Alerts\StudentAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentAlertController extends Controller
{
    public function __construct(
        private readonly StudentAlertService $studentAlertService
    ) {}



    public function classes(
        GetTeacherClassesRequest $request
    ): JsonResponse {
        $teacher = $request->user()->teacher;

        $classes = $this->studentAlertService->getTeacherClasses(
            $teacher,
            $request->integer('grade_id') ?: null
        );

        return response()->json([
            'success' => true,
            'data' => TeacherClassResource::collection(
                collect($classes)
            ),
        ]);
    }

    public function students(
        Request $request,
        int $sectionId
    ): JsonResponse {
        $teacher = $request->user()->teacher;

        $this->authorizeForUser(
            $teacher,
            'viewSection',
            [StudentAlert::class, $sectionId]
        );
        $students = $this->studentAlertService->getSectionStudents(
            $teacher,
            $sectionId
        );

        return response()->json([
            'success' => true,
            'data' => TeacherStudentResource::collection($students),
        ]);
    }

    public function showStudent(
        Request $request,
        Student $student
    ): JsonResponse {
        $teacher = $request->user()->teacher;

        $this->authorizeForUser(
            $teacher,
            'viewStudent',
            [StudentAlert::class, $student]
        );

        $student = $this->studentAlertService->getStudentDetails(
            $teacher,
            $student->id
        );

        return response()->json([
            'success' => true,
            'data' => new TeacherStudentDetailsResource($student),
        ]);
    }

    public function store(
    CreateStudentAlertRequest $request,
    Student $student
): JsonResponse {
    $teacher = $request->user()->teacher;

    $this->authorizeForUser(
        $teacher,
        'create',
        [StudentAlert::class, $student]
    );

    $alert = $this->studentAlertService->createAlert(
        $teacher,
        $student->id,
        $request->validated()
    );

    return response()->json([
        'success' => true,
        'message' => 'تم إرسال الإنذار بنجاح.',
        'data' => [
            'id' => $alert->id,
        ],
    ], 201);
}
    

    public function index(
        GetStudentAlertsRequest $request
    ): JsonResponse {
        $student = $request->attributes->get('selected_student');

        $result = $this->studentAlertService->getParentAlerts(
            $student,
            $request->enum('type', StudentAlertType::class)
        );

        return response()->json([
            'success' => true,
            'data' => [
                'counts' => $result['counts'],
                'alerts' => ParentStudentAlertResource::collection(
                    $result['alerts']
                ),
            ],
        ]);
    }

    public function show(
        Request $request,
        int $alertId
    ): JsonResponse {
        $student = $request->attributes->get('selected_student');

        $alert = $this->studentAlertService->getParentAlertDetails(
            $student,
            $alertId
        );

        return response()->json([
            'success' => true,
            'data' => new ParentStudentAlertDetailsResource($alert),
        ]);
    }
}
