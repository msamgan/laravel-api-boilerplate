<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case SUPER_ADMIN = 'Super Admin';
    case EMPLOYEE = 'Employee';
    case CLIENT = 'Client';
}
