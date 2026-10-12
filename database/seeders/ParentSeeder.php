<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->where('name', 'parent')->firstOrFail();

        $parents = [

            ['first_name' => 'خالد',  'last_name' => 'علي',    'phone' => '0991111101', 'address' => 'دمشق، المزة'],
            ['first_name' => 'أحمد',  'last_name' => 'محمد',   'phone' => '0991111102', 'address' => 'دمشق، المالكي'],
            ['first_name' => 'عمر',   'last_name' => 'حسن',    'phone' => '0991111103', 'address' => 'دمشق، أبو رمانة'],
            ['first_name' => 'يوسف',  'last_name' => 'أحمد',   'phone' => '0991111104', 'address' => 'دمشق، الميدان'],
            ['first_name' => 'سامي',  'last_name' => 'خالد',   'phone' => '0991111105', 'address' => 'دمشق، كفرسوسة'],
            ['first_name' => 'باسل',  'last_name' => 'علي',    'phone' => '0991111106', 'address' => 'دمشق، برزة'],
            ['first_name' => 'محمود', 'last_name' => 'عمر',    'phone' => '0991111107', 'address' => 'دمشق، ركن الدين'],
            ['first_name' => 'حسن',   'last_name' => 'يوسف',   'phone' => '0991111108', 'address' => 'دمشق، القنوات'],
            ['first_name' => 'علي',   'last_name' => 'محمود',  'phone' => '0991111109', 'address' => 'دمشق، باب توما'],
            ['first_name' => 'فادي',  'last_name' => 'أحمد',   'phone' => '0991111110', 'address' => 'دمشق، القصاع'],

            ['first_name' => 'طارق', 'last_name' => 'علي',    'phone' => '0991111111', 'address' => 'دمشق، التجارة'],
            ['first_name' => 'نادر', 'last_name' => 'حسن',    'phone' => '0991111112', 'address' => 'دمشق، الدويلعة'],
            ['first_name' => 'رائد', 'last_name' => 'محمد',   'phone' => '0991111113', 'address' => 'دمشق، الزاهرة'],
            ['first_name' => 'وائل', 'last_name' => 'خالد',   'phone' => '0991111114', 'address' => 'دمشق، القدم'],
            ['first_name' => 'عماد', 'last_name' => 'أحمد',   'phone' => '0991111115', 'address' => 'دمشق، التضامن'],
            ['first_name' => 'زياد', 'last_name' => 'علي',    'phone' => '0991111116', 'address' => 'دمشق، جرمانا'],
            ['first_name' => 'أيمن', 'last_name' => 'عمر',    'phone' => '0991111117', 'address' => 'دمشق، صحنايا'],
            ['first_name' => 'رامي', 'last_name' => 'يوسف',   'phone' => '0991111118', 'address' => 'دمشق، قدسيا'],
            ['first_name' => 'جمال', 'last_name' => 'محمود',  'phone' => '0991111119', 'address' => 'دمشق، مشروع دمر'],
            ['first_name' => 'سامر', 'last_name' => 'علي',    'phone' => '0991111120', 'address' => 'دمشق، دمر البلد'],

            ['first_name' => 'عدنان', 'last_name' => 'حسن',   'phone' => '0991111121', 'address' => 'دمشق، الهامة'],
            ['first_name' => 'مازن',  'last_name' => 'علي',   'phone' => '0991111122', 'address' => 'دمشق، ريف دمشق'],
            ['first_name' => 'نبيل',  'last_name' => 'خالد',  'phone' => '0991111123', 'address' => 'دمشق، مساكن برزة'],
            ['first_name' => 'غسان',  'last_name' => 'محمد',  'phone' => '0991111124', 'address' => 'دمشق، مساكن المزة'],
            ['first_name' => 'رشيد',  'last_name' => 'أحمد',  'phone' => '0991111125', 'address' => 'دمشق، المهاجرين'],
            ['first_name' => 'أنس',   'last_name' => 'علي',   'phone' => '0991111126', 'address' => 'دمشق، الصالحية'],
            ['first_name' => 'كمال',  'last_name' => 'يوسف',  'phone' => '0991111127', 'address' => 'دمشق، سوق الحميدية'],
            ['first_name' => 'حاتم',  'last_name' => 'محمود', 'phone' => '0991111128', 'address' => 'دمشق، باب شرقي'],
            ['first_name' => 'إياد',  'last_name' => 'عمر',   'phone' => '0991111129', 'address' => 'دمشق، العباسيين'],
            ['first_name' => 'تامر',  'last_name' => 'علي',   'phone' => '0991111130', 'address' => 'دمشق، ريف دمشق - حرستا'],

            ['first_name' => 'فراس',  'last_name' => 'حسن',   'phone' => '0991111131', 'address' => 'دمشق، ريف دمشق - دوما'],
            ['first_name' => 'أمجد',  'last_name' => 'علي',   'phone' => '0991111132', 'address' => 'دمشق، ريف دمشق - التل'],
            ['first_name' => 'لؤي',   'last_name' => 'محمد',  'phone' => '0991111133', 'address' => 'دمشق، ريف دمشق - قطنا'],
            ['first_name' => 'بلال',  'last_name' => 'خالد',  'phone' => '0991111134', 'address' => 'دمشق، ريف دمشق - داريا'],
            ['first_name' => 'نعيم',  'last_name' => 'أحمد',  'phone' => '0991111135', 'address' => 'دمشق، ريف دمشق - المعضمية'],
            ['first_name' => 'قاسم',  'last_name' => 'علي',   'phone' => '0991111136', 'address' => 'دمشق، ريف دمشق - الكسوة'],
            ['first_name' => 'سعيد',  'last_name' => 'يوسف',  'phone' => '0991111137', 'address' => 'دمشق، ريف دمشق - الزبداني'],
            ['first_name' => 'طلال',  'last_name' => 'محمود', 'phone' => '0991111138', 'address' => 'دمشق، ريف دمشق - بلودان'],
            ['first_name' => 'وليد',  'last_name' => 'عمر',   'phone' => '0991111139', 'address' => 'دمشق، ريف دمشق - النبك'],
            ['first_name' => 'يامن',  'last_name' => 'علي',   'phone' => '0991111140', 'address' => 'دمشق، ريف دمشق - يبرود'],

            ['first_name' => 'سلام',  'last_name' => 'يامن',  'phone' => '0991111141', 'address' => 'دمشق، المزة 86'],
            ['first_name' => 'طلال',  'last_name' => 'يوسف',  'phone' => '0991111142', 'address' => 'دمشق، المزة فيلات'],
            ['first_name' => 'أحمد',  'last_name' => 'محمد',  'phone' => '0991111143', 'address' => 'دمشق، مساكن برزة البلد'],
            ['first_name' => 'بلال',  'last_name' => 'خالد',  'phone' => '0991111144', 'address' => 'دمشق، شارع بغداد'],
            ['first_name' => 'نعيم',  'last_name' => 'علي',   'phone' => '0991111145', 'address' => 'دمشق، العدوي'],
            ['first_name' => 'قاسم',  'last_name' => 'طلال',  'phone' => '0991111146', 'address' => 'دمشق، ركن الدين - أسد الدين'],
            ['first_name' => 'وليد',  'last_name' => 'يوسف',  'phone' => '0991111147', 'address' => 'دمشق، الشعلان'],
            ['first_name' => 'طلال',  'last_name' => 'محمود', 'phone' => '0991111148', 'address' => 'دمشق، الحلبوني'],
            ['first_name' => 'بلال',  'last_name' => 'عمر',   'phone' => '0991111149', 'address' => 'دمشق، البرامكة'],
            ['first_name' => 'يوسف',  'last_name' => 'أحمد',  'phone' => '0991111150', 'address' => 'دمشق، باب الجابية'],

            ['first_name' => 'سلام',  'last_name' => 'يامن',  'phone' => '0991111151', 'address' => 'دمشق، البحصة'],
            ['first_name' => 'طلال',  'last_name' => 'يوسف',  'phone' => '09911111452', 'address' => 'دمشق، سوق ساروجة'],
            ['first_name' => 'أحمد',  'last_name' => 'محمد',  'phone' => '0991111153', 'address' => 'دمشق، العمارة'],
            ['first_name' => 'بلال',  'last_name' => 'خالد',  'phone' => '0991111154', 'address' => 'دمشق، باب السلام'],
            ['first_name' => 'نعيم',  'last_name' => 'علي',   'phone' => '0991111155', 'address' => 'دمشق، القيمرية'],
        ];

        $this->_createParents($parents, $role);
    }

    private function _createParents(array $parents, Role $role): void
    {
        foreach ($parents as $p) {

            $user = User::create([
                'first_name' => $p['first_name'],
                'last_name'  => $p['last_name'],
                'phone'      => $p['phone'],
                'code'       => $this->_generateUserCode('P'),
                'password'   => Hash::make('123456'),
                'role_id'    => $role->id,
                'status'     => 'active',
            ]);

            $user->parentProfile()->create([
                'address' => $p['address'],
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
