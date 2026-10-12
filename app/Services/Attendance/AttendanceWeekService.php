<?php

namespace App\Services\Attendance;

use App\Models\AcademicYear;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceWeekService
{
    public function getWeeks(): array
    {
        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            throw ValidationException::withMessages([
                'academic_year_id' => 'لا توجد سنة دراسية نشطة حالياً في النظام.',
            ]);
        }

        $startDate = Carbon::parse($academicYear->start_date)->startOfDay();
        $endDate = Carbon::parse($academicYear->end_date)->startOfDay();

        $weeks = [];

        $currentStart = $startDate->copy();

        while ($currentStart->lte($endDate)) {

            $currentEnd = $currentStart->copy();

            if ($currentStart->dayOfWeek !== Carbon::SUNDAY) {
                $daysUntilThursday = Carbon::THURSDAY - $currentStart->dayOfWeek;

                if ($daysUntilThursday < 0) {
                    $daysUntilThursday += 7;
                }

                $currentEnd->addDays($daysUntilThursday);
            } else {
                $currentEnd->addDays(4);
            }

            if ($currentEnd->gt($endDate)) {
                $currentEnd = $endDate->copy();
            }
            $days = [];

            $currentDay = $currentStart->copy();

            while ($currentDay->lte($currentEnd)) {
                $days[] = [
                    'date' => $currentDay->format('Y-m-d'),
                    'day' => match ($currentDay->dayOfWeek) {
                        Carbon::SUNDAY => 'الأحد',
                        Carbon::MONDAY => 'الاثنين',
                        Carbon::TUESDAY => 'الثلاثاء',
                        Carbon::WEDNESDAY => 'الأربعاء',
                        Carbon::THURSDAY => 'الخميس',
                    },
                ];

                $currentDay->addDay();
            }
            $weeks[] = [
                'week' => count($weeks) + 1,
                'start_date' => $currentStart->format('Y-m-d'),
                'end_date' => $currentEnd->format('Y-m-d'),
                'days' => $days,
            ];

            $currentStart = $currentStart->copy();

            if ($currentStart->dayOfWeek === Carbon::SUNDAY) {
                $currentStart->addDays(7);
            } else {
                $currentStart->next(Carbon::SUNDAY);
            }
        }


        return [
            'academic_year' => [
                'id' => $academicYear->id,
                'name' => $academicYear->name,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'weeks' => $weeks,
        ];
    }
}
