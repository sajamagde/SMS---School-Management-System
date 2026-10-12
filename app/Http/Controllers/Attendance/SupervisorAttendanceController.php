<?php

namespace App\Http\Controllers\Attendance;

use App\Enums\AttendanceStatus;
use App\Enums\JustificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreStudentAttendanceRequest;
use App\Http\Requests\Attendance\StoreTeacherAttendanceRequest;
use App\Models\AcademicYear;
use App\Services\Attendance\SupervisorAttendanceService;
use App\Services\Attendance\TeacherAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupervisorAttendanceController extends Controller
{
    public function __construct(
        private readonly SupervisorAttendanceService $attendanceService,
        private TeacherAttendanceService $teacherAttendanceService
    ) {}

    public function sections(Request $request): JsonResponse
    {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب شعب الصف المشرف عليه بنجاح.',
            'data' => $this->attendanceService->getSupervisorSections($supervisor),
        ]);
    }
    public function students(
        Request $request,
        int $sectionId
    ): JsonResponse {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب طلاب الشعبة بنجاح.',
            'data' => $this->attendanceService->getSectionStudents(
                $supervisor,
                $sectionId
            ),
        ]);
    }
    public function studentAttendance(
        Request $request,
        int $sectionId
    ): JsonResponse {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب حضور الطلاب بنجاح.',
            'data' => $this->attendanceService->getStudentAttendance(
                $supervisor,
                $sectionId
            ),
        ]);
    }
    public function storeStudentAttendance(
        StoreStudentAttendanceRequest $request,
        int $sectionId
    ): JsonResponse {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        $data = $this->attendanceService->storeStudentAttendance(
            $supervisor,
            $sectionId,
            $request->validated('attendances')
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل حضور الطلاب بنجاح.',
            'data' => $data,
        ]);
    }
    public function teachers(Request $request): JsonResponse
    {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب قائمة المعلمين بنجاح.',
            'data' => $this->attendanceService->getSupervisorTeachers(
                $supervisor
            ),
        ]);
    }
    public function storeTeacherAttendance(
        StoreTeacherAttendanceRequest $request
    ): JsonResponse {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        $data = $request->validated();

        $result = $this->attendanceService->storeTeacherAttendance(
            $supervisor,
            $data['teacher_id'],
            AttendanceStatus::from($data['status'])
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل حضور المعلم بنجاح.',
            'data' => $result,
        ]);
    }
    public function teacherDetails(
        Request $request,
        int $teacherId
    ): JsonResponse {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب تفاصيل المعلم بنجاح.',
            'data' => $this->attendanceService->getTeacherDetails(
                $supervisor,
                $teacherId
            ),
        ]);
    }
    public function studentDetails(
        Request $request,
        int $studentId
    ): JsonResponse {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب تفاصيل الطالب بنجاح.',
            'data' => $this->attendanceService->getStudentDetails(
                $supervisor,
                $studentId
            ),
        ]);
    }
    public function justifications(Request $request): JsonResponse
    {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        $status = $request->query('status');

        if ($status !== null && $status !== 'all') {
            $status = JustificationStatus::tryFrom($status);

            if ($status === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'حالة التبرير غير صالحة.',
                ], 422);
            }
        } else {
            $status = null;
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب قائمة المبررات بنجاح.',
            'data' => $this->attendanceService->getJustifications(
                $supervisor,
                $status
            ),
        ]);
    }
    public function justificationDetails(
        Request $request,
        int $justificationId
    ): JsonResponse {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب تفاصيل التبرير بنجاح.',
            'data' => $this->attendanceService->getJustificationDetails(
                $supervisor,
                $justificationId
            ),
        ]);
    }
    public function approveJustification(
        Request $request,
        int $justificationId
    ): JsonResponse {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        $data = $this->attendanceService->approveJustification(
            $supervisor,
            $justificationId
        );

        return response()->json([
            'success' => true,
            'message' => 'تمت الموافقة على التبرير بنجاح.',
            'data' => $data,
        ]);
    }
    public function rejectJustification(
        Request $request,
        int $justificationId
    ): JsonResponse {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        $data = $this->attendanceService->rejectJustification(
            $supervisor,
            $justificationId
        );

        return response()->json([
            'success' => true,
            'message' => 'تم رفض التبرير.',
            'data' => $data,
        ]);
    }
    public function getStudentAttendanceRate(
        Request $request,
        int $studentId
    ): JsonResponse {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف مشرف.',
            ], 403);
        }

        $data = $this->attendanceService->getStudentAttendanceRate(
            $supervisor,
            $studentId
        );

        return response()->json([
            'success' => true,
            'message' => 'تم جلب نسبة حضور الطالب بنجاح.',
            'data' => $data,
        ]);
    }
    public function attachment(
        Request $request,
        int $justificationId
    ) {
        $supervisor = $request->user()->supervisor;

        if (! $supervisor) {
            abort(403, 'حساب المشرف غير موجود.');
        }

        return $this->attendanceService
            ->getSupervisorAttachment(
                $supervisor,
                $justificationId
            );
    }
   public function summaryTeacher(Request $request)
    {
        $teacher = $request->user()->teacher;

        $summary = $this->teacherAttendanceService
            ->getAttendanceSummary($teacher);


        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }



    public function historyTeacher(Request $request)
    {
        $request->validate([
            'from' => [
                'required',
                'date',
            ],

            'to' => [
                'required',
                'date',
                'after_or_equal:from',
            ],
        ]);


        $teacher = $request->user()->teacher;


        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();


        if (! $academicYear) {
            abort(422, 'لا توجد سنة دراسية نشطة حاليًا.');
        }


        $history = $this->teacherAttendanceService
            ->getAttendanceHistory(
                $teacher,
                $academicYear,
                Carbon::parse($request->from),
                Carbon::parse($request->to)
            );


        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}
