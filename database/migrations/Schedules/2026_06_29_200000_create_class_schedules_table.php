<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('term');
            $table->unsignedTinyInteger('day_of_week');
            $table->unsignedTinyInteger('period_number');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();

            $table->unique(
                ['academic_year_id', 'section_id', 'term', 'day_of_week', 'period_number'],
                'class_schedules_section_slot_unique'
            );
            $table->unique(
                ['academic_year_id', 'teacher_id', 'term', 'day_of_week', 'period_number'],
                'class_schedules_teacher_slot_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};
