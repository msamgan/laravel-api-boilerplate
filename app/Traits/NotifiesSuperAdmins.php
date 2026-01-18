<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\Role;
use App\Models\User;
use App\Notifications\EntityChangedNotification;
use Illuminate\Support\Facades\Notification;

trait NotifiesSuperAdmins
{
    /**
     * Boot the trait and register model event listeners.
     */
    public static function bootNotifiesSuperAdmins(): void
    {
        static::created(function ($model): void {
            $model->notifySuperAdmins('created');
        });

        static::updated(function ($model): void {
            $model->notifySuperAdmins('updated');
        });

        static::deleted(function ($model): void {
            $model->notifySuperAdmins('deleted');
        });
    }

    /**
     * Send notification to all super admins.
     */
    protected function notifySuperAdmins(string $action): void
    {
        $superAdmins = User::query()->role(Role::SUPER_ADMIN->value)->get();

        if ($superAdmins->isEmpty()) {
            return;
        }

        $user = auth('sanctum')->user() ?? auth()->user();
        $performedBy = $user ? $user->name : 'System';

        Notification::send($superAdmins, new EntityChangedNotification($this, $action, $performedBy));
    }
}
