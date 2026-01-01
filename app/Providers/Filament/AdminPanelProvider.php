<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnforcePageAccess;
use App\Services\DashboardConfigurationService;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Admin Panel Provider - Zero-Config Permissions
 * 
 * This panel automatically filters:
 * - Widgets: Via DashboardConfigurationService in widget classes
 * - Resources: Via DashboardConfigurationService in resource classes  
 * - Pages: Via DashboardConfigurationService in page classes + middleware
 * 
 * The system uses a "defensive" approach:
 * - Even if developer forgets traits, the middleware protects access
 * - Components are auto-discovered and appear in Role Permissions
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

            // Auto-discover Resources
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')

            // Auto-discover Pages
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])

            // Auto-discover Widgets
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')

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
                EnforcePageAccess::class, // Protects direct URL access to denied pages
            ]);
    }
}
