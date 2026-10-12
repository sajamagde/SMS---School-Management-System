<?php

namespace App\Services\Schedules;

use App\Models\AcademicYear;
use Illuminate\Validation\ValidationException;

abstract class BaseScheduleService
{
    private const PERIODS_PER_DAY = 7;
    protected function getActiveAcademicYearId(): int
    {

        $year = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        if (! $year) {
            throw ValidationException::withMessages([
                'academic_year_id' => 'لا توجد سنة دراسية نشطة حالياً في النظام.',
            ]);
        }

        return $year->id;
    }

    protected function applyClassPeriodTimes(array $data): array
    {
        $periodTimes = config("schedule_times.periods.{$data['period_number']}");

        if (! $periodTimes) {
            throw ValidationException::withMessages([
                'period_number' => 'لا توجد أوقات معرفة لهذه الحصة.',
            ]);
        }

        $data['start_time'] = $periodTimes['start'];
        $data['end_time'] = $periodTimes['end'];

        return $data;
    }
}
