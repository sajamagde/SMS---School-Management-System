<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case ACTIVE = 'active';
    case PASSED = 'passed';
    case FAILED = 'failed';
    case TRANSFERRED = 'transferred';
    case GRADUATED = 'graduated';
    case DISMISSED = 'dismissed';
}
