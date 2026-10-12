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
        Schema::table('supervisors', function (Blueprint $table) {
         $table->foreign('grade_id')
                ->references('id')
                ->on('grades')
                ->restrictOnDelete();

            $table->unique('grade_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supervisors', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
            $table->dropUnique(['grade_id']);
        });
    }
};
