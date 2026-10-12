<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\Teacher;
use Carbon\Carbon;

class TeacherAttendanceService
{
    public function getAttendanceSummary(Teacher $teacher): array
    {
        $today = Carbon::today();

        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            abort(422, 'لا توجد سنة دراسية نشطة حاليًا.');
        }


        $attendances = $teacher->attendances()
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


        $attendanceRate = $totalDays > 0
            ? round(($presentDays / $totalDays) * 100, 2)
            : 0;


        return [
            'attendance_rate' => $attendanceRate,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
        ];
    }


    public function getAttendanceHistory(
        Teacher $teacher,
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


        $attendances = $teacher->attendances()
            ->whereBetween('date', [
                $from,
                $to
            ])
            ->orderBy('date')
            ->get();


        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],


            'attendance_records' => $attendances
                ->map(function ($attendance) {

                    return [
                        'date' => $attendance->date->toDateString(),

                        'day' => $this->dayLabel(
                            $attendance->date
                        ),

                        'status' => $this->attendanceStatusLabel(
                            $attendance->status
                        ),
                        'recorded_by' => [
                            'id' => $attendance->recordedBy->id,
                            'name' => trim(
                                $attendance->recordedBy->first_name . ' ' .
                                    $attendance->recordedBy->last_name
                            ),
                        ],
                    ];
                })
                ->values()
                ->all(),
        ];
    }


    private function attendanceStatusLabel(
        AttendanceStatus $status
    ): string {

        return match ($status) {

            AttendanceStatus::PRESENT =>
            'حاضر',

            AttendanceStatus::ABSENT =>
            'غائب',

            AttendanceStatus::EXCUSED_ABSENT =>
            'غائب بعذر',
        };
    }


    private function dayLabel(Carbon $date): string
    {
        return $date
            ->locale('ar')
            ->translatedFormat('l');
    }
}
