<?php

declare(strict_types=1);

namespace App\Http\Resources\v1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

/**
 * @mixin Role
 */
final class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'created_at' => $this->created_at?->toISOString(),
            'formatted_created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'formatted_updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
