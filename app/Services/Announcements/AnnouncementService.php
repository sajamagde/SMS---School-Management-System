<?php

namespace App\Services\Announcements;

use App\Enums\AnnouncementTarget;
use App\Models\Announcement;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Supervisor;
use App\Models\TeacherAssignment;
use App\Services\Context\ParentContextService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnnouncementService
{

    public function createAnnouncement(array $data, $user): Announcement
    {
        $this->_validateAdminRestrictions($data, $user);
        $this->_validateTeacherRestrictions($data, $user);

        return DB::transaction(function () use ($data, $user) {
            $targetAudience = $data['target_audience'] ?? [AnnouncementTarget::ALL->value];

            if ($user->isTeacher()) {
                $targetAudience = [AnnouncementTarget::STUDENTS->value];
            } else {
                $targetAudience = !empty($data['target_audience'])
                    ? $data['target_audience']
                    : [AnnouncementTarget::ALL->value];
            }

            $announcement = Announcement::create([
                'user_id'         => $user->id,
                'title'           => $data['title'],
                'content'         => $data['content'],
                'target_audience' => $targetAudience,
                'is_pinned'       => $data['is_pinned'] ?? false,
                'expires_at'      => $data['expires_at'] ?? null,
            ]);

            $announcement->grades()->sync($data['grade_ids'] ?? []);
            $announcement->sections()->sync($data['section_ids'] ?? []);

            return $announcement;
        });
    }

    public function updateAnnouncement(Announcement $announcement, array $data, $user): Announcement
    {
        $this->_validateAdminRestrictions($data, $user);
        $this->_validateTeacherRestrictions($data, $user);

        return DB::transaction(function () use ($announcement, $data, $user) {
            $targetAudience = $data['target_audience'] ?? $announcement->target_audience;

            if ($user->role->name === 'teacher') {
                $targetAudience = [AnnouncementTarget::STUDENTS->value];
            }

            $announcement->update([
                'title'           => $data['title'] ?? $announcement->title,
                'content'         => $data['content'] ?? $announcement->content,
                'target_audience' => $targetAudience,
                'is_pinned'       => array_key_exists('is_pinned', $data) ? $data['is_pinned'] : $announcement->is_pinned,
                'expires_at'      => array_key_exists('expires_at', $data) ? $data['expires_at'] : $announcement->expires_at,
            ]);

            if (array_key_exists('grade_ids', $data)) {
                $announcement->grades()->sync($data['grade_ids'] ?? []);
            }

            if (array_key_exists('section_ids', $data)) {
                $announcement->sections()->sync($data['section_ids'] ?? []);
            }

            return $announcement;
        });
    }

    public function deleteAnnouncement(Announcement $announcement): bool
    {
        return (bool) $announcement->forceDelete();
    }

    public function togglePinAnnouncement(Announcement $announcement, $user): Announcement
    {
        $announcement->is_pinned = !$announcement->is_pinned;
        $announcement->save();

        return $announcement;
    }

    public function getAdminAnnouncements()
    {
        return Announcement::with(['user.role', 'grades', 'sections'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();
    }
    public function getStudentAnnouncements($user)
    {
        $student = $user->student;

        if (!$student) {
            return collect([]);
        }

        $activeEnrollment = $student->enrollments()
            ->where('status', 'active')
            ->with('section')
            ->first();

        $section = $activeEnrollment?->section;

        if (!$section) {
            return collect([]);
        }

        $sectionId = $section->id;
        $gradeId = $section->grade_id;

        return $this->_baseAnnouncementQuery()
            ->where(function ($query) use ($gradeId, $sectionId) {

                $query->where(function ($sub) {
                    $sub->whereJsonContains(
                        'target_audience',
                        AnnouncementTarget::ALL->value
                    )
                        ->orWhereJsonContains(
                            'target_audience',
                            AnnouncementTarget::STUDENTS->value
                        );
                });

                $query->where(function ($sub) use ($gradeId, $sectionId) {

                    $sub->where(function ($q) use ($gradeId, $sectionId) {
                        $q->whereHas(
                            'grades',
                            fn($g) => $g->where('grades.id', $gradeId)
                        )
                            ->whereHas(
                                'sections',
                                fn($s) => $s->where('sections.id', $sectionId)
                            );
                    })

                        ->orWhere(function ($q) use ($gradeId) {
                            $q->whereHas(
                                'grades',
                                fn($g) => $g->where('grades.id', $gradeId)
                            )
                                ->doesntHave('sections');
                        })

                        ->orWhere(function ($q) use ($sectionId) {
                            $q->whereHas(
                                'sections',
                                fn($s) => $s->where('sections.id', $sectionId)
                            );
                        })

                        ->orWhere(function ($q) {
                            $q->doesntHave('grades')
                                ->doesntHave('sections');
                        });
                });
            })
            ->get();
    }
    public function getSupervisorAnnouncements($user)
    {
        $assignedGradeIds = $user->supervisors ? $user->supervisors->pluck('grade_id')->toArray() : [];

        if (empty($assignedGradeIds)) {
            return collect([]);
        }

        return $this->_baseAnnouncementQuery()
            ->where(function ($query) use ($assignedGradeIds) {
                $query->whereJsonContains('target_audience', AnnouncementTarget::ALL->value)
                    ->orWhereJsonContains('target_audience', AnnouncementTarget::SUPERVISORS->value)
                    ->orWhereHas('grades', function ($q) use ($assignedGradeIds) {
                        $q->whereIn('grades.id', $assignedGradeIds);
                    })
                    ->orWhere(function ($q) use ($assignedGradeIds) {
                        foreach ($assignedGradeIds as $id) {
                            $q->orWhereJsonContains('grade_ids', (int)$id)
                                ->orWhereJsonContains('grade_ids', (string)$id);
                        }
                    });
            })
            ->get();
    }



    public function getParentAnnouncements(?int $studentId = null)
    {
        $gradeId = null;
        $sectionId = null;

        if ($studentId) {
            $enrollment = StudentEnrollment::query()
                ->where('student_id', $studentId)
                ->where('status', 'active')
                ->with('section')
                ->first();

            if ($enrollment) {
                $gradeId   = $enrollment->section->grade_id;
                $sectionId = $enrollment->section_id;
            }
        }

        return $this->_baseAnnouncementQuery()
            ->where(function ($query) use ($gradeId, $sectionId) {

                $query->where(function ($q) {
                    $q->whereJsonContains('target_audience', AnnouncementTarget::ALL->value)
                        ->orWhereJsonContains('target_audience', AnnouncementTarget::PARENTS->value)
                        ->doesntHave('grades')
                        ->doesntHave('sections');
                });

                if ($gradeId) {
                    $query->orWhere(function ($q) use ($gradeId, $sectionId) {
                        $q->where(function ($sub) {
                            $sub->whereJsonContains('target_audience', AnnouncementTarget::ALL->value)
                                ->orWhereJsonContains('target_audience', AnnouncementTarget::PARENTS->value)
                                ->orWhereJsonContains('target_audience', AnnouncementTarget::STUDENTS->value);
                        })
                            ->where(function ($sub) use ($gradeId) {
                                $sub->whereDoesntHave('grades')
                                    ->orWhereHas('grades', fn($g) => $g->where('grades.id', $gradeId));
                            })
                            ->where(function ($sub) use ($sectionId) {
                                $sub->whereDoesntHave('sections')
                                    ->orWhereHas('sections', fn($s) => $s->where('sections.id', $sectionId));
                            });
                    });
                }
            })
            ->latest()
            ->get();
    }
    public function getTeacherAnnouncements($user)
    {
        $teacher = $user->teacher;

        $assignedSectionIds = $teacher
            ? $teacher->assignments
            ->pluck('section_id')
            ->unique()
            ->toArray()
            : [];

        $assignedGradeIds = Section::query()
            ->whereIn('id', $assignedSectionIds, 'and', false)
            ->pluck('grade_id')
            ->unique()
            ->toArray();

        return $this->_baseAnnouncementQuery()
            ->where(function ($query) use ($user, $assignedGradeIds) {

                $query->where(function ($q) use ($user, $assignedGradeIds) {
                    $q->where('user_id', $user->id)
                        ->where(function ($sub) use ($assignedGradeIds) {
                            $sub->whereDoesntHave('grades')
                                ->orWhereHas(
                                    'grades',
                                    fn($g) => $g->whereIn(
                                        'grades.id',
                                        $assignedGradeIds
                                    )
                                );
                        });
                })

                    ->orWhere(function ($q) use ($assignedGradeIds) {
                        $q->where(function ($sub) {
                            $sub->whereJsonContains(
                                'target_audience',
                                AnnouncementTarget::ALL->value
                            )
                                ->orWhereJsonContains(
                                    'target_audience',
                                    AnnouncementTarget::TEACHERS->value
                                );
                        })
                            ->where(function ($sub) use ($assignedGradeIds) {
                                $sub->whereDoesntHave('grades')
                                    ->orWhereHas(
                                        'grades',
                                        fn($g) => $g->whereIn(
                                            'grades.id',
                                            $assignedGradeIds
                                        )
                                    );
                            });
                    });
            })
            ->get();
    }

    private function _baseAnnouncementQuery()
    {
        return Announcement::with(['user.role', 'grades', 'sections'])
            ->notExpired()
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');
    }

    private function _validateAdminRestrictions(array $data, $user): void
    {
        if ($user->role->name !== 'super_admin') {
            return;
        }

        if (!empty($data['grade_ids']) || !empty($data['section_ids'])) {
            throw ValidationException::withMessages([
                'target_audience' => 'المدير يمكنه إرسال إعلانات عامة فقط للفئات، ولا يمكنه تخصيص صفوف أو شعب.'
            ]);
        }
    }

    private function _validateTeacherRestrictions(array $data, $user): void
    {
        if ($user->role->name !== 'teacher') {
            return;
        }

        if (empty($data['grade_ids']) || !is_array($data['grade_ids'])) {
            throw ValidationException::withMessages([
                'grade_ids' =>
                'يجب على المعلم تحديد صف دراسي واحد على الأقل لنشر الإعلان.'
            ]);
        }



        $assignedSectionIds = $user->teacher
            ? $user->teacher
            ->assignments()
            ->pluck('section_id')
            ->toArray()
            : [];


        $assignedGradeIds = Section::query()
            ->where(function ($query) use ($assignedSectionIds) {
                foreach ($assignedSectionIds as $sectionId) {
                    $query->orWhere('id', $sectionId);
                }
            })
            ->pluck('grade_id')
            ->unique()
            ->toArray();


        foreach ($data['grade_ids'] as $gradeId) {

            if (!in_array($gradeId, $assignedGradeIds)) {

                $grade = Grade::query()->find($gradeId);

                $gradeName = $grade
                    ? $grade->name
                    : $gradeId;

                throw ValidationException::withMessages([
                    'grade_ids' =>
                    "عذراً، لا يمكنك النشر في ($gradeName) لأنك غير مكلف بتدريس أي شعبة فيه."
                ]);
            }
        }

        if (!empty($data['section_ids']) && is_array($data['section_ids'])) {

            foreach ($data['section_ids'] as $sectionId) {


                if (!in_array($sectionId, $assignedSectionIds)) {

                    $section = Section::query()
                        ->with('grade')
                        ->find($sectionId);

                    $sectionName = $section?->name ?? $sectionId;

                    throw ValidationException::withMessages([
                        'section_ids' =>
                        "عذراً، لا يمكنك النشر في الشعبة ($sectionName) لأنك غير مكلف بها."
                    ]);
                }


                $section = Section::query()
                    ->with('grade')
                    ->find($sectionId);

                if ($section && !in_array($section->grade_id, $data['grade_ids'])) {

                    throw ValidationException::withMessages([
                        'section_ids' =>
                        "الشعبة ({$section->name}) لا تنتمي إلى أي من الصفوف المحددة للإعلان."
                    ]);
                }
            }
        }
    }
}
