<?php
namespace App\Http\Controllers\Mobile\Parent;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentController extends Controller
{
   
    public function getMyChildren(Request $request): JsonResponse
    {
        $parent = $request->user()->parentProfile;

        if (!$parent) {
            return response()->json(['data' => []]);
        }

        $students = $parent->students()->with(['user', 'enrollments.section.grade'])->get();

        $data = $students->map(function ($student) {
            $activeEnrollment = $student->enrollments->where('status', 'active')->first();
            return [
                'student_id'   => $student->id,
                'full_name'    => $student->user->first_name . ' ' . $student->user->last_name,
                'grade_name'   => $activeEnrollment?->section?->grade?->name ?? 'غير محدد',
                'section_name' => $activeEnrollment?->section?->name ?? 'غير محدد',
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }
   public function search(Request $request): JsonResponse
{
    $validated = $request->validate([
        'search' => 'required|string|min:2|max:100',
    ]);

    $search = $validated['search'];

    $parents = ParentModel::query()
        ->with('user')
        ->whereHas('user', function ($query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhereRaw(
                    "CONCAT(first_name, ' ', last_name) LIKE ?",
                    ["%{$search}%"]
                );
        })
        ->limit(20)
        ->get();

    return response()->json([
        'success' => true,
        'data' => $parents->map(function ($parent) {
            return [
                'id' => $parent->id,
                'user_id' => $parent->user_id,
                'name' => trim(
                    $parent->user->first_name . ' ' .
                    $parent->user->last_name
                ),
            ];
        })->values(),
    ]);
}
}