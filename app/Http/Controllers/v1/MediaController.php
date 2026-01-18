<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1;

use App\Actions\v1\Media\DeleteMediaAction;
use App\Actions\v1\Media\ListMediaAction;
use App\Actions\v1\Media\StoreMediaAction;
use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Media\ListMediaRequest;
use App\Http\Requests\v1\Media\StoreMediaRequest;
use App\Http\Resources\v1\Media\MediaResource;
use App\Models\Media;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

final class MediaController extends Controller
{
    use AuthorizesRequests;

    /**
     * List all media files.
     */
    public function index(ListMediaRequest $request, ListMediaAction $action): JsonResponse
    {
        $this->authorize('media.view');

        $perPage = (int) $request->query('per_page', (string) Constants::DEFAULT_PER_PAGE);
        $filters = $request->only(['search', 'type', 'user_id']);

        return $this->successResponse(
            MediaResource::collection($action->handle($request->user(), $filters, $perPage)),
            'Media retrieved successfully'
        );
    }

    /**
     * Upload a new media file.
     *
     * @bodyParam file required The file to upload. Max 10MB.
     */
    public function store(StoreMediaRequest $request, StoreMediaAction $action): JsonResponse
    {
        $this->authorize('media.create');

        return $this->createdResponse(
            new MediaResource($action->handle($request->user(), $request->file('file'), $request->input('user_id'))),
            'Media'
        );
    }

    /**
     * Delete a media file.
     *
     * @urlParam media int required The ID of the media. Example: 1
     */
    public function destroy(Media $media, DeleteMediaAction $action): JsonResponse
    {
        $this->authorize('media.delete');

        $action->handle($media);

        return $this->deletedResponse('Media');
    }
}
