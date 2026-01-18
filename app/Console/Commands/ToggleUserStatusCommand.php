<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

final class ToggleUserStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:toggle-status {email} {--active= : Set to true or false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle a user\'s active status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $active = $this->option('active');

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("User with email {$email} not found.");

            return 1;
        }

        $user->is_active = $active === null ? ! $user->is_active : filter_var($active, FILTER_VALIDATE_BOOLEAN);

        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        $this->info("User {$email} has been {$status}.");

        return 0;
    }
}
