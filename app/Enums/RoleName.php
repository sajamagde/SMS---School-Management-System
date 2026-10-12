<?php

namespace App\Enums;

enum RoleName: string
{
    case SUPER_ADMIN = 'super_admin';
    case SUPERVISOR = 'supervisor';
    case TEACHER = 'teacher';
    case STUDENT = 'student';
    case PARENT = 'parent';

    case LIBRARIAN = 'librarian';
}
