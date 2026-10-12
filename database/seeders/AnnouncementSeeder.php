<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;
use App\Models\User;
use App\Enums\AnnouncementTarget;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $grade1Id = 1; // Grade 7
        $grade2Id = 2; // Grade 8

        $sectionAId = 1;
        $sectionBId = 2;

        // جلب معلم من الصف الأول ومعلم من الصف الثاني للتنويع في الناشرين
        // المعلم 3 من معلمي الصف الأول، والمعلم 13 من معلمي الصف الثاني بناءً على TeacherSeeder
        $teacherGrade1 = User::query()->where('code', 'T1001')->first()?->id ?? 3;
        $teacherGrade2 = User::query()->where('code', 'T1011')->first()?->id ?? 13; 

        // -------------------------------------------------------------
        // الحالة 1: إعلان عام للمدرسة بأكملها (من معلم الصف الأول)
        // -------------------------------------------------------------
        Announcement::create([
            'user_id'         => 1,
            'title'           => 'إعلان عام لجميع الطلاب',
            'content'         => 'هذا الإعلان يجب أن يظهر لجميع الطلاب في المدرسة بدون استثناء.',
            'target_audience' => [AnnouncementTarget::ALL->value],
            'is_pinned'       => false,
        ]);

        // -------------------------------------------------------------
        // الحالة 2: إعلان مخصص للصف الأول كامل (من معلم الصف الأول)
        // -------------------------------------------------------------
        $announcementGrade1 = Announcement::create([
            'user_id'         => 1,
            'title'           => 'إعلان خاص بالصف الأول فقط',
            'content'         => 'هذا الإعلان موجه لطلاب الصف الأول فقط بجميع شعبه، ولا يظهر للصف الثاني.',
            'target_audience' => [AnnouncementTarget::STUDENTS->value],
            'is_pinned'       => false,
        ]);
        $announcementGrade1->grades()->attach($grade1Id);

        // -------------------------------------------------------------
        // الحالة 3: إعلان مخصص للصف الأول - شعبة (أ) تحديداً
        // -------------------------------------------------------------
        $announcementSectionA = Announcement::create([
            'user_id'         => 1,
            'title'           => 'إعلان خاص بالصف الأول - شعبة أ',
            'content'         => 'هذا الإعلان يجب أن يظهر فقط لطالب شعبة (أ) ولا يظهر لطالب شعبة (ب).',
            'target_audience' => [AnnouncementTarget::STUDENTS->value],
            'is_pinned'       => false,
        ]);
        $announcementSectionA->grades()->attach($grade1Id);
        $announcementSectionA->sections()->attach($sectionAId);

        // -------------------------------------------------------------
        // الحالة 4: إعلان مخصص للشعبة (ب) فقط
        // -------------------------------------------------------------
        $announcementSectionB = Announcement::create([
            'user_id'         => 1,
            'title'           => 'إعلان خاص بالصف الأول - شعبة ب',
            'content'         => 'هذا الإعلان مخصص لشعبة ب، الطالب في شعبة أ يجب ألا يراه مطلقاً.',
            'target_audience' => [AnnouncementTarget::STUDENTS->value],
            'is_pinned'       => false,
        ]);
        $announcementSectionB->grades()->attach($grade1Id);
        $announcementSectionB->sections()->attach($sectionBId);

        // -------------------------------------------------------------
        // الحالة 5: إعلان مخصص للصف الثاني (ينشئه معلم الصف الثاني T1011)
        // -------------------------------------------------------------
        $announcementGrade2 = Announcement::create([
            'user_id'         => 11, // 👈 تغيير الناشر ليصبح معلماً من الصف الثاني
            'title'           => 'إعلان خاص بالصف الثاني',
            'content'         => 'هذا الإعلان موجه للصف الثاني، طلاب معلمو الصف الأول يجب ألا يروه.',
            'target_audience' => [AnnouncementTarget::STUDENTS->value],
            'is_pinned'       => false,
        ]);
        $announcementGrade2->grades()->attach($grade2Id);
    }
}