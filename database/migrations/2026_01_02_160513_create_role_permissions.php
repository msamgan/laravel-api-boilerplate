<?php

declare(strict_types=1);

use App\Actions\Permission\CreatePermissionAction;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
        ];

        app(CreatePermissionAction::class)->handle($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
