<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreAcademicYearRequest;
use App\Http\Requests\Academic\UpdateAcademicYearRequest;
use App\Http\Resources\AcademicYearResource;
use App\Models\AcademicYear;
use App\Services\Academic\AcademicYearService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AcademicYearService $service
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', AcademicYear::class);

        $academicYears = AcademicYear::query()
            ->with('terms')
            ->orderByDesc('start_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => AcademicYearResource::collection($academicYears),
        ]);
    }

    public function active(): JsonResponse
    {
        $this->authorize('viewActive', AcademicYear::class);

        $academicYear = $this->service->getActive();

        return response()->json([
            'success' => true,
            'data' => new AcademicYearResource($academicYear),
        ]);
    }


    public function currentTerm(): JsonResponse
    {
        $this->authorize('viewActive', AcademicYear::class);

        $term = $this->service->getCurrentTerm();

        return response()->json([
            'success' => true,
            'data' => $term,
        ]);
    }

  
    public function store(StoreAcademicYearRequest $request): JsonResponse
    {
        
        $this->authorize('create', AcademicYear::class);

        $academicYear = $this->service->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء السنة الدراسية بنجاح.',
            'data' => new AcademicYearResource($academicYear),
        ], 201);
    }

    public function update(
        UpdateAcademicYearRequest $request,
        int $id
    ): JsonResponse {
        $this->authorize('update', AcademicYear::class);

        $academicYear = $this->service->update(
            $id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث السنة الدراسية بنجاح.',
            'data' => new AcademicYearResource($academicYear),
        ]);
    }

    public function activate(int $id): JsonResponse
    {
        $this->authorize('activate', AcademicYear::class);

        $academicYear = $this->service->activate($id);

        return response()->json([
            'success' => true,
            'message' => 'تم تفعيل السنة الدراسية بنجاح.',
            'data' => new AcademicYearResource($academicYear),
        ]);
    }

    public function deactivate(int $id): JsonResponse
    {
        $this->authorize('deactivate', AcademicYear::class);

        $academicYear = $this->service->deactivate($id);

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء تفعيل السنة الدراسية بنجاح.',
            'data' => new AcademicYearResource($academicYear),
        ]);
    }

   
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', AcademicYear::class);

        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'تم حذف السنة الدراسية بنجاح.',
        ]);
    }
}
