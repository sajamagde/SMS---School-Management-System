<?php

namespace App\Enums;

enum BookReturnCondition: string
{
    case USED = 'used';
    case DAMAGED = 'damaged';
}