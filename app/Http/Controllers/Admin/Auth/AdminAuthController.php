<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginAdminRequest;
use App\Services\Admin\Auth\AdminAuthService;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    public function __construct(
        private AdminAuthService $admin
    ) {}

    public function login(LoginAdminRequest $request)
    {
        $result = $this->admin->login($request->validated());
        return response()->json([

            'message' => 'Login successful',
            'data' => $result,
        ]);
    }
    public function logoutFromAllDevices(Request $request)
    {
        $this->admin->logoutFromAllDevices($request->user());
        return response()->json([
            'message' => 'Logout From All Devices successfully',
        ],200);
    }

    public function logout(Request $request)
    {
        $this->admin->logout($request->user());
        return response()->json([
            'message' => 'Logout From Current Device successfully',
        ],200);
    }
}
