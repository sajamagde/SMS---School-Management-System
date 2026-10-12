<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Student;
use Symfony\Component\HttpFoundation\Response;

class EnsureParentChildContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isParent()) {

            $parentProfile = $user->parentProfile;

            if ($parentProfile) {

                $studentId = $request->header('X-Student-Id')
                    ?? $request->input('student_id');

                $selectedStudent = Student::query()
                    ->where('parent_id', $parentProfile->id)
                    ->when($studentId, function ($query) use ($studentId) {
                        $query->where('id', $studentId);
                    })
                    ->first();

                if (!$selectedStudent) {
                    return response()->json([
                        'success' => false,
                        'message' => 'عذراً، لا يوجد طالب مرتبط بحسابك.'
                    ], 403);
                }

                $request->attributes->set('selected_student', $selectedStudent);
            }
        }

        return $next($request);
    }
}
