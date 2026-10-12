<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unique('user_id');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->unique('user_id');
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->unique('user_id');
        });

        Schema::table('supervisors', function (Blueprint $table) {
            $table->unique('user_id');
        });

        Schema::table('librarians', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        Schema::table('supervisors', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        Schema::table('librarians', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};