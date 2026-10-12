<?php

namespace App\Services\Academic;

use App\Enums\EnrollmentStatus;
use App\Models\Section;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AcademicDistributionService
{
    public function autoDistribute(
        int $academicYearId,
        int $minimum,
        int $maximum
    ): array {
        return DB::transaction(function () use (
            $academicYearId,
            $minimum,
            $maximum
        ) {
            $unassigned = StudentEnrollment::query()
                ->where('academic_year_id', $academicYearId)
                ->where('section_id', '=', null)
                ->where('status', EnrollmentStatus::ACTIVE)
                ->orderBy('id', 'asc')
                ->get();

            if ($unassigned->isEmpty()) {
                return [
                    'distributed' => 0,
                    'message' => 'لا يوجد طلاب غير موزعين.',
                ];
            }

            $byGrade = $unassigned->groupBy('grade_id');

            $distributed = 0;

            foreach ($byGrade as $gradeId => $enrollments) {
                $sections = Section::query()
                    ->where('grade_id', $gradeId)
                    ->where('is_open', true)
                    ->withCount([
                        'enrollments as active_students_count' => function ($query) use ($academicYearId) {
                            $query->where('academic_year_id', $academicYearId)
                                ->where('status', EnrollmentStatus::ACTIVE);
                        },
                    ])
                    ->orderBy('id', 'asc')
                    ->get();

                if ($sections->isEmpty()) {
                    throw new RuntimeException(
                        "لا توجد شعب مفتوحة للصف رقم {$gradeId}."
                    );
                }

                $capacity = $sections->sum(
                    fn($section) => max(
                        0,
                        $maximum - $section->active_students_count
                    )
                );

                if ($enrollments->count() > $capacity) {
                    throw new RuntimeException(
                        "السعة المتاحة لا تكفي لاستيعاب جميع الطلاب غير الموزعين."
                    );
                }

                foreach ($enrollments as $enrollment) {
                    $availableSections = $sections
                        ->filter(
                            fn($section) =>
                            $section->active_students_count < $maximum
                        )
                        ->sortBy([
                            ['active_students_count', 'asc'],
                            ['id', 'asc'],
                        ]);

                    $section = $availableSections->first();

                    if (!$section) {
                        throw new RuntimeException(
                            'تعذر إكمال توزيع الطلاب.'
                        );
                    }

                    $enrollment->update([
                        'section_id' => $section->id,
                    ]);

                    $section->active_students_count++;

                    $distributed++;
                }
            }

            return [
                'distributed' => $distributed,
                'message' => 'تم توزيع الطلاب غير الموزعين بنجاح.',
            ];
        });
    }
    public function getStudents(int $academicYearId)
    {
        return StudentEnrollment::query()
            ->with([
                'student',
                'grade',
                'section',
            ])
            ->where('academic_year_id', $academicYearId)
            ->where('status', EnrollmentStatus::ACTIVE)
            ->orderBy('grade_id')
            ->orderBy('section_id')
            ->orderBy('id')
            ->get()
            ->map(function (StudentEnrollment $enrollment) {
                return [
                    'id' => $enrollment->student_id,
                    'name' => trim($enrollment->student->user->first_name ?? '') . ' ' . ($enrollment->student->user->last_name ?? ''),
                    'grade' => [
                        'id' => $enrollment->grade->id,
                        'name' => $enrollment->grade->name,
                    ],
                    'section' => $enrollment->section
                        ? [
                            'id' => $enrollment->section->id,
                            'name' => $enrollment->section->name,
                        ]
                        : null,
                ];
            });
    }
   public function moveStudent(
    int $studentId,
    int $sectionId,
    int $academicYearId
): array {
    return DB::transaction(function () use (
        $studentId,
        $sectionId,
        $academicYearId
    ) {
        $enrollment = StudentEnrollment::query()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', EnrollmentStatus::ACTIVE)
            ->first();

        if (!$enrollment) {
            throw new RuntimeException(
                'لا يوجد تسجيل فعال للطالب في السنة الدراسية الحالية.'
            );
        }

        // نفس الشعبة
        if ((int) $enrollment->section_id === $sectionId) {
            return [
                'changed' => false,
                'enrollment' => $enrollment->load(
                    'student',
                    'grade',
                    'section'
                ),
            ];
        }

        $section = Section::query()->find($sectionId);

        if (!$section) {
            throw new RuntimeException(
                'الشعبة المحددة غير موجودة.'
            );
        }

        // يجب أن تكون الشعبة من نفس الصف
        if ((int) $section->grade_id !== (int) $enrollment->grade_id) {
            throw new RuntimeException(
                'لا يمكن نقل الطالب إلى شعبة تابعة لصف آخر.'
            );
        }

        // لا يمكن النقل إلى شعبة مغلقة
        if (!$section->is_open) {
            throw new RuntimeException(
                'لا يمكن نقل الطالب إلى شعبة مغلقة.'
            );
        }

        // النقل الفعلي
        $enrollment->section_id = $section->id;
        $enrollment->save();

        return [
            'changed' => true,
            'enrollment' => $enrollment->fresh([
                'student',
                'grade',
                'section',
            ]),
        ];
    });
}
}
