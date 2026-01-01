<?php

namespace App\Http\Middleware;

use App\Services\DashboardConfigurationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnforcePageAccess Middleware
 * 
 * Automatically checks page access for ALL Filament pages.
 * No need for developers to remember using BasePage or traits.
 * 
 * Works by:
 * 1. Detecting if current route is a Filament page
 * 2. Finding the page class from the route
 * 3. Checking access via DashboardConfigurationService
 * 4. Returning 403 if access denied
 */
class EnforcePageAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only check authenticated users
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Super admin bypasses all checks
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // Check if this is a Filament route
        $route = $request->route();

        if (!$route) {
            return $next($request);
        }

        // Get the page class from route action
        $controller = $route->getAction('controller');

        if (!$controller) {
            return $next($request);
        }

        // Extract the page class (Filament pages use the class as controller)
        $pageClass = is_string($controller) ? explode('@', $controller)[0] : null;

        if (!$pageClass) {
            return $next($request);
        }

        // Check if it's a Filament page
        if (!class_exists($pageClass) || !is_subclass_of($pageClass, \Filament\Pages\Page::class)) {
            return $next($request);
        }

        // Check access
        $service = app(DashboardConfigurationService::class);

        if (!$service->canAccessPage($pageClass)) {
            abort(403, __('admin.access_denied'));
        }

        return $next($request);
    }
}
