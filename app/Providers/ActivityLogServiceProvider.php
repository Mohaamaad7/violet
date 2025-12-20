<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add IP, user agent, and device type to every activity log entry
        Activity::saving(function (Activity $activity) {
            $activity->ip_address = config('activitylog.context.ip_address');
            $activity->user_agent = config('activitylog.context.user_agent');
            $activity->device_type = config('activitylog.context.device_type');
        });
    }
}
