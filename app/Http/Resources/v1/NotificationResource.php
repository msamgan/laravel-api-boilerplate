<?php

declare(strict_types=1);

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

/**
 * @mixin DatabaseNotification
 */
final class NotificationResource extends JsonResource
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
            'type' => $this->type,
            'data' => $this->data,
            'read_at' => $this->read_at?->toISOString(),
            'formatted_read_at' => $this->read_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toISOString(),
            'formatted_created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
