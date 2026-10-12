<?php

use App\Enums\UserStatus;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('username')->nullable()->unique(); //only for Admin :)

            $table->string('code')->unique();
            $table->string('password');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();

            $table->foreignId('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();

            $table->enum('status', array_column(
                UserStatus::cases(),
                'value'
            ))->default(UserStatus::ACTIVE->value);
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
