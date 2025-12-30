<?php

namespace App\Http\Middleware;

use App\Services\DashboardConfigurationService;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to apply dynamic navigation group ordering based on user role
 */
class ApplyDashboardConfiguration
{
    public function __construct(
        protected DashboardConfigurationService $dashboardService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Get navigation groups for user and store in session for views
            $navGroups = $this->dashboardService->getNavigationGroupsForUser($user);
            session(['dashboard_nav_groups' => $navGroups]);

            // Get accessible resources
            $resources = $this->dashboardService->getResourcesForUser($user);
            session(['dashboard_resources' => $resources]);
        }

        return $next($request);
    }
}
