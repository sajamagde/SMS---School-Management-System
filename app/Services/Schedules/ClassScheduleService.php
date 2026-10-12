<?php

namespace App\Services\Schedules;

use App\Models\ClassSchedule;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClassScheduleService extends BaseScheduleService
{
    public function getSectionScheduleForAdmin(
        int $sectionId,
        int $term
    ): array {
        $section = Section::query()
            ->with([
                'grade',
                'enrollments' => function ($query) {
                    $query->where('status', 'active');
                },
            ])
            ->findOrFail($sectionId);

        return [
            'section' => [
                'id' => $section->id,
                'name' => $section->name,
                'grade_name' => $section->grade?->name ?? '',
                'students_count' => $section->enrollments->count(),
            ],

            'schedule' => $this->getSectionWeeklySchedule(
                $sectionId,
                $term
            ),
        ];
    }
    public function saveWeeklySchedule(
        int $sectionId,
        int $term,
        array $lessons
    ): array {
        return DB::transaction(function () use (
            $sectionId,
            $term,
            $lessons
        ) {
            $academicYearId = $this->getActiveAcademicYearId();

            $section = Section::findOrFail($sectionId);


            $incomingSlots = collect($lessons)
                ->keyBy(function (array $lesson) {
                    return $lesson['day_of_week']
                        . '-'
                        . $lesson['period_number'];
                });


            $this->validateWeeklySchedule(
                $section,
                $academicYearId,
                $term,
                $incomingSlots
            );


            $existingSchedules = ClassSchedule::query()
                ->where('section_id', $sectionId)
                ->where('academic_year_id', $academicYearId)
                ->where('term', $term)
                ->get()
                ->keyBy(function (ClassSchedule $schedule) {
                    return $schedule->day_of_week
                        . '-'
                        . $schedule->period_number;
                });


            foreach ($incomingSlots as $slot => $lesson) {
                $data = [
                    'academic_year_id' => $academicYearId,
                    'section_id' => $sectionId,
                    'subject_id' => $lesson['subject_id'],
                    'teacher_id' => $lesson['teacher_id'],
                    'term' => $term,
                    'day_of_week' => $lesson['day_of_week'],
                    'period_number' => $lesson['period_number'],
                ];

                $data = $this->applyClassPeriodTimes($data);

                $existingSchedule = $existingSchedules->get($slot);

                if ($existingSchedule) {
                    $existingSchedule->update($data);
                } else {
                    ClassSchedule::create($data);
                }
            }


            foreach ($existingSchedules as $slot => $schedule) {
                if (! $incomingSlots->has($slot)) {
                    // Use destroy to ensure correct argument is passed when deleting by id
                    ClassSchedule::destroy($schedule->id);
                }
            }

            return $this->getSectionWeeklySchedule(
                $sectionId,
                $term
            );
        });
    }
    public function getSectionWeeklySchedule(
        int $sectionId,
        int $term
    ): array {
        Section::findOrFail($sectionId);
        $schedules = ClassSchedule::query()
            ->with([
                'subject',
                'teacher.user',
                'section.grade',
                'section.enrollments' => function ($query) {
                    $query->where('status', 'active');
                },
            ])
            ->where(
                'academic_year_id',
                $this->getActiveAcademicYearId()
            )
            ->where('section_id', $sectionId)
            ->where('term', $term)
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get();
        $scheduleBySlot = $schedules->keyBy(
            fn(ClassSchedule $schedule) =>
            $schedule->day_of_week
                . '-'
                . $schedule->period_number
        );

        $days = [];

        for ($day = 0; $day <= 4; $day++) {
            $periods = [];

            for ($period = 1; $period <= 7; $period++) {
                $slot = $day . '-' . $period;

                $schedule = $scheduleBySlot->get($slot);

                $times = config(
                    "schedule_times.periods.{$period}"
                );

                $break = collect(config('schedule_times.breaks'))
                    ->firstWhere('after_period', $period);

                $periods[] = [
                    'period_number' => $period,
                    'start_time' => $times['start'],
                    'end_time' => $times['end'],

                    'schedule' => $schedule
                        ? new \App\Http\Resources\ClassScheduleResource($schedule)
                        : null,

                    'break' => $break ? [
                        'name' => $break['name'],
                        'start_time' => $break['start'],
                        'end_time' => $break['end'],
                    ] : null,
                ];
            }
            $days[] = [
                'day_of_week' => $day,
                'periods' => $periods,
            ];
        }

        return $days;
    }

    public function getTeachersBySection(int $sectionId)
    {
        Section::findOrFail($sectionId);

        $sectionSubjects = SectionSubject::query()
            ->where('section_id', $sectionId)
            ->with('subject')
            ->get()
            ->keyBy('subject_id');

        $teachers = Teacher::query()
            ->whereHas('assignments', function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId);
            })
            ->with([
                'user',

                'assignments' => function ($q) use ($sectionId) {
                    $q->where('section_id', $sectionId)
                        ->with('subject');
                },

                'availabilities',
            ])
            ->get();

        return $teachers->map(function ($teacher) use ($sectionSubjects) {
            return [
                'id' => $teacher->id,

                'name' => trim(
                    $teacher->user->first_name . ' ' .
                        $teacher->user->last_name
                ),

                'subjects' => $teacher->assignments
                    ->map(function ($assignment) use ($sectionSubjects) {
                        $sectionSubject = $sectionSubjects
                            ->get($assignment->subject_id);

                        return [
                            'subject_id' => $assignment->subject_id,
                            'subject_name' => $assignment->subject?->name ?? '',
                            'weekly_periods' => $sectionSubject?->weekly_periods ?? 0,
                        ];
                    })
                    ->values(),

                'availability' => $teacher->availabilities,
            ];
        })->values();
    }

    public function getSectionDailySchedule(
        int $sectionId,
        int $term,
        int $dayOfWeek
    ): array {
        Section::findOrFail($sectionId);

        $schedules = ClassSchedule::query()
            ->with([
                'subject',
                'teacher.user',
                'section.grade',
                'section.enrollments' => function ($query) {
                    $query->where('status', 'active');
                },
            ])
            ->where(
                'academic_year_id',
                $this->getActiveAcademicYearId()
            )
            ->where('section_id', $sectionId)
            ->where('term', $term)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('period_number')
            ->get()
            ->keyBy('period_number');

        $data = [];

        for ($period = 1; $period <= 7; $period++) {

            $times = config(
                "schedule_times.periods.{$period}"
            );

            $schedule = $schedules->get($period);


            $data[] = [
                'type' => 'lesson',
                'period_number' => $period,
                'start_time' => $times['start'],
                'end_time' => $times['end'],
                'schedule' => $schedule
                    ? new \App\Http\Resources\ClassScheduleResource($schedule)
                    : null,
            ];


            $break = collect(
                config('schedule_times.breaks')
            )->firstWhere('after_period', $period);

            if ($break) {
                $data[] = [
                    'type' => 'break',
                    'name' => $break['name'],
                    'start_time' => $break['start'],
                    'end_time' => $break['end'],
                ];
            }
        }

        return $data;
    }
    public function getStudentDailySchedule(
        int $studentId,
        int $term,
        int $dayOfWeek
    ) {
        $enrollment = StudentEnrollment::query()
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->first();

        if (! $enrollment) {
            return collect([]);
        }

        return $this->getSectionDailySchedule(
            $enrollment->section_id,
            $term,
            $dayOfWeek
        );
    }
    public function getTeacherDailySchedule(
        int $teacherId,
        int $term,
        int $dayOfWeek
    ): array {
        $schedules = ClassSchedule::query()
            ->with([
                'subject',
                'teacher.user',
                'section.grade',
                'section.enrollments' => function ($query) {
                    $query->where('status', 'active');
                },
            ])
            ->where(
                'academic_year_id',
                $this->getActiveAcademicYearId()
            )
            ->where('teacher_id', $teacherId)
            ->where('term', $term)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('period_number')
            ->get()
            ->keyBy('period_number');

        $data = [];

        for ($period = 1; $period <= 7; $period++) {

            $times = config(
                "schedule_times.periods.{$period}"
            );

            $schedule = $schedules->get($period);


            $data[] = [
                'type' => 'lesson',
                'period_number' => $period,
                'start_time' => $times['start'],
                'end_time' => $times['end'],
                'schedule' => $schedule
                    ? new \App\Http\Resources\ClassScheduleResource($schedule)
                    : null,
            ];


            $break = collect(
                config('schedule_times.breaks')
            )->firstWhere('after_period', $period);

            if ($break) {
                $data[] = [
                    'type' => 'break',
                    'name' => $break['name'],
                    'start_time' => $break['start'],
                    'end_time' => $break['end'],
                ];
            }
        }

        return $data;
    }
    private function ensureTeacherCanTeachSubject(
        array $data,
        Section $section
    ): void {
        $exists = TeacherAssignment::query()
            ->where('teacher_id', $data['teacher_id'])
            ->where('section_id', $section->id)
            ->where('subject_id', $data['subject_id'])
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'schedule' => 'المعلم المختار غير مكلف بتدريس هذه المادة لهذه الشعبة.',
            ]);
        }
    }

    private function validateWeeklySchedule(
        Section $section,
        int $academicYearId,
        int $term,
        $incomingSlots
    ): void {

        $subjectCounts = $incomingSlots
            ->groupBy('subject_id')
            ->map(fn($lessons) => $lessons->count());

        foreach ($subjectCounts as $subjectId => $count) {

            $sectionSubject = SectionSubject::query()
                ->where('section_id', $section->id)
                ->where('subject_id', $subjectId)
                ->with('subject')
                ->first();

            $subjectName = $sectionSubject?->subject?->name
                ?? "المادة رقم {$subjectId}";

            if (! $sectionSubject) {
                throw ValidationException::withMessages([
                    'schedule' =>
                    "المادة {$subjectName} غير موجودة ضمن خطة هذه الشعبة.",
                ]);
            }

            if ($count > $sectionSubject->weekly_periods) {
                throw ValidationException::withMessages([
                    'schedule' =>
                    "تم تجاوز العدد الأسبوعي المحدد للمادة {$subjectName}. "
                        . "العدد المسموح: {$sectionSubject->weekly_periods} حصص.",
                ]);
            }
        }


        foreach ($incomingSlots as $lesson) {
            $data = [
                'section_id' => $section->id,
                'subject_id' => $lesson['subject_id'],
                'teacher_id' => $lesson['teacher_id'],
            ];

            $this->ensureTeacherCanTeachSubject($data, $section);
        }

        $teacherSlots = [];

        foreach ($incomingSlots as $lesson) {
            $key = $lesson['teacher_id']
                . '-'
                . $lesson['day_of_week']
                . '-'
                . $lesson['period_number'];

            if (isset($teacherSlots[$key])) {
                throw ValidationException::withMessages([
                    'schedule' =>
                    'لا يمكن للمعلم تدريس حصتين في نفس الوقت.',
                ]);
            }

            $teacherSlots[$key] = true;
        }

        foreach ($incomingSlots as $lesson) {
            $exists = ClassSchedule::query()
                ->where('teacher_id', $lesson['teacher_id'])
                ->where('academic_year_id', $academicYearId)
                ->where('term', $term)
                ->where('day_of_week', $lesson['day_of_week'])
                ->where('period_number', $lesson['period_number'])
                ->where('section_id', '!=', $section->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'schedule' =>
                    'هذا المعلم لديه حصة أخرى في نفس الوقت مع شعبة أخرى.',
                ]);
            }
        }
    }
    public function getScheduleTimes(): array
    {
        $periods = config('schedule_times.periods');
        $breaks = config('schedule_times.breaks');

        $items = [];

        for ($period = 1; $period <= count($periods); $period++) {

            $items[] = [
                'type' => 'period',
                'period_number' => $period,
                'start_time' => $periods[$period]['start'],
                'end_time' => $periods[$period]['end'],
            ];

            $break = collect($breaks)
                ->firstWhere('after_period', $period);

            if ($break) {
                $items[] = [
                    'type' => 'break',
                    'name' => $break['name'],
                    'start_time' => $break['start'],
                    'end_time' => $break['end'],
                ];
            }
        }

        return $items;
    }
}
