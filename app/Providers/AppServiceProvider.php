<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('ar');
        $this->loadMigrationsFrom([
            database_path('migrations'),
            database_path('migrations/Core'),
            database_path('migrations/Users'),
            database_path('migrations/Academic'),
            database_path('migrations/Announcements'),
            database_path('migrations/Schedules'),
            database_path('migrations/Alerts'),
            database_path('migrations/Attendances'),
            database_path('migrations/Grades'),
            database_path('migrations/Library'),

        ]);
    }
}
