<?php

namespace App\Services\Mobile\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class MobileAuthService
{
    public function login(array $data)
    {
        $user = User::query()
            ->where('code', $data['code'])
            ->whereHas('role', fn($query) => $query->where('name', '!=', 'super_admin'))
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'code' => ['Wrong code or password']

            ]);
        }
        $user->load('role');

        $token = $user->createToken('user-token')->plainTextToken;



        return [
            'id' => $user->id,
            'code' => $user->code,
            'role' => $user->role->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'token' => $token,
        ];
    }
}
