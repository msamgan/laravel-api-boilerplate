<?php

declare(strict_types=1);

namespace App\Actions\v1\Common;

use App\Actions\Action;
use Illuminate\Database\Eloquent\Model;

final readonly class DeleteAction extends Action
{
    /**
     * Handle the action.
     */
    public function handle(Model $model): bool
    {
        return (bool) $model->delete();
    }
}
