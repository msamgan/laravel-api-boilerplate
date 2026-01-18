<?php

declare(strict_types=1);

namespace App\Actions\v1\Common;

use Illuminate\Database\Eloquent\Model;

final class ToggleActiveAction
{
    public function handle(Model $model): Model
    {
        $model->update([
            'is_active' => ! $model->is_active,
        ]);

        return $model;
    }
}
