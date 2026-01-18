<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', fn (): Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse => redirect(config('app.frontend_url')));
