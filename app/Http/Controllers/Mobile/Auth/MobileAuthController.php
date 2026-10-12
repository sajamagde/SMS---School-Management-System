<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\Auth\LoginMobileRequest;
use App\Services\Mobile\Auth\MobileAuthService;

class MobileAuthController extends Controller
{
    public function __construct(
        private MobileAuthService $moblie_auth
    ) {}
    public function login(LoginMobileRequest $request){
      
        $result = $this->moblie_auth->login($request->validated());
        return response()->json([

            'message' => 'Login successful',
            'data' => $result,
        ]);
    }
    }
