<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceWeekService;
use Illuminate\Http\JsonResponse;

class AttendanceWeekController extends Controller
{
    public function __construct(
        protected AttendanceWeekService $service
    ) {}

    public function index(): JsonResponse
    {
        $weeks = $this->service->getWeeks();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب أسابيع السنة الدراسية بنجاح.',
            'data' => $weeks,
        ]);
    }
}