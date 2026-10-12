<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use App\Enums\JustificationStatus;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\StudentAbsenceJustification;
use App\Models\StudentAttendance;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ParentJustificationService
{
    public function store(
        ParentModel $parent,
        array $data,
        ?UploadedFile $attachment = null
    ): array {
        $student = $parent->students()
            ->whereKey($data['student_id'])
            ->first();

        if (! $student) {
            abort(
                422,
                'الطالب المحدد ليس من أبناء ولي الأمر.'
            );
        }

        $attendance = StudentAttendance::query()
            ->where('student_id', $student->id)
            ->whereDate('date', $data['absence_date'])
            ->first();

        if (! $attendance) {
            abort(
                422,
                'لا يوجد سجل غياب للطالب في التاريخ المحدد.'
            );
        }

        if ($attendance?->status !== AttendanceStatus::ABSENT) {
            abort(
                422,
                'يمكن تقديم تبرير للغياب غير المبرر فقط.'
            );
        }

        $existingJustification = StudentAbsenceJustification::query()
            ->where('student_id', $student->id)
            ->whereDate('absence_date', $data['absence_date'])
            ->exists();

        if ($existingJustification) {
            abort(
                422,
                'يوجد تبرير بالفعل لهذا الغياب.'
            );
        }

        $attachmentPath = null;

        DB::transaction(function () use (
            $parent,
            $student,
            $data,
            $attachment,
            &$attachmentPath
        ) {
            if ($attachment) {
                $attachmentPath = $attachment->store(
                    'justifications'
                );
            }

            StudentAbsenceJustification::create([
                'student_id' => $student->id,
                'absence_date' => $data['absence_date'],
                'parent_id' => $parent->id,
                'reason' => $data['reason'],
                'attachment' => $attachmentPath,
                'status' => JustificationStatus::PENDING,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);
        });

        return [
            'student_id' => $student->id,
            'absence_date' => $data['absence_date'],
            'status' => JustificationStatus::PENDING->value,
        ];
    }
    public function getJustifications(
        ParentModel $parent,
        Student $student,
        ?JustificationStatus $status = null
    ): array {
        $baseQuery = StudentAbsenceJustification::query()
            ->where('parent_id', $parent->id)
            ->where('student_id', $student->id);

        $total = (clone $baseQuery)->count();

        $pending = (clone $baseQuery)
            ->where('status', JustificationStatus::PENDING)
            ->count();

        $approved = (clone $baseQuery)
            ->where('status', JustificationStatus::APPROVED)
            ->count();

        $rejected = (clone $baseQuery)
            ->where('status', JustificationStatus::REJECTED)
            ->count();

        $query = (clone $baseQuery)
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

        return [
            'statistics' => [
                'total' => $total,
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected,
            ],

            'justifications' => $justifications
                ->map(function ($justification) {
                    return [
                        'id' => $justification->id,

                        'status' => $this->justificationStatusLabel(
                            $justification->status
                        ),

                        'parent_name' => trim(
                            $justification->parent->user->first_name . ' ' .
                                $justification->parent->user->last_name
                        ),
                        'student_name' => trim(
                            $justification->student->user->first_name . ' ' .
                                $justification->student->user->last_name
                        ),

                        'submitted_at' => $justification->created_at
                            ?->toDateString() ?? '',

                        'absence_date' => $justification->absence_date
                            ->toDateString() ?? '',

                        'reason' => $justification->reason ?? '',

                        'attachment' => $justification->attachment !== null,
                    ];
                })
                ->values()
                ->all(),
        ];
    }
    public function getJustificationDetails(
        ParentModel $parent,
        int $justificationId
    ): array {
        $justification = StudentAbsenceJustification::query()
            ->whereKey($justificationId)
            ->where('parent_id', $parent->id)
            ->with([
                'student.user',
                'student.enrollments' => function ($query) {
                    $query
                        ->where('status', 'active')
                        ->with('section.grade');
                },
                'reviewedBy',
            ])
            ->firstOrFail();

        $enrollment = $justification->student
            ->enrollments
            ->first();

        return [
            'id' => $justification->id,

            'status' => $this->justificationStatusLabel($justification->status) ?? '',

            'submitted_at' => $justification->created_at
                ?->toDateString() ?? '',
            'reviewed_at' => $justification->reviewed_at
                ?->toDateString() ?? '',
            'absence_date' => $justification->absence_date
                ->toDateString() ?? '',

            'reason' => $justification->reason ?? '',

            'attachment' => $justification->attachment
                ? [
                    'available' => true,
                    'path' => $justification->attachment,
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

            'reviewed_by' => $justification->reviewedBy
                ? [
                    'id' => $justification->reviewedBy->id,
                    'name' => trim(
                        $justification->reviewedBy->first_name . ' ' .
                            $justification->reviewedBy->last_name
                    ),
                ]
                : '',
        ];
    }
    public function cancelJustification(
        ParentModel $parent,
        int $justificationId
    ): void {
        $justification = StudentAbsenceJustification::query()
            ->whereKey($justificationId)
            ->where('parent_id', $parent->id)
            ->firstOrFail();

        if ($justification->status !== JustificationStatus::PENDING) {
            abort(
                422,
                'لا يمكن إلغاء التبرير بعد مراجعته.'
            );
        }

        $justification->delete();
    }
    public function getParentAttachment(
        ParentModel $parent,
        int $justificationId
    ) {
        $justification = StudentAbsenceJustification::query()
            ->whereKey($justificationId)
            ->where('parent_id', $parent->id)
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
    private function justificationStatusLabel(JustificationStatus $status): string
    {
        return match ($status) {
            JustificationStatus::PENDING => 'قيد المراجعة',
            JustificationStatus::APPROVED => 'مقبول',
            JustificationStatus::REJECTED => 'مرفوض',
        };
    }
}
