<?php

namespace App\Services\Admin\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthService
{
    public function login(array $data)
    {

        $user = User::query()
            ->where('username', $data['username'])
            ->whereHas(
                'role',
                fn($query) => $query->where('name', 'super_admin')
            )
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Wrong username or password']

            ]);
        }
        $token = $user->createToken('admin-token')->plainTextToken;

        return [
            'id' => $user->id,
            'username' => $user->username,
            'token' => $token,
        ];
    }
    public function logoutFromAllDevices(User $user): void
    {
        $user->tokens()->delete();
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
