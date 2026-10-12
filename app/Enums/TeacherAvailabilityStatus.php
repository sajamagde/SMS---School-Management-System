<?php

namespace App\Enums;

enum TeacherAvailabilityStatus: int
{
    case UNAVAILABLE = 0;
    case  AVAILABLE = 1;
    case PREFERRED = 2;
}
