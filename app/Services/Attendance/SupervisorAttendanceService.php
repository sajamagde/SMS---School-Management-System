<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use App\Enums\JustificationStatus;
use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAbsenceJustification;
use App\Models\StudentAttendance;
use App\Models\Supervisor;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\TeacherAttendance;
use App\Models\StudentGrade;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupervisorAttendanceService
{
    public function __construct(
        private StudentAttendanceService $studentAttendanceService
    ) {}
    public function getSupervisorSections(Supervisor $supervisor): array
    {
        $grade = $supervisor->grade()
            ->with([
                'sections' => function ($query) {
                    $query->withCount([
                        'enrollments as students_count' => function ($query) {
                            $query->where('status', 'active');
                        },
                    ]);
                },
            ])
            ->firstOrFail();

        return [
            'grade' => [
                'id' => $grade->id,
                'name' => $grade->name,
            ],
            'sections' => $grade->sections->map(function (Section $section) {
                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'students_count' => $section->students_count,
                ];
            })->values()->all(),
        ];
    }
    public function getSectionStudents(
        Supervisor $supervisor,
        int $sectionId
    ): array {
        $section = Section::query()
            ->where('id', $sectionId)
            ->where('grade_id', $supervisor->grade_id)
            ->with([
                'grade',
                'enrollments' => function ($query) {
                    $query
                        ->where('status', 'active')
                        ->with('student.user');
                },
            ])
            ->firstOrFail();

        return [
            'section' => [
                'id' => $section->id,
                'name' => $section->name,
                'grade' => [
                    'id' => $section->grade->id,
                    'name' => $section->grade->name,
                ],
            ],
            'students' => $section->enrollments
                ->map(function ($enrollment) {
                    $student = $enrollment->student;

                    return [
                        'id' => $student->id,
                        'name' => trim(
                            $student->user->first_name . ' ' . $student->user->last_name
                        ),
                        'code' => $student->user->code,
                    ];
                })
                ->values()
                ->all(),
        ];
    }
    public function getStudentAttendance(
        Supervisor $supervisor,
        int $sectionId
    ): array {
        $today = Carbon::today();

        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            abort(422, 'لا توجد سنة دراسية نشطة حاليًا.');
        }

        if (
            $today->lt($academicYear->start_date) ||
            $today->gt($academicYear->end_date)
        ) {
            abort(422, 'تسجيل الحضور غير متاح خارج السنة الدراسية.');
        }

        if ($today->isFriday() || $today->isSaturday()) {
            abort(422, 'تسجيل الحضور متاح فقط من الأحد إلى الخميس.');
        }

        $section = Section::query()
            ->where('id', $sectionId)
            ->where('grade_id', $supervisor->grade_id)
            ->with([
                'grade',
                'enrollments' => function ($query) {
                    $query
                        ->where('status', 'active')
                        ->with('student.user');
                },
            ])
            ->firstOrFail();
        $attendances = StudentAttendance::query()
            ->whereDate('date', $today)
            ->whereIn(
                'student_id',
                $section->enrollments->pluck('student_id')
            )
            ->with('recordedBy')
            ->get()
            ->keyBy('student_id');

        return [
            'date' => $today->toDateString(),
            'day' => $this->dayLabel($today),

            'section' => [
                'id' => $section->id,
                'name' => $section->name,
                'grade' => [
                    'id' => $section->grade->id,
                    'name' => $section->grade->name,
                ],
            ],

            'students' => $section->enrollments
                ->map(function ($enrollment) use ($attendances) {
                    $student = $enrollment->student;
                    $attendance = $attendances->get($student->id);

                    return [
                        'id' => $student->id,
                        'name' => trim(
                            $student->user->first_name . ' ' .
                                $student->user->last_name
                        ),
                        'code' => $student->user->code,
                        'attendance_status' => $attendance
                            ? $this->attendanceStatusLabel($attendance->status)
                            : '',
                        'recorded_by' => $attendance?->recordedBy
                            ? [
                                'id' => $attendance->recordedBy->id,
                                'name' => trim(
                                    $attendance->recordedBy->first_name . ' ' .
                                        $attendance->recordedBy->last_name
                                ),
                            ]
                            : '',
                    ];
                })
                ->values()
                ->all(),
        ];
    }
    public function storeStudentAttendance(
        Supervisor $supervisor,
        int $sectionId,
        array $attendances
    ): array {
        $today = Carbon::today();

        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            abort(422, 'لا توجد سنة دراسية نشطة حاليًا.');
        }

        if (
            $today->lt($academicYear->start_date) ||
            $today->gt($academicYear->end_date)
        ) {
            abort(422, 'تسجيل الحضور غير متاح خارج السنة الدراسية.');
        }

        if ($today->isFriday() || $today->isSaturday()) {
            abort(422, 'تسجيل الحضور متاح فقط من الأحد إلى الخميس.');
        }

        $section = Section::query()
            ->where('id', $sectionId)
            ->where('grade_id', $supervisor->grade_id)
            ->with([
                'enrollments' => function ($query) {
                    $query->where('status', 'active');
                },
            ])
            ->firstOrFail();

        $sectionStudentIds = $section->enrollments
            ->pluck('student_id')
            ->flip();

        foreach ($attendances as $attendance) {
            if (! $sectionStudentIds->has($attendance['student_id'])) {
                abort(
                    422,
                    'أحد الطلاب المرسل مع الطلب لا ينتمي إلى هذه الشعبة.'
                );
            }
        }

        DB::transaction(function () use (
            $attendances,
            $today,
            $supervisor
        ) {
            foreach ($attendances as $attendance) {
                StudentAttendance::updateOrCreate(
                    [
                        'student_id' => $attendance['student_id'],
                        'date' => $today,
                    ],
                    [
                        'status' => AttendanceStatus::from(
                            $attendance['status']
                        ),
                        'recorded_by' => $supervisor->user_id,
                    ]
                );
            }
        });

        return [
            'date' => $today->toDateString(),
            'saved_count' => count($attendances),
            'recorded_by' => [
                'id' => $supervisor->user_id,
                'name' => trim(
                    $supervisor->user->first_name . ' ' .
                        $supervisor->user->last_name
                ),
            ],
        ];
    }
    public function getSupervisorTeachers(
        Supervisor $supervisor
    ): array {
        $today = Carbon::today();

        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            abort(422, 'لا توجد سنة دراسية نشطة حاليًا.');
        }

        if (
            $today->lt($academicYear->start_date) ||
            $today->gt($academicYear->end_date)
        ) {
            abort(422, 'عرض حضور المعلمين غير متاح خارج السنة الدراسية.');
        }

        if ($today->isFriday() || $today->isSaturday()) {
            abort(422, 'عرض حضور المعلمين متاح فقط من الأحد إلى الخميس.');
        }

        $assignments = TeacherAssignment::query()
            ->whereHas('section', function ($query) use ($supervisor) {
                $query->where('grade_id', $supervisor->grade_id);
            })
            ->with([
                'teacher.user',
                'subject',
                'section.grade',
            ])
            ->get();

        $teacherIds = $assignments
            ->pluck('teacher_id')
            ->unique()
            ->values();

        $attendances = TeacherAttendance::query()
            ->whereDate('date', $today)
            ->whereIn('teacher_id', $teacherIds)
            ->with('recordedBy')
            ->get()
            ->keyBy('teacher_id');
        $teachers = $assignments
            ->groupBy('teacher_id')
            ->map(function ($teacherAssignments) use ($attendances, $supervisor) {
                $teacher = $teacherAssignments->first()->teacher;
                $attendance = $attendances->get($teacher->id);

                return [
                    'id' => $teacher->id,

                    'name' => trim(
                        $teacher->user->first_name . ' ' .
                            $teacher->user->last_name
                    ),

                    'code' => $teacher->user->code,

                    'assignments' => $teacherAssignments
                        ->map(function ($assignment) {
                            return [
                                'subject' => [
                                    'id' => $assignment->subject->id,
                                    'name' => $assignment->subject->name,
                                ],
                                'grade' => [
                                    'id' => $assignment->section->grade->id,
                                    'name' => $assignment->section->grade->name,
                                ],
                                'section' => [
                                    'id' => $assignment->section->id,
                                    'name' => $assignment->section->name,
                                ],
                            ];
                        })
                        ->values()
                        ->all(),

                    'attendance_status' => $attendance
                        ? $this->attendanceStatusLabel($attendance->status)
                        : '',

                    'can_edit_attendance' =>
                    $attendance !== null &&
                        $attendance->recorded_by === $supervisor->user_id,
                    'recorded_by' => $attendance?->recordedBy
                        ? [
                            'id' => $attendance->recordedBy->id,
                            'name' => trim(
                                $attendance->recordedBy->first_name . ' ' .
                                    $attendance->recordedBy->last_name
                            ),
                        ]
                        : '',
                ];
            })
            ->values()
            ->all();

        return [
            'day' => $today->translatedFormat('l'),
            'teachers' => $teachers,
        ];
    }
    public function storeTeacherAttendance(
        Supervisor $supervisor,
        int $teacherId,
        AttendanceStatus $status
    ): array {
        $today = Carbon::today();

        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            abort(422, 'لا توجد سنة دراسية نشطة حاليًا.');
        }

        if (
            $today->lt($academicYear->start_date) ||
            $today->gt($academicYear->end_date)
        ) {
            abort(422, 'تسجيل الحضور غير متاح خارج السنة الدراسية.');
        }

        if ($today->isFriday() || $today->isSaturday()) {
            abort(422, 'تسجيل الحضور متاح فقط من الأحد إلى الخميس.');
        }

        $teacher = Teacher::query()
            ->whereKey($teacherId)
            ->whereHas('assignments.section', function ($query) use ($supervisor) {
                $query->where('grade_id', $supervisor->grade_id);
            })
            ->firstOrFail();

        return DB::transaction(function () use (
            $teacher,
            $today,
            $status,
            $supervisor
        ) {
            $attendance = TeacherAttendance::query()
                ->where('teacher_id', $teacher->id)
                ->whereDate('date', $today)
                ->first();

            if ($attendance) {
                if ($attendance->recorded_by !== $supervisor->user_id) {
                    abort(
                        403,
                        'لا يمكنك تعديل حضور هذا المعلم لأن مشرفًا آخر قام بتسجيله.'
                    );
                }

                $attendance->update([
                    'status' => $status,
                ]);
            } else {
                $attendance = TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'date' => $today,
                    'status' => $status,
                    'recorded_by' => $supervisor->user_id,
                ]);
            }
            $attendance->load('recordedBy');
            return [
                'teacher_id' => $teacher->id,
                'date' => $today->toDateString(),
                'status' => $this->attendanceStatusLabel(
                    $attendance->status
                ) ?? '',
                'recorded_by' => [
                    'id' => $attendance->recordedBy->id,
                    'name' => trim(
                        $attendance->recordedBy->first_name . ' ' .
                            $attendance->recordedBy->last_name
                    ),
                ],
            ];
        });
    }
    public function getTeacherDetails(
        Supervisor $supervisor,
        int $teacherId
    ): array {
        $teacher = Teacher::query()
            ->whereKey($teacherId)
            ->whereHas('assignments.section', function ($query) use ($supervisor) {
                $query->where('grade_id', $supervisor->grade_id);
            })
            ->with([
                'user',
                'assignments.subject',
                'assignments.section.grade',
            ])
            ->firstOrFail();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $attendances = TeacherAttendance::query()
            ->where('teacher_id', $teacher->id)
            ->whereBetween('date', [
                $startOfMonth,
                $endOfMonth,
            ])
            ->get();

        return [
            'id' => $teacher->id,

            'name' => trim(
                $teacher->user->first_name . ' ' .
                    $teacher->user->last_name
            ),

            'code' => $teacher->user->code,
            'absence_count' => $attendances
                ->where('status', AttendanceStatus::ABSENT)
                ->count(),

            'attendance_summary' => [
                'present' => $attendances
                    ->where('status', AttendanceStatus::PRESENT)
                    ->count(),

                'absent' => $attendances
                    ->where('status', AttendanceStatus::ABSENT)
                    ->count(),

                'excused_absent' => $attendances
                    ->where('status', AttendanceStatus::EXCUSED_ABSENT)
                    ->count(),
            ],

            'personal_information' => [
                'name' => trim(
                    $teacher->user->first_name . ' ' .
                        $teacher->user->last_name
                ),
                'phone' => $teacher->user->phone,
            ],

            'teaching_information' => [
                'subjects' => $teacher->assignments
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->subject->id,
                            'name' => $assignment->subject->name,
                        ];
                    })
                    ->unique('id')
                    ->values()
                    ->all(),

                'grades' => $teacher->assignments
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->section->grade->id,
                            'name' => $assignment->section->grade->name,
                        ];
                    })
                    ->unique('id')
                    ->values()
                    ->all(),

                'sections' => $teacher->assignments
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->section->id,
                            'name' => $assignment->section->name,
                            'grade' => [
                                'id' => $assignment->section->grade->id,
                                'name' => $assignment->section->grade->name,
                            ],
                        ];
                    })
                    ->unique('id')
                    ->values()
                    ->all(),
            ],
        ];
    }
    public function getStudentDetails(
        Supervisor $supervisor,
        int $studentId
    ): array {
        $student = Student::query()
            ->whereKey($studentId)
            ->whereHas('enrollments', function ($query) use ($supervisor) {
                $query
                    ->where('status', 'active')
                    ->whereHas('section', function ($sectionQuery) use ($supervisor) {
                        $sectionQuery->where(
                            'grade_id',
                            $supervisor->grade_id
                        );
                    });
            })
            ->with([
                'user',
                'parent.user',
                'enrollments' => function ($query) {
                    $query
                        ->where('status', 'active')
                        ->with([
                            'section.grade',
                            'section.subjects.subject',
                        ]);
                },
                'attendances',
                'absenceJustifications',
            ])
            ->firstOrFail();

        $attendances = $student->attendances;

        $attendanceSummary = $this->studentAttendanceService
            ->getAttendanceSummary($student);

        $enrollment = $student->enrollments->first();


        $subjectIds = $enrollment?->section?->subjects
            ->pluck('subject_id')
            ?? collect();

        $grades = StudentGrade::query()
            ->where('student_id', $student->id)
            ->whereIn('subject_id', $subjectIds)
            ->whereNotNull('score')
            ->get()
            ->keyBy('subject_id');

        $subjects = $enrollment?->section?->subjects
            ->map(function ($sectionSubject) use ($grades) {
                $grade = $grades->get($sectionSubject->subject_id);

                return [
                    'subject_name' => $sectionSubject->subject->name,
                    'score' => $grade?->score !== null
                        ? (float) $grade->score
                        : '',
                ];
            })
            ->values()
            ->all() ?? [];

        $scores = collect($subjects)
            ->pluck('score')
            ->filter(fn($score) => $score !== null)
            ->map(fn($score) => (float) $score);

        $average = $scores->isNotEmpty()
            ? round($scores->average(), 2)
            : null;

        return [
            'id' => $student->id,

            'name' => trim(
                $student->user->first_name . ' ' .
                    $student->user->last_name
            ),

            'code' => $student->user->code,
            'absence_count' => $attendanceSummary['absent_days'],
            'attendance_summary' => $attendanceSummary,

            'academic_summary' => [
                'average' => $average ?? 0,
                'subjects' => $subjects,
            ],

            'personal_information' => [
                'name' => trim(
                    $student->user->first_name . ' ' .
                        $student->user->last_name
                ),
                'phone' => $student->user->phone,
                'address' => $student->address,
                'mother_name' => $student->mother_name,
                'birth_date' => $student->birth_date
                    ? $student->birth_date->toDateString()
                    : '',

                'grade' => $enrollment?->section?->grade
                    ? [
                        'id' => $enrollment->section->grade->id,
                        'name' => $enrollment->section->grade->name,
                    ]
                    : null,

                'section' => $enrollment?->section
                    ? [
                        'id' => $enrollment->section->id,
                        'name' => $enrollment->section->name,
                    ]
                    : null,
            ],

            'parent' => $student->parent?->user
                ? [
                    'id' => $student->parent->id,
                    'name' => trim(
                        $student->parent->user->first_name . ' ' .
                            $student->parent->user->last_name
                    ),
                    'phone' => $student->parent->user->phone ?? '',
                ]
                : '',

            'absence_records' => $attendances
                ->filter(function ($attendance) {
                    return in_array(
                        $attendance->status,
                        [
                            AttendanceStatus::ABSENT,
                            AttendanceStatus::EXCUSED_ABSENT,
                        ],
                        true
                    );
                })
                ->sortByDesc('date')
                ->map(function ($attendance) use ($student) {

                    $justification = $student->absenceJustifications
                        ->first(function ($justification) use ($attendance) {
                            return $justification->absence_date->isSameDay(
                                $attendance->date
                            );
                        });

                    return [
                        'date' => $attendance->date->toDateString(),

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
                                'submitted_at' => $justification->created_at
                                    ?->toDateString() ?? '',
                            ]
                            : '',
                    ];
                })
                ->values()
                ->all(),
        ];
    }
    public function getJustifications(
        Supervisor $supervisor,
        ?JustificationStatus $status = null
    ): array {
        $query = StudentAbsenceJustification::query()
            ->whereHas('student.enrollments', function ($query) use ($supervisor) {
                $query
                    ->where('status', 'active')
                    ->whereHas('section', function ($sectionQuery) use ($supervisor) {
                        $sectionQuery->where(
                            'grade_id',
                            $supervisor->grade_id
                        );
                    });
            })
            ->with([
                'student.user',
                'student.enrollments' => function ($query) {
                    $query
                        ->where('status', 'active')
                        ->with('section.grade');
                },
                'parent.user',
            ])
            ->latest();

        if ($status !== null) {
            $query->where('status', $status);
        }

        $justifications = $query->get();

        return $justifications
            ->map(function ($justification) {
                $enrollment = $justification->student
                    ->enrollments
                    ->first();

                return [
                    'id' => $justification->id,

                    'status' => $this->justificationStatusLabel(
                        $justification->status
                    ),

                    'parent_name' => $justification->parent?->user
                        ? trim(
                            $justification->parent->user->first_name . ' ' .
                                $justification->parent->user->last_name
                        )
                        : '',
                    'student_name' => trim(
                        $justification->student->user->first_name . ' ' .
                            $justification->student->user->last_name
                    ),

                    'absence_date' => $justification->absence_date
                        ->toDateString(),

                    'section' => $enrollment?->section
                        ? [
                            'grade' => [
                                'id' => $enrollment->section->grade->id,
                                'name' => $enrollment->section->grade->name,
                            ],
                            'id' => $enrollment->section->id,
                            'name' => $enrollment->section->name,
                        ]
                        : '',
                ];
            })
            ->values()
            ->all();
    }
    public function getJustificationDetails(
        Supervisor $supervisor,
        int $justificationId
    ): array {
        $justification = StudentAbsenceJustification::query()
            ->whereKey($justificationId)
            ->whereHas('student.enrollments', function ($query) use ($supervisor) {
                $query
                    ->where('status', 'active')
                    ->whereHas('section', function ($sectionQuery) use ($supervisor) {
                        $sectionQuery->where(
                            'grade_id',
                            $supervisor->grade_id
                        );
                    });
            })
            ->with([
                'student.user',
                'student.enrollments' => function ($query) {
                    $query
                        ->where('status', 'active')
                        ->with('section.grade');
                },
                'parent.user',
            ])
            ->firstOrFail();

        $enrollment = $justification->student
            ->enrollments
            ->first();

        return [
            'id' => $justification->id,

            'status' => $this->justificationStatusLabel(
                $justification->status
            ),

            'submitted_at' => $justification->created_at
                ?->toDateString() ?? '',
            'absence_date' => $justification->absence_date->toDateString() ?? '',

            'reason' => $justification->reason,

            'attachment' => $justification->attachment
                ? [
                    'available' => true,
                ]
                : [
                    'available' => false,
                    'path' => '',
                ],

            'student' => [
                'id' => $justification->student->id,

                'name' => trim(
                    $justification->student->user->first_name . ' ' .
                        $justification->student->user->last_name
                ),

                'code' => $justification->student->user->code,

                'grade' => $enrollment?->section?->grade
                    ? [
                        'id' => $enrollment->section->grade->id,
                        'name' => $enrollment->section->grade->name,
                    ]
                    : '',

                'section' => $enrollment?->section
                    ? [
                        'id' => $enrollment->section->id,
                        'name' => $enrollment->section->name,
                    ]
                    : '',
            ],

            'parent' => [
                'id' => $justification->parent->id,

                'name' => trim(
                    $justification->parent->user->first_name . ' ' .
                        $justification->parent->user->last_name
                ),

                'phone' => $justification->parent->user->phone,
            ],

            'reviewed_at' => $justification->reviewed_at
                ?->toDateString() ?? '',
        ];
    }
    public function approveJustification(
        Supervisor $supervisor,
        int $justificationId
    ): array {
        return DB::transaction(function () use (
            $supervisor,
            $justificationId
        ) {
            $justification = StudentAbsenceJustification::query()
                ->whereKey($justificationId)
                ->whereHas('student.enrollments', function ($query) use ($supervisor) {
                    $query
                        ->where('status', 'active')
                        ->whereHas('section', function ($sectionQuery) use ($supervisor) {
                            $sectionQuery->where(
                                'grade_id',
                                $supervisor->grade_id
                            );
                        });
                })
                ->with('student')
                ->lockForUpdate()
                ->firstOrFail();

            if ($justification->status !== JustificationStatus::PENDING) {
                abort(
                    422,
                    'لا يمكن مراجعة هذا التبرير لأنه تمت مراجعته مسبقًا.'
                );
            }

            $attendance = $justification->student
                ->attendances()
                ->whereDate(
                    'date',
                    $justification->absence_date
                )
                ->lockForUpdate()
                ->first();

            if (! $attendance) {
                abort(
                    422,
                    'لا يوجد سجل حضور للطالب في تاريخ الغياب.'
                );
            }

            if ($attendance->status !== AttendanceStatus::ABSENT) {
                abort(
                    422,
                    'حالة حضور الطالب لا تسمح بقبول هذا التبرير.'
                );
            }

            $justification->update([
                'status' => JustificationStatus::APPROVED,
                'reviewed_by' => $supervisor->user_id,
                'reviewed_at' => now(),
            ]);

            $attendance->update([
                'status' => AttendanceStatus::EXCUSED_ABSENT,
            ]);

            return [
                'id' => $justification->id,
                'status' => $this->justificationStatusLabel(
                    $justification->status
                ),
                'reviewed_by' => $supervisor->user_id,
                'reviewed_at' => $justification->reviewed_at
                    ?->toDateString() ?? '',
            ];
        });
    }
    public function rejectJustification(
        Supervisor $supervisor,
        int $justificationId
    ): array {
        return DB::transaction(function () use (
            $supervisor,
            $justificationId
        ) {
            $justification = StudentAbsenceJustification::query()
                ->whereKey($justificationId)
                ->whereHas('student.enrollments', function ($query) use ($supervisor) {
                    $query
                        ->where('status', 'active')
                        ->whereHas('section', function ($sectionQuery) use ($supervisor) {
                            $sectionQuery->where(
                                'grade_id',
                                $supervisor->grade_id
                            );
                        });
                })
                ->with('student')
                ->lockForUpdate()
                ->firstOrFail();

            if ($justification->status !== JustificationStatus::PENDING) {
                abort(
                    422,
                    'لا يمكن مراجعة هذا التبرير لأنه تمت مراجعته مسبقًا.'
                );
            }

            $attendance = $justification->student
                ->attendances()
                ->whereDate(
                    'date',
                    $justification->absence_date
                )
                ->lockForUpdate()
                ->first();

            if (! $attendance) {
                abort(
                    422,
                    'لا يوجد سجل حضور للطالب في تاريخ الغياب.'
                );
            }

            $justification->update([
                'status' => JustificationStatus::REJECTED,
                'reviewed_by' => $supervisor->user_id,
                'reviewed_at' => now(),
            ]);

            return [
                'id' => $justification->id,
                'status' => $this->justificationStatusLabel(
                    $justification->status
                ),
                'reviewed_by' => $supervisor->user_id,
                'reviewed_at' => $justification->reviewed_at
                    ?->toDateString() ?? '',
            ];
        });
    }
    public function getStudentAttendanceRate(
        Supervisor $supervisor,
        int $studentId
    ): array {
        $today = Carbon::today();

        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            abort(422, 'لا توجد سنة دراسية نشطة حاليًا.');
        }

        if ($today->lt($academicYear->start_date)) {
            abort(
                422,
                'لم تبدأ السنة الدراسية الحالية بعد.'
            );
        }

        $student = Student::query()
            ->whereKey($studentId)
            ->whereHas('enrollments', function ($query) use ($supervisor) {
                $query
                    ->where('status', 'active')
                    ->whereHas('section', function ($sectionQuery) use ($supervisor) {
                        $sectionQuery->where(
                            'grade_id',
                            $supervisor->grade_id
                        );
                    });
            })
            ->firstOrFail();

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

        $attendanceRate = $totalDays > 0
            ? round(($presentDays / $totalDays) * 100, 2)
            : 0;

        return [
            'student_id' => $student->id,

            'period' => [
                'from' => $academicYear->start_date->toDateString(),
                'to' => $today->toDateString(),
            ],

            'attendance_rate' => $attendanceRate,

            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'excused_absent_days' => $excusedAbsentDays,
        ];
    }
    public function getSupervisorAttachment(
        Supervisor $supervisor,
        int $justificationId
    ) {
        $justification = StudentAbsenceJustification::query()
            ->whereKey($justificationId)
            ->whereHas('student.enrollments', function ($query) use ($supervisor) {
                $query
                    ->where('status', 'active')
                    ->whereHas('section', function ($sectionQuery) use ($supervisor) {
                        $sectionQuery->where(
                            'grade_id',
                            $supervisor->grade_id
                        );
                    });
            })
            ->firstOrFail();

        if (! $justification->attachment) {
            abort(404, 'لا يوجد مرفق لهذا التبرير.');
        }

        if (! Storage::disk('local')->exists($justification->attachment)) {
            abort(404, 'المرفق غير موجود.');
        }

        return response()->file(
            Storage::disk('local')->path($justification->attachment)
        );
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
