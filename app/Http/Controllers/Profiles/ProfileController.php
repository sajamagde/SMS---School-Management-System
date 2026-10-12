<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Resources\Profiles\ProfileResource;
use App\Services\Profiles\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    public function supervisor(Request $request): JsonResponse
    {
        $profile = $this->profileService->getSupervisorProfile(
            $request->user()
        );

        return $this->successResponse($profile);
    }

    public function teacher(Request $request): JsonResponse
    {
        $profile = $this->profileService->getTeacherProfile(
            $request->user()
        );

        return $this->successResponse($profile);
    }

    public function student(Request $request): JsonResponse
    {
        $profile = $this->profileService->getStudentProfile(
            $request->user()
        );

        return $this->successResponse($profile);
    }

    public function parent(Request $request): JsonResponse
    {
        $studentId = $request->header('X-Student-Id');

        if (!$studentId) {
            return response()->json([
                'success' => false,
                'message' => 'X-Student-Id مطلوب.',
            ], 422);
        }

        if (!is_numeric($studentId)) {
            return response()->json([
                'success' => false,
                'message' => 'X-Student-Id غير صالح.',
            ], 422);
        }

        $profile = $this->profileService->getParentProfile(
            user: $request->user(),
            studentId: (int) $studentId,
        );

        return $this->successResponse($profile);
    }
    public function librarian(Request $request): JsonResponse
    {
        $profile = $this->profileService->getLibrarianProfile(
            $request->user()
        );

        return $this->successResponse($profile);
    }

    private function successResponse(array $profile): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'تم جلب الملف الشخصي بنجاح.',
            'data' => new ProfileResource($profile),
        ]);
    }
}
