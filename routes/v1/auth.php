<?php

declare(strict_types=1);

use App\Http\Controllers\v1\Auth\ChangePasswordController;
use App\Http\Controllers\v1\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\v1\Auth\ForgotPasswordController;
use App\Http\Controllers\v1\Auth\LoginController;
use App\Http\Controllers\v1\Auth\LogoutController;
use App\Http\Controllers\v1\Auth\MeController;
use App\Http\Controllers\v1\Auth\RegisterController;
use App\Http\Controllers\v1\Auth\ResetPasswordController;
use App\Http\Controllers\v1\Auth\UpdateMeController;
use App\Http\Controllers\v1\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::post('register', RegisterController::class)->name('v1.auth.register');
Route::post('login', LoginController::class)->name('v1.auth.login');
Route::post('forgot-password', ForgotPasswordController::class)->name('v1.auth.forgot-password');
Route::post('reset-password', ResetPasswordController::class)->name('v1.auth.reset-password');

Route::middleware('auth:sanctum')->group(static function (): void {
    Route::post('logout', LogoutController::class)->name('v1.auth.logout');
    Route::get('me', MeController::class)->name('v1.auth.me.index');
    Route::post('me', UpdateMeController::class)->name('v1.auth.me.update');
    Route::post('change-password', ChangePasswordController::class)->name('v1.auth.change-password');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware('signed')
        ->name('v1.auth.verification.verify');

    Route::post('email/verification-notification', EmailVerificationNotificationController::class)
        ->middleware('throttle:6,1')
        ->name('v1.auth.verification.send');
});
