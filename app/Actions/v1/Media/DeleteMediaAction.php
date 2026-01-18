<?php

declare(strict_types=1);

namespace App\Actions\v1\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteMediaAction
{
    /**
     * Execute the action.
     */
    public function handle(Media $media): void
    {
        Storage::disk($media->disk)->delete($media->path);

        $media->delete();
    }
}
