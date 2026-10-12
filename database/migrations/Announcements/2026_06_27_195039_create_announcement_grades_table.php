<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcement_grades', function (Blueprint $table) {

            $table->foreignId('announcement_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('grade_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->primary([
                'announcement_id',
                'grade_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_grades');
    }
};
