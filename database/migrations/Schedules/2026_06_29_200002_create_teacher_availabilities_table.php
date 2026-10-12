<?php

use App\Enums\TeacherAvailabilityStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->unsignedTinyInteger('period_number');
            $table->unsignedTinyInteger('availability_status')
                ->default(TeacherAvailabilityStatus::AVAILABLE->value);
            $table->timestamps();

            $table->unique(
                ['teacher_id', 'day_of_week', 'period_number'],
                'teacher_availabilities_slot_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_availabilities');
    }
};
