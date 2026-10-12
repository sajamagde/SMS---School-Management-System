<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Librarian;
use Illuminate\Support\Facades\Hash;

class LibrarianSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->where('name', 'librarian')->first();

        $librarians = [
            [
                'first_name' => 'خالد',
                'last_name'  => 'احمد',
                'phone'      => '0961111111',
            ],
            [
                'first_name' => 'منى',
                'last_name'  => 'سامي',
                'phone'      => '0961111112',
            ],
        ];

        foreach ($librarians as $l) {

            $user = User::create([
                'first_name' => $l['first_name'],
                'last_name'  => $l['last_name'],
                'phone'      => $l['phone'],
                'code'       => $this->_generateUserCode('L'),
                'password'   => Hash::make('123456'),
                'role_id'    => $role->id,
            ]);

            $user->librarian()->create([]);
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