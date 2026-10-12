<?php

namespace App\Http\Controllers\Announcements;

use App\Http\Controllers\Controller;
use App\Services\Announcements\AnnouncementService;
use App\Http\Requests\Announcements\StoreAnnouncementRequest;
use App\Http\Requests\Announcements\UpdateAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected AnnouncementService $announcementService) {}

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user->isParent()) {
            /** @var \App\Models\Student|null $selectedStudent */
            $selectedStudent = $request->attributes->get('selected_student');

            $announcements = $this->announcementService->getParentAnnouncements($selectedStudent?->id);
        } else {
            $announcements = match ($user->role?->name) {
                'super_admin' => $this->announcementService->getAdminAnnouncements(),
                'supervisor'  => $this->announcementService->getSupervisorAnnouncements($user),
                'student'     => $this->announcementService->getStudentAnnouncements($user),
                'teacher'     => $this->announcementService->getTeacherAnnouncements($user),
                default       => collect([]),
            };
        }

        return response()->json([
            'success' => true,
            'data'    => AnnouncementResource::collection($announcements)
        ]);
    }


    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $this->authorize('create', Announcement::class);

        $announcement = $this->announcementService->createAnnouncement(
            $request->validated(),
            Auth::user()
        );

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الإعلان بنجاح.',
            'data'    => new AnnouncementResource($announcement)
        ], 201);
    }


    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): JsonResponse
    {
        $this->authorize('update', $announcement);

        $updatedAnnouncement = $this->announcementService->updateAnnouncement(
            $announcement,
            $request->validated(),
            Auth::user()
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الإعلان بنجاح.',
            'data'    => new AnnouncementResource($updatedAnnouncement)
        ]);
    }


    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->authorize('delete', $announcement);

        $this->announcementService->deleteAnnouncement($announcement);

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإعلان بنجاح.'
        ]);
    }
    public function togglePin(Announcement $announcement): JsonResponse
    {
        $this->authorize('togglePin', $announcement);

        $updatedAnnouncement = $this->announcementService->togglePinAnnouncement($announcement, Auth::user());

        return response()->json([
            'success' => true,
            'message' => $updatedAnnouncement->is_pinned ? 'تم تثبيت الإعلان بنجاح.' : 'تم فك تثبيت الإعلان بنجاح.',
            'data'    => new AnnouncementResource($updatedAnnouncement)
        ]);
    }
}
