<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\NotifiesSuperAdmins;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property string $id
 * @property string $name
 * @property string $file_name
 * @property string $mime_type
 * @property string $path
 * @property string $disk
 * @property int $size
 * @property int|null $created_by
 * @property int|null $super_admin_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read User|null $superAdmin
 * @property-read string $url
 * @property-read Collection<int, User> $users
 * @property-read Collection<int, User> $client
 */
final class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory, HasUuids, LogsActivity, NotifiesSuperAdmins;

    protected $fillable = [
        'name',
        'file_name',
        'mime_type',
        'path',
        'disk',
        'size',
        'created_by',
        'super_admin_id',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected $appends = [
        'url',
    ];

    public function url(): Attribute
    {
        return Attribute::get(fn () => Storage::disk($this->disk)->url($this->path));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /**
     * @return BelongsTo<User, Media>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, Media>
     */
    public function superAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }

    /**
     * @return BelongsToMany<User, Media>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_media');
    }

    public function client(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_media');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $query, string $search): void {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        });
    }

    public function scopeByType(Builder $query, ?string $type): Builder
    {
        return $query->when($type, function (Builder $query, string $type): void {
            if ($type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($type === 'document') {
                $query->where('mime_type', 'application/pdf');
            }
        });
    }
}
