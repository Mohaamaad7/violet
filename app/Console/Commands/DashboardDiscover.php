<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Dashboard Discover Command - DEPRECATED
 * 
 * This command is no longer needed with the Zero-Config approach.
 * Widgets and resources are now discovered automatically at runtime.
 * 
 * Kept for backward compatibility - now just clears cache.
 */
class DashboardDiscover extends Command
{
    protected $signature = 'dashboard:discover 
                            {--widgets : (deprecated) No effect}
                            {--resources : (deprecated) No effect}
                            {--nav-groups : (deprecated) No effect}';

    protected $description = '(Deprecated) Clear dashboard cache to refresh auto-discovery';

    public function handle(): int
    {
        $this->info('🔄 Zero-Config Mode Active');
        $this->newLine();

        $this->info('📝 In Zero-Config mode, widgets and resources are discovered automatically.');
        $this->info('   No manual registration is needed!');
        $this->newLine();

        // Just clear the cache to force re-discovery
        Cache::forget('all_widget_classes');
        Cache::forget('all_resource_classes');
        Cache::flush();

        $this->info('✅ Cache cleared. New widgets/resources will be discovered on next page load.');
        $this->newLine();

        $this->warn('💡 This command is deprecated. New widgets/resources appear automatically.');

        return Command::SUCCESS;
    }
}
