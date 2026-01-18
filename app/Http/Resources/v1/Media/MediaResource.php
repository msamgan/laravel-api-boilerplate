<?php

declare(strict_types=1);

namespace App\Http\Resources\v1\Media;

use App\Http\Resources\v1\UserResource;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Media
 */
final class MediaResource extends JsonResource
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
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'url' => Storage::disk($this->disk)->url($this->path),
            'client' => new UserResource($this->client?->first()),
            'creator' => new UserResource($this->whenLoaded('user')),
            'super_admin' => new UserResource($this->whenLoaded('superAdmin')),
            'created_at' => $this->created_at?->toISOString(),
            'formatted_created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'formatted_updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
