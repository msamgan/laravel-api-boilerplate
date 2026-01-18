<?php

declare(strict_types=1);

namespace App\Http\Resources\v1;

use App\Http\Resources\v1\Media\MediaResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class UserResource extends JsonResource
{
    private bool $withPermissions = false;

    /**
     * @return $this
     */
    public function withPermissions(): self
    {
        $this->withPermissions = true;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->getRoleNames()->first(),
            'profile_picture' => new MediaResource($this->whenLoaded('profilePicture')),
            'is_active' => $this->whenHas('is_active'),
            'created_at' => $this->created_at?->toISOString(),
            'formatted_created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'formatted_updated_at' => $this->updated_at?->toDateTimeString(),
            'permissions' => $this->when($this->withPermissions, fn () => $this->getAllPermissions()->pluck('name')),
        ];
    }
}
