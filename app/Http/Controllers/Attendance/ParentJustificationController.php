<?php

namespace App\Http\Controllers\Attendance;

use App\Enums\JustificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\GetAttendanceHistoryRequest;
use App\Models\AcademicYear;
use App\Services\Attendance\ParentJustificationService;
use App\Services\Attendance\StudentAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentJustificationController extends Controller
{
    public function __construct(
        private ParentJustificationService $justificationService,
        private StudentAttendanceService $studentAttendanceService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer'],
            'absence_date' => ['required', 'date'],
            'reason' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $parent = $request->user()->parentProfile;

        if (! $parent) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف ولي أمر.',
            ], 403);
        }

        $justification = $this->justificationService->store(
            $parent,
            $validated,
            $request->file('attachment')
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تقديم التبرير بنجاح.',
            'data' => $justification,
        ], 201);
    }
    public function index(Request $request): JsonResponse
    {
        $parent = $request->user()->parentProfile;

        if (! $parent) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف ولي أمر.',
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
        $student = $request->attributes->get('selected_student');

        return response()->json([
            'success' => true,
            'message' => 'تم جلب المبررات بنجاح.',
            'data' => $this->justificationService->getJustifications(
                $parent,
                $student,
                $status
            ),
        ]);
    }
    public function show(
        Request $request,
        int $justificationId
    ): JsonResponse {
        $parent = $request->user()->parentProfile;

        if (! $parent) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف ولي أمر.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب تفاصيل التبرير بنجاح.',
            'data' => $this->justificationService->getJustificationDetails(
                $parent,
                $justificationId
            ),
        ]);
    }
    public function destroy(
        Request $request,
        int $justificationId
    ): JsonResponse {
        $parent = $request->user()->parentProfile;

        if (! $parent) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف ولي أمر.',
            ], 403);
        }

        $this->justificationService->cancelJustification(
            $parent,
            $justificationId
        );

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء التبرير بنجاح.',
        ]);
    }
    public function getStudentAttendance(
        Request $request,
        int $studentId,
        StudentAttendanceService $studentAttendanceService
    ): JsonResponse {
        $parent = $request->user()->parentProfile;

        if (! $parent) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف ولي أمر.',
            ], 403);
        }

        $student = $parent->students()
            ->whereKey($studentId)
            ->firstOrFail();

        $attendanceSummary = $studentAttendanceService
            ->getAttendanceSummary($student);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب نسبة حضور الطالب بنجاح.',
            'data' => [
                'student_id' => $student->id,
                'name' => trim(
                    $student->user->first_name . ' ' .
                        $student->user->last_name
                ),
                'code' => $student->user->code,
                'attendance_summary' => $attendanceSummary,
            ],
        ]);
    }
    public function attachment(
        Request $request,
        int $justificationId
    ) {
        $parent = $request->user()->parentProfile;

        if (! $parent) {
            abort(403, 'حساب ولي الأمر غير موجود.');
        }

        return $this->justificationService
            ->getParentAttachment(
                $parent,
                $justificationId
            );
    }
    public function getMyAttendance(
        Request $request
    ): JsonResponse {
        $student = $request->user()->student;

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف طالب.',
            ], 403);
        }

        $attendanceSummary = $this->studentAttendanceService
            ->getAttendanceSummary($student);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب نسبة حضور الطالب بنجاح.',
            'data' => [
                'student_id' => $student->id,

                'name' => trim(
                    $student->user->first_name . ' ' .
                        $student->user->last_name
                ),

                'code' => $student->user->code,

                'attendance_summary' => $attendanceSummary,
            ],
        ]);
    }
    public function attendanceHistory(
        GetAttendanceHistoryRequest $request
    ): JsonResponse {
        $student = $request->user()->student;

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف طالب.',
            ], 403);
        }

        $academicYear = AcademicYear::findOrFail(
            $request->validated('academic_year_id')
        );

        $data = $this->studentAttendanceService->getAttendanceHistory(
            $student,
            $academicYear,
            \Carbon\Carbon::parse($request->validated('from')),
            \Carbon\Carbon::parse($request->validated('to'))
        );

        return response()->json([
            'success' => true,
            'message' => 'تم جلب سجل الحضور والغياب بنجاح.',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => trim(
                        $student->user->first_name . ' ' .
                            $student->user->last_name
                    ),
                    'code' => $student->user->code,
                ],

                ...$data,
            ],
        ]);
    }
    public function childAttendanceHistory(
        GetAttendanceHistoryRequest $request
    ): JsonResponse {
        $parent = $request->user()->parentProfile;

        if (! $parent) {
            return response()->json([
                'success' => false,
                'message' => 'حساب المستخدم لا يحتوي على ملف ولي أمر.',
            ], 403);
        }

        $student = $request->attributes->get('selected_student');

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تحديد الطالب أولاً.',
            ], 422);
        }

        $academicYear = AcademicYear::findOrFail(
            $request->validated('academic_year_id')
        );

        $data = $this->studentAttendanceService->getAttendanceHistory(
            $student,
            $academicYear,
            \Carbon\Carbon::parse($request->validated('from')),
            \Carbon\Carbon::parse($request->validated('to'))
        );

        return response()->json([
            'success' => true,
            'message' => 'تم جلب سجل الحضور والغياب بنجاح.',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => trim(
                        $student->user->first_name . ' ' .
                            $student->user->last_name
                    ),
                    'code' => $student->user->code,
                ],

                ...$data,
            ],
        ]);
    }
}
