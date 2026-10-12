<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use App\Enums\JustificationStatus;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentAbsenceJustification;
use Carbon\Carbon;

class StudentAttendanceService
{
    public function getAttendanceSummary(Student $student): array
    {
        $today = Carbon::today();

        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            abort(422, 'لا توجد سنة دراسية نشطة حاليًا.');
        }

        $attendances = $student->attendances()
            ->whereBetween('date', [
                $academicYear->start_date,
                $today,
            ])
            ->get();

        $totalDays = $attendances->count();

        $presentDays = $attendances
            ->where('status', AttendanceStatus::PRESENT)
            ->count();

        $absentDays = $attendances
            ->where('status', AttendanceStatus::ABSENT)
            ->count();

        $excusedAbsentDays = $attendances
            ->where('status', AttendanceStatus::EXCUSED_ABSENT)
            ->count();

        $attendedDays = $attendances
            ->whereIn('status', [
                AttendanceStatus::PRESENT,
                AttendanceStatus::EXCUSED_ABSENT,
            ])
            ->count();

        $attendanceRate = $totalDays > 0
            ? round(($attendedDays / $totalDays) * 100, 2)
            : 0;

        return [
            'attendance_rate' => $attendanceRate,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'excused_absent_days' => $excusedAbsentDays,
        ];
    }
    public function getAttendanceHistory(
        Student $student,
        AcademicYear $academicYear,
        Carbon $from,
        Carbon $to
    ): array {
        if ($from->lt($academicYear->start_date)) {
            abort(422, 'تاريخ بداية الفترة خارج السنة الدراسية.');
        }

        if ($to->gt($academicYear->end_date)) {
            abort(422, 'تاريخ نهاية الفترة خارج السنة الدراسية.');
        }

        $attendances = $student->attendances()
            ->whereBetween('date', [$from, $to])
            ->get()
            ->keyBy(fn($attendance) => $attendance->date->toDateString());

        $justifications = StudentAbsenceJustification::query()
            ->where('student_id', $student->id)
            ->whereBetween('absence_date', [$from, $to])
            ->get()
            ->keyBy(fn($justification) => $justification->absence_date->toDateString());
        $attendances = $student->attendances()
            ->whereBetween('date', [$from, $to])
            ->get()
            ->keyBy(fn($attendance) => $attendance->date->toDateString());

        $totalDays = $attendances->count();

        $presentDays = $attendances
            ->where('status', AttendanceStatus::PRESENT)
            ->count();

        $absentDays = $attendances
            ->where('status', AttendanceStatus::ABSENT)
            ->count();

        $excusedAbsentDays = $attendances
            ->where('status', AttendanceStatus::EXCUSED_ABSENT)
            ->count();

        $attendedDays = $attendances
            ->whereIn('status', [
                AttendanceStatus::PRESENT,
                AttendanceStatus::EXCUSED_ABSENT,
            ])
            ->count();

        $attendanceRate = $totalDays > 0
            ? round(($attendedDays / $totalDays) * 100, 2)
            : 0;
        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'attendance_rate' => $attendanceRate,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'excused_absent_days' => $excusedAbsentDays,
            'attendance_records' => $attendances
                ->sortBy('date')
                ->map(function ($attendance) use ($justifications, $student) {
                    $date = $attendance->date->toDateString();

                    $justification = $justifications->get($date);

                    return [
                        'date' => $date,

                        'day' => $this->dayLabel($attendance->date),

                        'status' => $this->attendanceStatusLabel(
                            $attendance->status
                        ),

                        'justification' => $justification
                            ? [
                                'id' => $justification->id,
                                'status' => $this->justificationStatusLabel(
                                    $justification->status
                                ),
                                'reason' => $justification->reason,
                            ]
                            : '',
                    ];
                })
                ->values()
                ->all(),

            'justifications' => $justifications
                ->sortByDesc('absence_date')
                ->map(function ($justification) {
                    return [
                        'id' => $justification->id,

                        'absence_date' => $justification->absence_date
                            ->toDateString() ?? '',

                        'reason' => $justification->reason ?? '',

                        'status' => $this->justificationStatusLabel(
                            $justification->status
                        ),
                        'submitted_at' => $justification->created_at
                            ?->toDateString() ?? '',
                    ];
                })
                ->values()
                ->all(),
        ];
    }
    private function attendanceStatusLabel(AttendanceStatus $status): string
    {
        return match ($status) {
            AttendanceStatus::PRESENT => 'حاضر',
            AttendanceStatus::ABSENT => 'غائب',
            AttendanceStatus::EXCUSED_ABSENT => 'غائب بعذر',
        };
    }

    private function justificationStatusLabel(JustificationStatus $status): string
    {
        return match ($status) {
            JustificationStatus::PENDING => 'قيد المراجعة',
            JustificationStatus::APPROVED => 'مقبول',
            JustificationStatus::REJECTED => 'مرفوض',
        };
    }

    private function dayLabel(Carbon $date): string
    {
        return $date->locale('ar')->translatedFormat('l');
    }
}
