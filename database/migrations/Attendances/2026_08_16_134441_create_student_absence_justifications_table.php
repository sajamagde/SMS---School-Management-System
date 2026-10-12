<?php

use App\Enums\JustificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_absence_justifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->date('absence_date');

            $table->foreignId('parent_id')
                ->constrained('parents')
                ->cascadeOnDelete();

            $table->text('reason');

            $table->string('attachment')->nullable();

            $table->string('status')
                ->default(JustificationStatus::PENDING->value);

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->unique([
                'student_id',
                'absence_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_absence_justifications');
    }
};