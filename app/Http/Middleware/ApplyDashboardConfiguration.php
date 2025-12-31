<?php

namespace App\Http\Middleware;

use App\Services\DashboardConfigurationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to apply dynamic dashboard configuration
 * 
 * Zero-Config Approach:
 * This middleware is now simplified - widgets and resources are 
 * discovered at runtime, not pre-loaded into session.
 */
class ApplyDashboardConfiguration
{
    public function __construct(
        protected DashboardConfigurationService $dashboardService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Zero-Config: No need to pre-load anything
        // Widgets and resources are discovered at runtime
        // Permissions are checked when each widget/resource is accessed

        return $next($request);
    }
}
