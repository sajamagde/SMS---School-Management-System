<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounts\CreateUserRequest;
use App\Services\Admin\Accounts\UserService;
use Exception;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{

    public function __construct(private UserService $user) {}

    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $result = $this->user->createUser($request->validated());

            return response()->json([
                'message' => 'User created successfully',
                'data'    => $result
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong while creating the user.',
                'error'   => config('app.debug') ? $e->getMessage() : 'Server Error'
            ], 500);
        }
    }
}
