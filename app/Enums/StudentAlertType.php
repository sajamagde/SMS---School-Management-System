<?php

namespace App\Enums;

enum StudentAlertType: string
{
    case GENERAL = 'general';
    case BEHAVIORAL_COMPLAINT = 'behavioral_complaint';
    case ABSENCE_WARNING = 'absence_warning';
}