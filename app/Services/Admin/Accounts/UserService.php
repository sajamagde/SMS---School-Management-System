<?php

namespace App\Services\Admin\Accounts;

use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{

    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {

            $role = Role::query()->where('name', $data['role_name'])->firstOrFail();

            $user = User::create([
                'code'       => $this->_generateUniqueUserCode($role->name),
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'phone'      => $data['phone'],
                'password'   => Hash::make($data['password']),
                'role_id'    => $role->id,
                'status'     => 'active',
            ]);

            $this->_createProfileForRole($user, $role->name, $data);

            return $user;
        });
    }
    private function _createProfileForRole(User $user, string $roleName, array $data): void
    {
        switch ($roleName) {

            case 'student':

                $student = $user->student()->create([
                    'mother_name' => $data['mother_name'],
                    'birth_date'  => $data['birth_date'],
                    'address'     => $data['address'],
                    'parent_id'   => $data['parent_id'] ?? null,
                ]);


                $academicYear = AcademicYear::query()
                    ->where('is_active', true)
                    ->firstOrFail();

                $student->enrollments()->create([
                    'academic_year_id' => $academicYear->id,
                    'grade_id' => $data['grade_id'],
                ]);

                break;

            case 'teacher':

                $teacher = $user->teacher()->create([]);

                foreach ($data['section_ids'] as $sectionId) {
                    $teacher->assignments()->create([
                        'section_id' => $sectionId,
                        'subject_id' => $data['subject_id'],
                    ]);
                }

                break;

            case 'supervisor':

                $user->supervisor()->create([
                    'grade_id' => $data['grade_id'],
                ]);

                break;

            case 'parent':

                $user->parentProfile()->create([
                    'address' => $data['address'] ?? null,
                ]);
                break;
        }
    }
    private function _generateUniqueUserCode(string $roleName): string
    {
        $prefix = match ($roleName) {
            'student'    => 'S',
            'teacher'    => 'T',
            'supervisor' => 'A',
            'parent'     => 'P',
            'librarian'  => 'L',
            default      => 'U',
        };

        $lastUser = User::query()
            ->where('code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if (!$lastUser) {
            return $prefix . '1001';
        }

        $lastNumber = (int) substr($lastUser->code, 1);

        return $prefix . ($lastNumber + 1);
    }
}
