<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\SectionSubject;
use App\Models\TeacherAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClassScheduleSeeder extends Seeder
{
    private const DAYS = [0, 1, 2, 3, 4];

    private const PERIODS_PER_DAY = 7;

    private const TERM = 1;

    public function run(): void
    {
        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            throw new RuntimeException(
                'لا توجد سنة دراسية نشطة. قم بتشغيل AcademicYearSeeder أولاً.'
            );
        }

        ClassSchedule::query()
            ->where('academic_year_id', $academicYear->id)
            ->where('term', self::TERM)
            ->delete();

        DB::transaction(function () use ($academicYear) {

            $sectionSubjects = SectionSubject::query()
                ->orderBy('section_id', 'asc')
                ->get();

            $assignments = TeacherAssignment::query()
                ->get();

            /*
            |--------------------------------------------------------------------------
            | بناء بيانات الجدولة
            |--------------------------------------------------------------------------
            |
            | لكل شعبة:
            |
            | teacher_id + subject_id + عدد الحصص
            |
            */

            $sectionLessons = [];

            foreach ($sectionSubjects as $sectionSubject) {

                $sectionId = $sectionSubject->section_id;
                $subjectId = $sectionSubject->subject_id;
                $weeklyPeriods = (int) $sectionSubject->weekly_periods;

                $assignment = $assignments
                    ->first(function ($assignment) use (
                        $sectionId,
                        $subjectId
                    ) {
                        return
                            $assignment->section_id === $sectionId &&
                            $assignment->subject_id === $subjectId;
                    });

                if (! $assignment) {
                    throw new RuntimeException(
                        "لا يوجد تكليف للمادة {$subjectId} للشعبة {$sectionId}."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | نضيف كل حصة كعنصر مستقل.
                |--------------------------------------------------------------------------
                */

                for ($i = 0; $i < $weeklyPeriods; $i++) {

                    $sectionLessons[$sectionId][] = [
                        'subject_id' => $subjectId,
                        'teacher_id' => $assignment->teacher_id,
                    ];
                }
            }

            /*
            |--------------------------------------------------------------------------
            | التحقق من عدد حصص كل شعبة
            |--------------------------------------------------------------------------
            |
            | بما أن لدينا:
            |
            | 5 أيام × 7 حصص = 35
            |
            | يجب أن يكون مجموع حصص كل شعبة 35.
            |
            */

            foreach ($sectionLessons as $sectionId => $lessons) {

                $total = count($lessons);

                if ($total !== count(self::DAYS) * self::PERIODS_PER_DAY) {
                    throw new RuntimeException(
                        "عدد حصص الشعبة {$sectionId} هو {$total}، " .
                            'بينما العدد المطلوب أسبوعيًا هو 35.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | حساب حمل كل معلم
            |--------------------------------------------------------------------------
            |
            | نستخدمه لاختيار المعلمين أصحاب الحمل الأكبر أولًا
            | عندما نبحث عن المطابقة.
            |--------------------------------------------------------------------------
            */

            $teacherLoads = [];

            foreach ($sectionLessons as $lessons) {

                foreach ($lessons as $lesson) {

                    $teacherId = $lesson['teacher_id'];

                    $teacherLoads[$teacherId] =
                        ($teacherLoads[$teacherId] ?? 0) + 1;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | الجدولة
            |--------------------------------------------------------------------------
            |
            | في كل خانة:
            |
            | - كل شعبة يجب أن تحصل على حصة.
            | - المعلم يجب ألا يكون مستخدمًا أكثر من مرة.
            |
            */

            foreach (self::DAYS as $day) {

                for (
                    $period = 1;
                    $period <= self::PERIODS_PER_DAY;
                    $period++
                ) {

                    $usedTeachers = [];

                    /*
                    |--------------------------------------------------------------------------
                    | نرتب الشعب بحيث نبدأ بالشعبة التي خياراتها أقل.
                    |--------------------------------------------------------------------------
                    */

                    $sections = array_keys($sectionLessons);

                    usort(
                        $sections,
                        function ($sectionA, $sectionB) use (
                            $sectionLessons,
                            $usedTeachers
                        ) {

                            $teachersA = collect($sectionLessons[$sectionA])
                                ->pluck('teacher_id')
                                ->unique()
                                ->count();

                            $teachersB = collect($sectionLessons[$sectionB])
                                ->pluck('teacher_id')
                                ->unique()
                                ->count();

                            return $teachersA <=> $teachersB;
                        }
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | نبحث عن مطابقة صحيحة لكل الشعب.
                    |--------------------------------------------------------------------------
                    */

                    $matching = $this->findMatching(
                        $sections,
                        $sectionLessons,
                        $usedTeachers,
                        $teacherLoads
                    );

                    if ($matching === null) {

                        throw new RuntimeException(
                            "تعذر إنشاء مطابقة صحيحة لليوم {$day} " .
                                "والحصة {$period}."
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | بعد إيجاد المطابقة، ننشئ الحصص.
                    |--------------------------------------------------------------------------
                    */

                    foreach ($matching as $sectionId => $lessonIndex) {

                        $lesson = $sectionLessons[$sectionId][$lessonIndex];

                        $this->createSchedule(
                            $academicYear->id,
                            $sectionId,
                            $lesson['subject_id'],
                            $lesson['teacher_id'],
                            $day,
                            $period
                        );

                        /*
                        | نحذف الحصة التي تمت جدولتها من قائمة الحصص
                        | الخاصة بالشعبة.
                        */

                        unset(
                            $sectionLessons[$sectionId][$lessonIndex]
                        );

                        $sectionLessons[$sectionId] = array_values(
                            $sectionLessons[$sectionId]
                        );

                        /*
                        | تحديث حمل المعلم المتبقي.
                        */

                        $teacherId = $lesson['teacher_id'];

                        $teacherLoads[$teacherId]--;
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | التأكد النهائي
            |--------------------------------------------------------------------------
            */

            foreach ($sectionLessons as $sectionId => $lessons) {

                if (count($lessons) !== 0) {

                    throw new RuntimeException(
                        "تبقى " .
                            count($lessons) .
                            " حصص غير مجدولة للشعبة {$sectionId}."
                    );
                }
            }
        });
    }

    /**
     * إيجاد مطابقة بين الشعب والمعلمين.
     *
     * كل شعبة يجب أن تأخذ حصة واحدة،
     * وكل معلم يمكن أن يظهر مرة واحدة فقط في الخانة.
     */
    private function findMatching(
        array $sections,
        array $sectionLessons,
        array $usedTeachers,
        array $teacherLoads
    ): ?array {

        $matching = [];

        return $this->matchSections(
            $sections,
            0,
            $sectionLessons,
            $usedTeachers,
            $teacherLoads,
            $matching
        );
    }

    /**
     * Backtracking بسيط للمطابقة.
     *
     * عدد الشعب عندك 9 فقط، لذلك هذا الحل مناسب
     * ولا يحتاج إلى مكتبة أو خوارزمية معقدة.
     */
    private function matchSections(
        array $sections,
        int $index,
        array $sectionLessons,
        array $usedTeachers,
        array $teacherLoads,
        array &$matching
    ): ?array {

        if ($index >= count($sections)) {
            return $matching;
        }

        $sectionId = $sections[$index];

        /*
        |--------------------------------------------------------------------------
        | الحصص المتاحة لهذه الشعبة.
        |--------------------------------------------------------------------------
        */

        $candidates = [];

        foreach ($sectionLessons[$sectionId] as $lessonIndex => $lesson) {

            $teacherId = $lesson['teacher_id'];

            /*
            | المعلم مستخدم في نفس الخانة.
            */

            if (isset($usedTeachers[$teacherId])) {
                continue;
            }

            /*
            | يجب أن يكون لديه حصص متبقية.
            */

            if (($teacherLoads[$teacherId] ?? 0) <= 0) {
                continue;
            }

            $candidates[] = [
                'index' => $lessonIndex,
                'teacher_id' => $teacherId,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | نبدأ بالمعلمين أصحاب الحمل الأكبر.
        |
        | هذا يساعد على تفريغ المعلمين الأكثر ازدحامًا
        | وعدم تركهم للنهاية.
        |--------------------------------------------------------------------------
        */

        usort(
            $candidates,
            function ($a, $b) use ($teacherLoads) {

                return ($teacherLoads[$b['teacher_id']] ?? 0)
                    <=>
                    ($teacherLoads[$a['teacher_id']] ?? 0);
            }
        );

        foreach ($candidates as $candidate) {

            $lessonIndex = $candidate['index'];
            $teacherId = $candidate['teacher_id'];

            $matching[$sectionId] = $lessonIndex;

            $usedTeachers[$teacherId] = true;

            $teacherLoads[$teacherId]--;

            $result = $this->matchSections(
                $sections,
                $index + 1,
                $sectionLessons,
                $usedTeachers,
                $teacherLoads,
                $matching
            );

            if ($result !== null) {
                return $result;
            }

            /*
            |--------------------------------------------------------------------------
            | إذا فشل الاختيار، نرجع عنه.
            |--------------------------------------------------------------------------
            */

            unset($matching[$sectionId]);

            unset($usedTeachers[$teacherId]);

            $teacherLoads[$teacherId]++;
        }

        return null;
    }

    /**
     * إنشاء الحصة.
     */
    private function createSchedule(
        int $academicYearId,
        int $sectionId,
        int $subjectId,
        int $teacherId,
        int $day,
        int $period
    ): void {

        $times = config("schedule_times.periods.{$period}");

        if (! $times) {
            throw new RuntimeException(
                "لا توجد إعدادات زمنية للحصة رقم {$period}."
            );
        }

        ClassSchedule::create([
            'academic_year_id' => $academicYearId,
            'section_id' => $sectionId,
            'subject_id' => $subjectId,
            'teacher_id' => $teacherId,
            'term' => self::TERM,
            'day_of_week' => $day,
            'period_number' => $period,
            'start_time' => $times['start'],
            'end_time' => $times['end'],
        ]);
    }
}
