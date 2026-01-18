<?php

declare(strict_types=1);

namespace App\Actions\v1\Media;

use App\Models\Media;
use App\Models\User;
use App\Models\UserMedia;
use Illuminate\Http\UploadedFile;

final readonly class StoreMediaAction
{
    /**
     * Execute the action.
     */
    public function handle(User $authUser, UploadedFile $file, ?string $userId = null): Media
    {
        $disk = config('filesystems.default');
        $fileName = $file->getClientOriginalName();
        $path = $file->storeAs('media', $fileName, $disk);

        $media = Media::query()->create([
            'name' => pathinfo($fileName, PATHINFO_FILENAME),
            'file_name' => $fileName,
            'mime_type' => $file->getMimeType(),
            'path' => $path,
            'disk' => $disk,
            'size' => $file->getSize(),
            'created_by' => $authUser->getKey(),
            'super_admin_id' => $authUser->getSuperAdminId(),
        ]);

        if ($userId) {
            $userId = User::query()->where('uuid', $userId)->value('id');

            UserMedia::query()->create([
                'user_id' => $userId,
                'media_id' => $media->getKey(),
                'created_by' => $authUser->getKey(),
                'super_admin_id' => $authUser->getSuperAdminId(),
            ]);
        }

        return $media->load(['user', 'superAdmin', 'client']);
    }
}
