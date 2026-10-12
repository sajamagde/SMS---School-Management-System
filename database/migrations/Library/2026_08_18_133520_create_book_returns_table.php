<?php

use App\Enums\BookReturnCondition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('academic_year_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum(
                'condition',
                array_column(
                    BookReturnCondition::cases(),
                    'value'
                )
            );

            $table->timestamps();

            $table->unique(
                ['student_id', 'academic_year_id'],
                'book_return_student_year_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_returns');
    }
};