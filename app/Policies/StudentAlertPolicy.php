<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\StudentAlert;
use App\Models\Teacher;
use App\Models\TeacherAssignment;

class StudentAlertPolicy
{
    public function viewSection(
        Teacher $teacher,
        int $sectionId
    ): bool {
        return TeacherAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('section_id', $sectionId)
            ->exists();
    }

    public function viewStudent(
        Teacher $teacher,
        Student $student
    ): bool {
        $sectionId = $student->enrollments()
            ->where('status', 'active')
            ->value('section_id');

        if (! $sectionId) {
            return false;
        }

        return $this->viewSection($teacher, $sectionId);
    }

    public function create(
        Teacher $teacher,
        Student $student
    ): bool {
        return $this->viewStudent($teacher, $student);
    }

    public function viewParentAlert(
        $parent,
        StudentAlert $alert
    ): bool {
        return $alert->student->parent_id === $parent->id;
    }
}