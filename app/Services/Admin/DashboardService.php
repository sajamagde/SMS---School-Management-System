<?php

namespace App\Services\Admin;

use App\Enums\AttendanceStatus;
use App\Enums\EnrollmentStatus;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use Carbon\Carbon;

class DashboardService
{
    public function getDashboard(): array
    {
        $academicYearId = 1;
        $today = Carbon::today();

        return [
            'total_students' => $this->getTotalStudents($academicYearId),

            'total_teachers' => $this->getTotalTeachers(),

            'today_absences' => $this->getTodayAbsences($today),

            'attendance_percentage' => $this->getAttendancePercentage($today),

            'staff' => $this->getStaff(),

            'recent_students' => $this->getRecentStudents($academicYearId),
        ];
    }

    private function getTotalStudents(int $academicYearId): int
    {
        return StudentEnrollment::query()
            ->where('academic_year_id', $academicYearId)
            ->where('status', EnrollmentStatus::ACTIVE)
            ->count('id');
    }

    private function getTotalTeachers(): int
    {
        return Teacher::query()
            ->whereHas('user')
            ->count();
    }

    private function getTodayAbsences(Carbon $today): int
    {
        return StudentAttendance::query()
            ->whereDate('date', $today)
            ->whereIn('status', [
                AttendanceStatus::ABSENT,
                AttendanceStatus::EXCUSED_ABSENT,
            ])
            ->count();
    }

    private function getAttendancePercentage(Carbon $today): float
    {
        $total = StudentAttendance::query()
            ->whereDate('date', $today)
            ->count();

        if ($total === 0) {
            return 0;
        }

        $present = StudentAttendance::query()
            ->whereDate('date', $today)
            ->where('status', AttendanceStatus::PRESENT)
            ->count();

        return round(($present / $total) * 100, 2);
    }

    private function getStaff()
    {
        return Teacher::query()
            ->with([
                'user',
                'assignments.section.grade',
                'assignments.subject',
            ])
            ->get()
            ->flatMap(function ($teacher) {
                return $teacher->assignments->map(function ($assignment) use ($teacher) {
                    return [
                        'teacher_id' => $teacher->id,
                        'name' => trim(
                            ($teacher?->user?->first_name ?? '') . ' ' .
                                ($teacher?->user?->last_name ?? '')
                        ),

                        'grade' => [
                            'id' => $assignment->section->grade->id,
                            'name' => $assignment->section->grade->name,
                        ],

                        'subject' => [
                            'id' => $assignment->subject->id,
                            'name' => $assignment->subject->name,
                        ],
                    ];
                });
            })
            ->values();
    }
    private function getRecentStudents(int $academicYearId)
    {
        return Student::query()
            ->with([
                'user',
                'enrollments' => function ($query) use ($academicYearId) {
                    $query->where('academic_year_id', $academicYearId)
                        ->where('status', EnrollmentStatus::ACTIVE)
                        ->with(['grade', 'section']);
                },
            ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($student) {
                $enrollment = $student->enrollments->first();

                return [
                    'id' => $student->id,
                    'name' => trim(
                        ($student?->user?->first_name ?? '') . ' ' .
                            ($student?->user?->last_name ?? '')
                    ),
                    'grade' => $enrollment?->grade
                        ? [
                            'id' => $enrollment->grade->id,
                            'name' => $enrollment->grade->name,
                        ]
                        : null,
                    'section' => $enrollment?->section
                        ? [
                            'id' => $enrollment->section->id,
                            'name' => $enrollment->section->name,
                        ]
                        : null,
                ];
            });
    }
}
