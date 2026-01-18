<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Common\ListStatusesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\FollowUp\Enums\FollowUpStatus;
use Modules\Leads\Enums\LeadStatus;
use Modules\Meetings\Enums\MeetingStatus;

final class StatusController extends Controller
{
    /**
     * Get statuses for a given model.
     *
     * @authenticated
     *
     * @options lead, follow-up, meeting
     *
     * @responseKey value string The status value
     * @responseKey label string The human-readable label
     *
     * @response 404 Model Not Found
     *
     * @responseKey 404 model string The requested model name
     */
    public function __invoke(ListStatusesRequest $request, string $model): JsonResponse
    {
        $validated = $request->validated();

        $enumClass = match (Str::kebab($validated['model'])) {
            default => null,
        };

        if ($enumClass === null) {
            return response()->json(['message' => __('api.model_not_found')], 404);
        }

        $search = $request->validated('search');

        $statuses = collect($enumClass::cases())
            ->map(fn ($case): array => [
                'value' => $case->value,
                'label' => method_exists($case, 'label') ? $case->label() : Str::headline($case->name),
            ])
            ->when($search, fn ($collection) => $collection->filter(function (array $status) use ($search): bool {
                if (Str::contains($status['label'], $search, true)) {
                    return true;
                }

                return Str::contains($status['value'], $search, true);
            }))
            ->values();

        return $this->successResponse($statuses);
    }
}
