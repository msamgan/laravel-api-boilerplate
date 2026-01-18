<?php

declare(strict_types=1);

namespace App\Actions\v1\Auth;

use App\Actions\v1\Media\StoreMediaAction;
use App\Models\User;
use Illuminate\Http\UploadedFile;

final readonly class UpdateMeAction
{
    public function __construct(private StoreMediaAction $storeMediaAction) {}

    /**
     * @param  array{name?: string, email?: string, profile_picture?: UploadedFile, remove_profile_picture?: bool}  $data
     */
    public function handle(User $user, array $data): User
    {
        if (isset($data['profile_picture'])) {
            $media = $this->storeMediaAction->handle($user, $data['profile_picture']);
            $data['profile_picture_id'] = $media->id;
            unset($data['profile_picture']);
        }

        if (isset($data['remove_profile_picture']) && $data['remove_profile_picture']) {
            $data['profile_picture_id'] = null;
            unset($data['remove_profile_picture']);
        }

        $user->update($data);

        return $user;
    }
}
