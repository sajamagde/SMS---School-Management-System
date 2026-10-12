<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Services\Academic\SectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function __construct(private SectionService $sectionService) {}

    public function getSectionsByGrade(Request $request)
    {
        $sections = $this->sectionService->getSectionsByGrade($request);

        return response()->json([
            'message' => 'تم جلب الشعب بنجاح',
            'data' => $sections,
        ]);
    }
    public function updateStatus(
        Request $request,
        int $sectionId
    ): JsonResponse {
        $validated = $request->validate([
            'is_open' => ['required', 'boolean'],
        ]);

        try {
            $section = $this->sectionService->updateStatus(
                $sectionId,
                $validated['is_open']
            );

            return response()->json([
                'success' => true,
                'message' => $validated['is_open']
                    ? 'تم فتح الشعبة بنجاح.'
                    : 'تم إغلاق الشعبة بنجاح.',
                'data' => [
                    'id' => $section->id,
                    'name' => $section->name,
                    'is_open' => $section->is_open,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
