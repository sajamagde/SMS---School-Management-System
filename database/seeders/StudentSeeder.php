<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()
            ->where('name', 'student')
            ->firstOrFail();


        $grade7SectionA = [

            ['first_name' => 'عمر', 'last_name' => 'علي', 'phone' => '0941111101', 'mother_name' => 'سارة', 'parent_id' => 1],
            ['first_name' => 'أحمد', 'last_name' => 'خالد', 'phone' => '0941111102', 'mother_name' => 'لمى', 'parent_id' => 1],
            ['first_name' => 'سارة', 'last_name' => 'محمد', 'phone' => '0941111103', 'mother_name' => 'هديل', 'parent_id' => 1],
            ['first_name' => 'لينا', 'last_name' => 'أحمد', 'phone' => '0941111104', 'mother_name' => 'أميرة', 'parent_id' => 2],
            ['first_name' => 'حسن', 'last_name' => 'عمر', 'phone' => '0941111105', 'mother_name' => 'سلام', 'parent_id' => 2],
            ['first_name' => 'نور', 'last_name' => 'علي', 'phone' => '0941111106', 'mother_name' => 'سجى', 'parent_id' => 2],
            ['first_name' => 'تالا', 'last_name' => 'يوسف', 'phone' => '0941111107', 'mother_name' => 'ريم', 'parent_id' => 3],
            ['first_name' => 'مايا', 'last_name' => 'حسن', 'phone' => '0941111108', 'mother_name' => 'رنا', 'parent_id' => 3],
            ['first_name' => 'كريم', 'last_name' => 'علي', 'phone' => '0941111109', 'mother_name' => 'رهف', 'parent_id' => 3],
            ['first_name' => 'راما', 'last_name' => 'خالد', 'phone' => '0941111110', 'mother_name' => 'قمر', 'parent_id' => 4],
        ];

        $this->_createStudents($grade7SectionA, $role, 1, 1);



        $grade7SectionB = [

            ['first_name' => 'علي', 'last_name' => 'حسن', 'phone' => '0941111111', 'mother_name' => 'سمر', 'parent_id' => 4],
            ['first_name' => 'يارا', 'last_name' => 'علي', 'phone' => '0941111112', 'mother_name' => 'فرح', 'parent_id' => 4],
            ['first_name' => 'جنى', 'last_name' => 'عمر', 'phone' => '0941111113', 'mother_name' => 'سارة', 'parent_id' => 5],
            ['first_name' => 'باسل', 'last_name' => 'أحمد', 'phone' => '0941111114', 'mother_name' => 'سارة', 'parent_id' => 5],
            ['first_name' => 'ربى', 'last_name' => 'خالد', 'phone' => '0941111115', 'mother_name' => 'عبير', 'parent_id' => 5],
            ['first_name' => 'فادي', 'last_name' => 'محمد', 'phone' => '0941111116', 'mother_name' => 'سماح', 'parent_id' => 6],
            ['first_name' => 'سامي', 'last_name' => 'علي', 'phone' => '0941111117', 'mother_name' => 'مريم', 'parent_id' => 6],
            ['first_name' => 'نور', 'last_name' => 'حسن', 'phone' => '0941111118', 'mother_name' => 'سردة', 'parent_id' => 6],
            ['first_name' => 'ريم', 'last_name' => 'يوسف', 'phone' => '0941111119', 'mother_name' => 'خديجة', 'parent_id' => 7],
            ['first_name' => 'زيد', 'last_name' => 'عمر', 'phone' => '0941111120', 'mother_name' => 'هبة', 'parent_id' => 7],
        ];

        $this->_createStudents($grade7SectionB, $role, 1, 2);


        $grade7SectionC = [

            ['first_name' => 'محمود', 'last_name' => 'علي', 'phone' => '0941111121', 'mother_name' => 'أحلام', 'parent_id' => 7],
            ['first_name' => 'ريان', 'last_name' => 'خالد', 'phone' => '0941111122', 'mother_name' => 'رنيم', 'parent_id' => 8],
            ['first_name' => 'ليان', 'last_name' => 'أحمد', 'phone' => '0941111123', 'mother_name' => 'لين', 'parent_id' => 8],
            ['first_name' => 'آدم', 'last_name' => 'عمر', 'phone' => '0941111124', 'mother_name' => 'مسا', 'parent_id' => 8],
            ['first_name' => 'ملك', 'last_name' => 'علي', 'phone' => '0941111125', 'mother_name' => 'بيان', 'parent_id' => 9],
            ['first_name' => 'يوسف', 'last_name' => 'محمد', 'phone' => '0941111126', 'mother_name' => 'هداية', 'parent_id' => 9],
            ['first_name' => 'سيلين', 'last_name' => 'حسن', 'phone' => '0941111127', 'mother_name' => 'أبرار', 'parent_id' => 9],
            ['first_name' => 'خالد', 'last_name' => 'أحمد', 'phone' => '0941111128', 'mother_name' => 'عنود', 'parent_id' => 10],
            ['first_name' => 'دانا', 'last_name' => 'علي', 'phone' => '0941111129', 'mother_name' => 'ميمونة', 'parent_id' => 10],
            ['first_name' => 'تيم', 'last_name' => 'عمر', 'phone' => '0941111130', 'mother_name' => 'حفصة', 'parent_id' => 10],
        ];

        $this->_createStudents($grade7SectionC, $role, 1, 3);



        $grade8SectionA = [

            ['first_name' => 'عمر', 'last_name' => 'محمود', 'phone' => '0951111101', 'mother_name' => 'دينا', 'parent_id' => 11],
            ['first_name' => 'أحمد', 'last_name' => 'علي', 'phone' => '0951111102', 'mother_name' => 'إيلا', 'parent_id' => 11],
            ['first_name' => 'سارة', 'last_name' => 'خالد', 'phone' => '0951111103', 'mother_name' => 'وتين', 'parent_id' => 12],
            ['first_name' => 'لينا', 'last_name' => 'حسن', 'phone' => '0951111104', 'mother_name' => 'راضية', 'parent_id' => 12],
            ['first_name' => 'نور', 'last_name' => 'محمد', 'phone' => '0951111105', 'mother_name' => 'أسلام', 'parent_id' => 13],
            ['first_name' => 'تالا', 'last_name' => 'علي', 'phone' => '0951111106', 'mother_name' => 'ريان', 'parent_id' => 13],
            ['first_name' => 'كريم', 'last_name' => 'عمر', 'phone' => '0951111107', 'mother_name' => 'سارة', 'parent_id' => 14],
            ['first_name' => 'راما', 'last_name' => 'أحمد', 'phone' => '0951111108', 'mother_name' => 'سارة', 'parent_id' => 14],
            ['first_name' => 'دانا', 'last_name' => 'يوسف', 'phone' => '0951111109', 'mother_name' => 'أميرة', 'parent_id' => 15],
            ['first_name' => 'آدم', 'last_name' => 'علي', 'phone' => '0951111110', 'mother_name' => 'آلاء', 'parent_id' => 15],
        ];

        $this->_createStudents($grade8SectionA, $role, 2, 4);


        $grade8SectionB = [

            ['first_name' => 'يارا', 'last_name' => 'علي', 'phone' => '0951111111', 'mother_name' => 'رولا', 'parent_id' => 16],
            ['first_name' => 'باسل', 'last_name' => 'خالد', 'phone' => '0951111112', 'mother_name' => 'هالة', 'parent_id' => 16],
            ['first_name' => 'ريم', 'last_name' => 'أحمد', 'phone' => '0951111113', 'mother_name' => 'نهى', 'parent_id' => 17],
            ['first_name' => 'فادي', 'last_name' => 'حسن', 'phone' => '0951111114', 'mother_name' => 'صفاء', 'parent_id' => 17],
            ['first_name' => 'مايا', 'last_name' => 'علي', 'phone' => '0951111115', 'mother_name' => 'شهد', 'parent_id' => 18],
            ['first_name' => 'زيد', 'last_name' => 'عمر', 'phone' => '0951111116', 'mother_name' => 'جنى', 'parent_id' => 18],
            ['first_name' => 'ملك', 'last_name' => 'محمد', 'phone' => '0951111117', 'mother_name' => 'لارا', 'parent_id' => 19],
            ['first_name' => 'تيم', 'last_name' => 'علي', 'phone' => '0951111118', 'mother_name' => 'ليما', 'parent_id' => 19],
            ['first_name' => 'ريان', 'last_name' => 'خالد', 'phone' => '0951111119', 'mother_name' => 'لينا', 'parent_id' => 20],
            ['first_name' => 'جنى', 'last_name' => 'أحمد', 'phone' => '0951111120', 'mother_name' => 'ريم', 'parent_id' => 20],
        ];

        $this->_createStudents($grade8SectionB, $role, 2, 5);



        $grade8SectionC = [

            ['first_name' => 'محمود', 'last_name' => 'علي', 'phone' => '0951111121', 'mother_name' => 'أميرة', 'parent_id' => 21],
            ['first_name' => 'سامي', 'last_name' => 'حسن', 'phone' => '0951111122', 'mother_name' => 'جمانة', 'parent_id' => 21],
            ['first_name' => 'ربى', 'last_name' => 'عمر', 'phone' => '0951111123', 'mother_name' => 'هيام', 'parent_id' => 22],
            ['first_name' => 'سيلين', 'last_name' => 'أحمد', 'phone' => '0951111124', 'mother_name' => 'شذى', 'parent_id' => 22],
            ['first_name' => 'علي', 'last_name' => 'محمد', 'phone' => '0951111125', 'mother_name' => 'شهام', 'parent_id' => 23],
            ['first_name' => 'نور', 'last_name' => 'علي', 'phone' => '0951111126', 'mother_name' => 'ختام', 'parent_id' => 23],
            ['first_name' => 'يوسف', 'last_name' => 'خالد', 'phone' => '0951111127', 'mother_name' => 'زهراء', 'parent_id' => 24],
            ['first_name' => 'هدى', 'last_name' => 'حسن', 'phone' => '0951111128', 'mother_name' => 'زهور', 'parent_id' => 24],
            ['first_name' => 'خالد', 'last_name' => 'علي', 'phone' => '0951111129', 'mother_name' => 'سوسن', 'parent_id' => 25],
            ['first_name' => 'ليان', 'last_name' => 'عمر', 'phone' => '0951111130', 'mother_name' => 'فوزية', 'parent_id' => 25],
        ];

        $this->_createStudents($grade8SectionC, $role, 2, 6);



        $grade9SectionA = [

            ['first_name' => 'عمر', 'last_name' => 'علي', 'phone' => '0961111101', 'mother_name' => 'بتول', 'parent_id' => 26],
            ['first_name' => 'أحمد', 'last_name' => 'حسن', 'phone' => '0961111102', 'mother_name' => 'تقوى', 'parent_id' => 27],
            ['first_name' => 'سارة', 'last_name' => 'محمد', 'phone' => '0961111103', 'mother_name' => 'سارة', 'parent_id' => 28],
            ['first_name' => 'لينا', 'last_name' => 'خالد', 'phone' => '0961111104', 'mother_name' => 'مريم', 'parent_id' => 29],
            ['first_name' => 'نور', 'last_name' => 'علي', 'phone' => '0961111105', 'mother_name' => 'عبير', 'parent_id' => 30],
            ['first_name' => 'تالا', 'last_name' => 'عمر', 'phone' => '0961111106', 'mother_name' => 'أسماء', 'parent_id' => 31],
            ['first_name' => 'كريم', 'last_name' => 'أحمد', 'phone' => '0961111107', 'mother_name' => 'فاطمة', 'parent_id' => 32],
            ['first_name' => 'دانا', 'last_name' => 'علي', 'phone' => '0961111108', 'mother_name' => 'مايا', 'parent_id' => 33],
            ['first_name' => 'آدم', 'last_name' => 'يوسف', 'phone' => '0961111109', 'mother_name' => 'ماريا', 'parent_id' => 34],
            ['first_name' => 'راما', 'last_name' => 'حسن', 'phone' => '0961111110', 'mother_name' => 'ماري', 'parent_id' => 35],
        ];

        $this->_createStudents($grade9SectionA, $role, 3, 7);


        $grade9SectionB = [

            ['first_name' => 'يارا', 'last_name' => 'علي', 'phone' => '0961111111', 'mother_name' => 'فتون', 'parent_id' => 36],
            ['first_name' => 'باسل', 'last_name' => 'خالد', 'phone' => '0961111112', 'mother_name' => 'سناء', 'parent_id' => 37],
            ['first_name' => 'ريم', 'last_name' => 'أحمد', 'phone' => '0961111113', 'mother_name' => 'سلاف', 'parent_id' => 38],
            ['first_name' => 'فادي', 'last_name' => 'محمد', 'phone' => '0961111114', 'mother_name' => 'هيا', 'parent_id' => 39],
            ['first_name' => 'مايا', 'last_name' => 'علي', 'phone' => '0961111115', 'mother_name' => 'هناء', 'parent_id' => 40],
            ['first_name' => 'زيد', 'last_name' => 'حسن', 'phone' => '0961111116', 'mother_name' => 'إيلينا', 'parent_id' => 41],
            ['first_name' => 'ملك', 'last_name' => 'عمر', 'phone' => '0961111117', 'mother_name' => 'بوني', 'parent_id' => 42],
            ['first_name' => 'تيم', 'last_name' => 'علي', 'phone' => '0961111118', 'mother_name' => 'سديم', 'parent_id' => 43],
            ['first_name' => 'ريان', 'last_name' => 'خالد', 'phone' => '0961111119', 'mother_name' => 'سارة', 'parent_id' => 44],
            ['first_name' => 'جنى', 'last_name' => 'أحمد', 'phone' => '0961111120', 'mother_name' => 'سجى', 'parent_id' => 45],
        ];

        $this->_createStudents($grade9SectionB, $role, 3, 8);



        $grade9SectionC = [

            ['first_name' => 'محمود', 'last_name' => 'علي', 'phone' => '0961111121', 'mother_name' => 'سماهر', 'parent_id' => 46],
            ['first_name' => 'سامي', 'last_name' => 'حسن', 'phone' => '0961111122', 'mother_name' => 'دينا', 'parent_id' => 47],
            ['first_name' => 'ربى', 'last_name' => 'محمد', 'phone' => '0961111123', 'mother_name' => 'ديانا', 'parent_id' => 48],
            ['first_name' => 'سيلين', 'last_name' => 'أحمد', 'phone' => '0961111124', 'mother_name' => 'رقية', 'parent_id' => 49],
            ['first_name' => 'علي', 'last_name' => 'عمر', 'phone' => '0961111125', 'mother_name' => 'روعة', 'parent_id' => 50],
            ['first_name' => 'نور', 'last_name' => 'علي', 'phone' => '0961111126', 'mother_name' => 'روان', 'parent_id' => 51],
            ['first_name' => 'يوسف', 'last_name' => 'خالد', 'phone' => '0961111127', 'mother_name' => 'رُواء', 'parent_id' => 52],
            ['first_name' => 'هدى', 'last_name' => 'حسن', 'phone' => '0961111128', 'mother_name' => 'بيان', 'parent_id' => 53],
            ['first_name' => 'خالد', 'last_name' => 'علي', 'phone' => '0961111129', 'mother_name' => 'نور', 'parent_id' => 54],
            ['first_name' => 'ليان', 'last_name' => 'أحمد', 'phone' => '0961111130', 'mother_name' => 'حواء', 'parent_id' => 55],
        ];

        $this->_createStudents($grade9SectionC, $role, 3, 9);
    }


    private function _createStudents(
        array $students,
        Role $role,
        int $gradeId,
        int $sectionId
    ): void {
        foreach ($students as $s) {

            $user = User::create([
                'first_name' => $s['first_name'],
                'last_name'  => $s['last_name'],
                'phone'      => $s['phone'],
                'code'       => $this->_generateUserCode('S'),
                'password'   => Hash::make('123456'),
                'role_id'    => $role->id,
                'status'     => 'active',
            ]);

            $student = $user->student()->create([
                'mother_name' => $s['mother_name'],
                'parent_id'   => $s['parent_id'],
                'birth_date'  => '2012-01-01',
                'address'     => 'دمشق',
            ]);

            $student->enrollments()->create([
                'academic_year_id' => 1,
                'grade_id'         => $gradeId,
                'section_id'       => $sectionId,
                'status'           => 'active',
            ]);
        }
    }


    private function _generateUserCode(string $prefix): string
    {
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