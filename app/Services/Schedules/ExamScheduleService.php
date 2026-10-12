<?php

namespace App\Services\Schedules;

use App\Models\ExamSchedule;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamScheduleService extends BaseScheduleService
{
    public function createExam(array $data): ExamSchedule
    {
        return DB::transaction(function () use ($data) {

            $academicYearId = $this->getActiveAcademicYearId();

            $data['academic_year_id'] = $academicYearId;

            $this->ensureSubjectHasExam($data['subject_id']);

            $this->ensureSubjectBelongsToGrade(
                $data['subject_id'],
                $data['grade_id']
            );

            $this->ensureNoExamConflict($data);

            return ExamSchedule::create($data)
                ->load(['subject', 'grade']);
        });
    }

    public function updateExam(
        int $id,
        array $data
    ): ExamSchedule {
        return DB::transaction(function () use ($id, $data) {

            $exam = ExamSchedule::findOrFail($id);

            $data = array_merge(
                $exam->only([
                    'grade_id',
                    'subject_id',
                    'term',
                    'exam_date',
                    'start_time',
                    'end_time',
                ]),
                $data
            );

            $data['academic_year_id'] =
                $this->getActiveAcademicYearId();

            $this->ensureSubjectHasExam(
                $data['subject_id']
            );

            $this->ensureSubjectBelongsToGrade(
                $data['subject_id'],
                $data['grade_id']
            );

            $this->ensureNoExamConflict(
                $data,
                $id
            );

            $exam->update($data);

            return $exam->load([
                'subject',
                'grade',
            ]);
        });
    }

    public function deleteExam(int $id): void
    {
        $exam = ExamSchedule::findOrFail($id);

        $exam->delete();
    }

    public function getStudentExams(
        int $studentId,
        int $term
    ) {
        $enrollment = StudentEnrollment::query()
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->with('section')
            ->first();

        if (
            ! $enrollment ||
            ! $enrollment->section
        ) {
            return collect([]);
        }

        return ExamSchedule::query()
            ->with([
                'subject',
                'grade',
            ])
            ->where(
                'grade_id',
                $enrollment->section->grade_id
            )
            ->where(
                'academic_year_id',
                $this->getActiveAcademicYearId()
            )
            ->where('term', $term)
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();
    }

    private function ensureSubjectHasExam(
        int $subjectId
    ): void {
        $subject = Subject::findOrFail($subjectId);

        if (! $subject->has_exam) {
            throw ValidationException::withMessages([
                'subject_id' =>
                    'هذه المادة لا يوجد لها امتحان.',
            ]);
        }
    }

    private function ensureSubjectBelongsToGrade(
        int $subjectId,
        int $gradeId
    ): void {
        $exists = SectionSubject::query()
            ->where('subject_id', $subjectId)
            ->whereHas('section', function ($query) use ($gradeId) {
                $query->where('grade_id', $gradeId);
            })
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'subject_id' =>
                    'هذه المادة غير مقررة لهذا الصف.',
            ]);
        }
    }

    private function ensureNoExamConflict(
        array $data,
        ?int $ignoreId = null
    ): void {
        $exists = ExamSchedule::query()
            ->where('grade_id', $data['grade_id'])
            ->where(
                'academic_year_id',
                $data['academic_year_id']
            )
            ->where('term', $data['term'])
            ->where(
                'exam_date',
                $data['exam_date']
            )
            ->where(
                'start_time',
                '<',
                $data['end_time']
            )
            ->where(
                'end_time',
                '>',
                $data['start_time']
            )
            ->when(
                $ignoreId,
                fn ($query) =>
                    $query->where(
                        'id',
                        '!=',
                        $ignoreId
                    )
            )
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'exam_date' =>
                    'هذا الصف لديه امتحان آخر في نفس التاريخ والتوقيت.',
            ]);
        }
    }
}