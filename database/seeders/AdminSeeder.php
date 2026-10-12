<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::query()
        ->where('name', 'super_admin')->firstOrFail();

        User::create([
            'username' => 'admin',
            'code' => '00000',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone' => null,
            'password' => 'Admin@123',
            'role_id' => $role->id,
            'status' => UserStatus::ACTIVE,
        ]);
    }
}
