<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Traits\HasApiResponse;

abstract class Controller
{
    use HasApiResponse;
}
