<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DashboardConfigurationService;
use Illuminate\Console\Command;

/**
 * Reset user preferences to role defaults
 */
class DashboardResetUser extends Command
{
    protected $signature = 'dashboard:reset-user 
                            {user : User ID or email}
                            {--confirm : Skip confirmation prompt}';

    protected $description = 'Reset user widget preferences to their role defaults';

    public function handle(DashboardConfigurationService $service): int
    {
        $userIdentifier = $this->argument('user');

        // Find user by ID or email
        $user = is_numeric($userIdentifier)
            ? User::find($userIdentifier)
            : User::where('email', $userIdentifier)->first();

        if (!$user) {
            $this->error("User not found: {$userIdentifier}");
            return Command::FAILURE;
        }

        $this->info("User: {$user->name} ({$user->email})");
        $this->info("Roles: " . $user->roles->pluck('name')->join(', '));
        $this->newLine();

        if (!$this->option('confirm') && !$this->confirm('Are you sure you want to reset this user\'s widget preferences?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        // Delete user preferences
        $deleted = $user->widgetPreferences()->delete();

        // Clear cache
        $service->clearUserCache($user);

        $this->info("✅ Deleted {$deleted} user preferences");
        $this->info("✅ Cache cleared for user");
        $this->newLine();
        $this->info("User will now use role defaults.");

        return Command::SUCCESS;
    }
}
