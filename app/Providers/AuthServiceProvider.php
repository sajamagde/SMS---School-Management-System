<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\ClassSchedule;
use App\Models\ExamSchedule;
use App\Models\Section;
use App\Policies\AcademicYearPolicy;
use App\Policies\AnnouncementPolicy;
use App\Policies\ClassSchedulePolicy;
use App\Policies\ExamSchedulePolicy;
use App\Policies\LibraryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Announcement::class => AnnouncementPolicy::class,
        ClassSchedule::class => ClassSchedulePolicy::class,
        ExamSchedule::class => ExamSchedulePolicy::class,
        AcademicYear::class => AcademicYearPolicy::class,
        Section::class => LibraryPolicy::class,
    ];
}
