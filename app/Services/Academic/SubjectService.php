<?php

namespace App\Services\Academic;

use App\Models\Grade;
use App\Models\SectionSubject;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class SubjectService
{
    private const DEFAULT_WEEKLY_PERIODS = 1;

    private const WEEKLY_PERIODS = [
        1 => 4,
        2 => 2,
        3 => 9,
        4 => 1,
        5 => 1,
        6 => 5,
        7 => 3,
        8 => 4,
        9 => 3,
        10 => 1,
        11 => 1,
        12 => 1,
    ];

    public function getAllSubjects()
    {
        return Subject::query()
            ->with('sectionSubjects.section.grade')
            ->get()
            ->map(function (Subject $subject) {
                $grades = $subject->sectionSubjects
                    ->pluck('section.grade')
                    ->filter()
                    ->unique('id')
                    ->values()
                    ->map(function ($grade) {
                        return [
                            'id' => $grade->id,
                            'name' => $grade->name,
                        ];
                    });

                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'has_exam' => $subject->has_exam,
                    'grades' => $grades,
                ];
            });
    }

    public function createOrAttachSubject(array $data): Subject
    {
        return DB::transaction(function () use ($data) {
            $name = trim($data['name']);

            $subject = Subject::firstOrCreate(
                ['name' => $name],
                [
                    'has_exam' => true,
                ]
            );

            $weeklyPeriods = self::WEEKLY_PERIODS[$subject->id]
                ?? self::DEFAULT_WEEKLY_PERIODS;

            $grades = Grade::with('sections')
                ->whereIn('id', $data['grade_ids'])
                ->get();

            foreach ($grades as $grade) {
                foreach ($grade->sections as $section) {
                    SectionSubject::firstOrCreate(
                        [
                            'section_id' => $section->id,
                            'subject_id' => $subject->id,
                        ],
                        [
                            'weekly_periods' => $weeklyPeriods,
                        ]
                    );
                }
            }

            return $subject->load(
                'sectionSubjects.section.grade'
            );
        });
    }
}
