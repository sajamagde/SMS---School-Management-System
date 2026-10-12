<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Library\LibraryAcademicYearRequest;
use App\Http\Requests\Library\SaveBookDistributionRequest;
use App\Http\Requests\Library\SaveBookReturnRequest;
use App\Http\Resources\Library\LibraryDistributionStudentResource;
use App\Http\Resources\Library\LibraryReturnStudentResource;
use App\Http\Resources\Library\LibrarySectionResource;
use App\Http\Resources\Library\LibraryStudentResource;
use App\Models\Section;
use App\Services\Library\LibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function __construct(
        private readonly LibraryService $libraryService
    ) {
    }

    public function getClasses(LibraryAcademicYearRequest $request)
    {
        $this->authorize('viewAny', Section::class);

        $sections = $this->libraryService->getClasses(
            $request->integer('academic_year_id')
        );

        return LibrarySectionResource::collection($sections);
    }

    public function getSectionStudents(
        LibraryAcademicYearRequest $request,
        Section $section
    ) {
        $this->authorize('view', $section);

        $students = $this->libraryService->getSectionStudents(
            $section,
            $request->integer('academic_year_id')
        );

        return LibraryStudentResource::collection($students);
    }

    public function getDistribution(
        LibraryAcademicYearRequest $request,
        Section $section
    ) {
        $this->authorize('view', $section);

        $result = $this->libraryService->getDistribution(
            $section,
            $request->integer('academic_year_id')
        );

        return response()->json([
            'data' => [
                'new_books' => $result['new_books'],
                'used_books' => $result['used_books'],
                'students' => LibraryDistributionStudentResource::collection(
                    $result['students']
                ),
            ],
        ]);
    }

    public function saveDistribution(
        SaveBookDistributionRequest $request,
        Section $section
    ): JsonResponse {
        $this->authorize('update', $section);

        $this->libraryService->saveDistribution(
            $section,
            $request->integer('academic_year_id'),
            $request->input('students')
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ توزيع الكتب بنجاح.',
        ]);
    }

    public function getReturns(
        LibraryAcademicYearRequest $request,
        Section $section
    ) {
        $this->authorize('view', $section);

        $students = $this->libraryService->getReturns(
            $section,
            $request->integer('academic_year_id')
        );

        return response()->json([
            'data' => LibraryReturnStudentResource::collection($students),
        ]);
    }

    public function saveReturns(
        SaveBookReturnRequest $request,
        Section $section
    ): JsonResponse {
        $this->authorize('update', $section);

        $this->libraryService->saveReturns(
            $section,
            $request->integer('academic_year_id'),
            $request->input('students')
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ سحب الكتب بنجاح.',
        ]);
    }
}