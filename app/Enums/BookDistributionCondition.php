<?php

namespace App\Enums;

enum BookDistributionCondition: string
{
    case NEW = 'new';
    case USED = 'used';
}