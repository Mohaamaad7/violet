<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnforcePageAccess;
use App\Services\DashboardConfigurationService;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Admin Panel Provider - Zero-Config Permissions with Auto-Filtering Navigation
 * 
 * This provider automatically filters navigation items based on permissions.
 * No traits or base classes required in individual Resources/Pages!
 * 
 * How it works:
 * 1. Discovers all Resources and Pages from filesystem
 * 2. Checks each one against DashboardConfigurationService
 * 3. Only shows items the user has permission to access
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // Brand identity
            ->brandName('Violet')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3.5rem')
            ->colors([
                'primary' => Color::Violet,
            ])
            ->font('Cairo', 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->globalSearch()
            ->sidebarCollapsibleOnDesktop()

            // Auto-discover Resources (needed for routing, filtering happens in navigation())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')

            // Auto-discover Pages (needed for routing, filtering happens in navigation())
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])

            // Auto-discover Widgets
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')

            // ⭐ MAGIC: Custom navigation builder with automatic permission filtering
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $this->buildFilteredNavigation($builder);
            })

            // Middleware stack
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\SetLocale::class,
                \App\Http\Middleware\ApplyDashboardConfiguration::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnforcePageAccess::class, // Backup protection for direct URL access
            ]);
    }

    /**
     * Build navigation with automatic permission filtering
     * 
     * This is the MAGIC method that makes Zero-Config work!
     */
    protected function buildFilteredNavigation(NavigationBuilder $builder): NavigationBuilder
    {
        $user = auth()->user();

        // If no user, return empty navigation
        if (!$user) {
            return $builder;
        }

        // Super admin sees everything - use default navigation
        if ($user->hasRole('super-admin')) {
            return $this->buildDefaultNavigation($builder);
        }

        $service = app(DashboardConfigurationService::class);
        $groups = [];

        // 1. Add Dashboard (always visible)
        $groups[''] = [
            NavigationItem::make('Dashboard')
                ->icon('heroicon-o-home')
                ->url(route('filament.admin.pages.dashboard'))
                ->isActiveWhen(fn() => request()->routeIs('filament.admin.pages.dashboard')),
        ];

        // 2. Process all Resources
        $allResources = $service->discoverAllResources();
        foreach ($allResources as $resourceClass) {
            if (!$this->shouldShowResource($resourceClass, $service)) {
                continue;
            }

            $navItem = $this->buildResourceNavigationItem($resourceClass);
            if ($navItem) {
                $group = $this->getNavigationGroup($resourceClass);
                if (!isset($groups[$group])) {
                    $groups[$group] = [];
                }
                $groups[$group][] = $navItem;
            }
        }

        // 3. Process all Pages
        $allPages = $service->discoverAllPages();
        foreach ($allPages as $pageClass) {
            // Skip Dashboard (already added)
            if ($pageClass === Dashboard::class || is_subclass_of($pageClass, Dashboard::class)) {
                continue;
            }

            if (!$this->shouldShowPage($pageClass, $service)) {
                continue;
            }

            $navItem = $this->buildPageNavigationItem($pageClass);
            if ($navItem) {
                $group = $this->getPageNavigationGroup($pageClass);
                if (!isset($groups[$group])) {
                    $groups[$group] = [];
                }
                $groups[$group][] = $navItem;
            }
        }

        // 4. Build navigation groups
        foreach ($groups as $groupLabel => $items) {
            if (empty($items)) {
                continue;
            }

            if (empty($groupLabel)) {
                // Items without group
                foreach ($items as $item) {
                    $builder->items([$item]);
                }
            } else {
                $builder->group($groupLabel, $items);
            }
        }

        return $builder;
    }

    /**
     * Build default navigation for super-admin
     */
    protected function buildDefaultNavigation(NavigationBuilder $builder): NavigationBuilder
    {
        $service = app(DashboardConfigurationService::class);
        $groups = [];

        // Dashboard
        $groups[''] = [
            NavigationItem::make('Dashboard')
                ->icon('heroicon-o-home')
                ->url(route('filament.admin.pages.dashboard'))
                ->isActiveWhen(fn() => request()->routeIs('filament.admin.pages.dashboard')),
        ];

        // All Resources
        $allResources = $service->discoverAllResources();
        foreach ($allResources as $resourceClass) {
            $navItem = $this->buildResourceNavigationItem($resourceClass);
            if ($navItem) {
                $group = $this->getNavigationGroup($resourceClass);
                if (!isset($groups[$group])) {
                    $groups[$group] = [];
                }
                $groups[$group][] = $navItem;
            }
        }

        // All Pages
        $allPages = $service->discoverAllPages();
        foreach ($allPages as $pageClass) {
            if ($pageClass === Dashboard::class || is_subclass_of($pageClass, Dashboard::class)) {
                continue;
            }

            $navItem = $this->buildPageNavigationItem($pageClass);
            if ($navItem) {
                $group = $this->getPageNavigationGroup($pageClass);
                if (!isset($groups[$group])) {
                    $groups[$group] = [];
                }
                $groups[$group][] = $navItem;
            }
        }

        // Build groups
        foreach ($groups as $groupLabel => $items) {
            if (empty($items)) {
                continue;
            }

            if (empty($groupLabel)) {
                foreach ($items as $item) {
                    $builder->items([$item]);
                }
            } else {
                $builder->group($groupLabel, $items);
            }
        }

        return $builder;
    }

    /**
     * Check if resource should be shown in navigation
     */
    protected function shouldShowResource(string $resourceClass, DashboardConfigurationService $service): bool
    {
        // Skip BaseResource
        if (Str::endsWith($resourceClass, 'BaseResource')) {
            return false;
        }

        return $service->canAccessResource($resourceClass, 'can_view');
    }

    /**
     * Check if page should be shown in navigation
     */
    protected function shouldShowPage(string $pageClass, DashboardConfigurationService $service): bool
    {
        // Skip BasePage
        if (Str::endsWith($pageClass, 'BasePage')) {
            return false;
        }

        return $service->canAccessPage($pageClass);
    }

    /**
     * Build navigation item for a Resource
     */
    protected function buildResourceNavigationItem(string $resourceClass): ?NavigationItem
    {
        try {
            if (!class_exists($resourceClass)) {
                return null;
            }

            $label = method_exists($resourceClass, 'getNavigationLabel')
                ? $resourceClass::getNavigationLabel()
                : class_basename($resourceClass);

            $icon = method_exists($resourceClass, 'getNavigationIcon')
                ? $resourceClass::getNavigationIcon()
                : 'heroicon-o-rectangle-stack';

            $sort = method_exists($resourceClass, 'getNavigationSort')
                ? $resourceClass::getNavigationSort()
                : null;

            $url = method_exists($resourceClass, 'getUrl')
                ? $resourceClass::getUrl()
                : null;

            if (!$url) {
                return null;
            }

            $item = NavigationItem::make($label)
                ->icon($icon)
                ->url($url);

            if ($sort !== null) {
                $item->sort($sort);
            }

            return $item;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Build navigation item for a Page
     */
    protected function buildPageNavigationItem(string $pageClass): ?NavigationItem
    {
        try {
            if (!class_exists($pageClass)) {
                return null;
            }

            $label = method_exists($pageClass, 'getNavigationLabel')
                ? $pageClass::getNavigationLabel()
                : class_basename($pageClass);

            $icon = method_exists($pageClass, 'getNavigationIcon')
                ? $pageClass::getNavigationIcon()
                : 'heroicon-o-document-text';

            $sort = method_exists($pageClass, 'getNavigationSort')
                ? $pageClass::getNavigationSort()
                : null;

            $url = method_exists($pageClass, 'getUrl')
                ? $pageClass::getUrl()
                : null;

            if (!$url) {
                return null;
            }

            $item = NavigationItem::make($label)
                ->icon($icon)
                ->url($url);

            if ($sort !== null) {
                $item->sort($sort);
            }

            return $item;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get navigation group for a Resource
     */
    protected function getNavigationGroup(string $resourceClass): string
    {
        if (method_exists($resourceClass, 'getNavigationGroup')) {
            return $resourceClass::getNavigationGroup() ?? '';
        }
        return '';
    }

    /**
     * Get navigation group for a Page
     */
    protected function getPageNavigationGroup(string $pageClass): string
    {
        if (method_exists($pageClass, 'getNavigationGroup')) {
            return $pageClass::getNavigationGroup() ?? '';
        }
        return '';
    }
}
