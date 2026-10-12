<?php

namespace App\Enums;

enum AnnouncementTarget : int
{
    case ALL = 0;
    case  STUDENTS = 1;
    case TEACHERS = 2;
    case PARENTS = 3;
    case SUPERVISORS = 4;
    
}
