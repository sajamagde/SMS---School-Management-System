<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Hash;

class SupervisorSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->where('name', 'supervisor')->first();


        $supervisors = [

            [
                'first_name' => 'عمر',
                'last_name'  => 'علي',
                'phone'      => '0951111111',
                'grade_id'   => 1,
            ],


            [
                'first_name' => 'سارة',
                'last_name'  => 'حسن',
                'phone'      => '0951111112',
                'grade_id'   => 2,
            ],



            [
                'first_name' => 'لينا',
                'last_name'  => 'محمد',
                'phone'      => '0951111113',
                'grade_id'   => 3,
            ],
        ];

        foreach ($supervisors as $s) {

            $user = User::create([
                'first_name' => $s['first_name'],
                'last_name'  => $s['last_name'],
                'phone'      => $s['phone'],
                'code'       => $this->_generateUserCode('A'),
                'password'   => Hash::make('123456'),
                'role_id'    => $role->id,
            ]);

            $user->supervisor()->create([
                'grade_id' => $s['grade_id'],
            ]);
        }
    }

    private function _generateUserCode(string $prefix): string
    {
        $lastUser = User::query()
            ->where('code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if (!$lastUser) {
            return $prefix . '1001';
        }

        $lastNumber = (int) substr($lastUser->code, 1);

        return $prefix . ($lastNumber + 1);
    }
}
