<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('term');
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->unique(
                ['academic_year_id', 'grade_id', 'term', 'exam_date', 'start_time'],
                'exam_schedules_grade_slot_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
