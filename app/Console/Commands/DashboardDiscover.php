<?php

namespace App\Console\Commands;

use App\Services\DashboardConfigurationService;
use Illuminate\Console\Command;

/**
 * Discover and register widgets, resources, and navigation groups
 */
class DashboardDiscover extends Command
{
    protected $signature = 'dashboard:discover 
                            {--widgets : Discover widgets only}
                            {--resources : Discover resources only}
                            {--nav-groups : Discover navigation groups only}';

    protected $description = 'Auto-discover and register widgets, resources, and navigation groups';

    public function handle(DashboardConfigurationService $service): int
    {
        $discoverAll = !$this->option('widgets') && !$this->option('resources') && !$this->option('nav-groups');

        $this->info('🔍 Starting discovery...');
        $this->newLine();

        // Discover Navigation Groups first (other things depend on them)
        if ($discoverAll || $this->option('nav-groups')) {
            $this->info('📁 Discovering navigation groups...');
            $count = $service->discoverNavigationGroups();
            $this->info("   ✅ Registered {$count} new navigation groups");
            $this->newLine();
        }

        // Discover Widgets
        if ($discoverAll || $this->option('widgets')) {
            $this->info('🧩 Discovering widgets...');
            $count = $service->discoverWidgets();
            $this->info("   ✅ Registered {$count} new widgets");
            $this->newLine();
        }

        // Discover Resources
        if ($discoverAll || $this->option('resources')) {
            $this->info('📦 Discovering resources...');
            $count = $service->discoverResources();
            $this->info("   ✅ Registered {$count} new resources");
            $this->newLine();
        }

        $this->info('✨ Discovery complete!');

        return Command::SUCCESS;
    }
}
