<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use App\Traits\NotifiesSuperAdmins;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property bool $is_active
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $super_admin_id
 * @property string|null $profile_picture_id
 * @property-read Media|null $profilePicture
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable, NotifiesSuperAdmins;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'uuid',
        'created_by',
        'super_admin_id',
        'profile_picture_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
    ];

    public function scopeSearch($query, ?string $search)
    {
        return $query->when($search, function ($query, $search): void {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });
    }

    public function scopeFilterByRole($query, ?string $role)
    {
        return $query->when($role, function ($query, $role): void {
            $query->role($role);
        });
    }

    public function scopeFilterByActive($query, ?bool $isActive)
    {
        return $query->when($isActive !== null, function ($query) use ($isActive): void {
            $query->where('is_active', $isActive);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function superAdmin(): BelongsTo
    {
        return $this->belongsTo(self::class, 'super_admin_id');
    }

    public function profilePicture(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'profile_picture_id');
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'user_media');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        \Illuminate\Auth\Notifications\ResetPassword::createUrlUsing(static fn (object $notifiable, string $token): string => config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . $notifiable->getEmailForPasswordReset());

        $this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        \Illuminate\Auth\Notifications\VerifyEmail::createUrlUsing(static fn (object $notifiable): string => \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'v1.auth.verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        ));

        $this->notify(new \Illuminate\Auth\Notifications\VerifyEmail);
    }

    /**
     * Get the superAdmin ID for media and other shared resources.
     * Recursively checks until a user with the Super Admin role is found.
     */
    public function getSuperAdminId(): int
    {
        if ($this->hasRole(Role::SUPER_ADMIN->value)) {
            return (int) $this->getKey();
        }

        if (isset($this->super_admin_id) && $this->super_admin_id) {
            return (int) $this->super_admin_id;
        }

        $user = $this;
        while (isset($user->created_by) && $user->created_by) {
            $user = $user->creator;
            if ($user && $user->hasRole(Role::SUPER_ADMIN->value)) {
                return (int) $user->getKey();
            }
        }

        return (int) ($this->created_by ?? $this->getKey());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
