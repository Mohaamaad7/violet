<?php

namespace App\Providers\Filament;

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
            // Place brand logo inside the sidebar header (Filament v4)
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3.5rem')
            // Brand color palette switched to Violet (Filament Support Colors\Color)
            ->colors([
                'primary' => Color::Violet,
            ])
            // Premium font (Google Fonts Cairo – professional Arabic/English)
            ->font('Cairo', 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap')
            // Custom Vite theme CSS (luxury Violet admin overrides)
            ->viteTheme('resources/css/filament/admin/theme.css')
            // Enable global search in topbar (HasGlobalSearch trait)
            ->globalSearch()
            // Improve UX: collapsible sidebar on desktop for spacious content area
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            // Widgets are discovered but filtered by canView() on each widget class
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
            ]);
    }

    /**
     * Get widgets for the current authenticated user
     * Falls back to default widgets if user is not authenticated
     */
    protected function getWidgetsForCurrentUser(): array
    {
        // Always include AccountWidget
        $widgets = [AccountWidget::class];

        // If user is authenticated, get their configured widgets
        if (auth()->check()) {
            try {
                $service = app(DashboardConfigurationService::class);
                $user = auth()->user();

                $configuredWidgets = $service->getWidgetClassesForUser($user);

                // Filter to only include classes that exist
                foreach ($configuredWidgets as $widgetClass) {
                    if (class_exists($widgetClass) && !in_array($widgetClass, $widgets)) {
                        $widgets[] = $widgetClass;
                    }
                }
            } catch (\Throwable $e) {
                // If service fails, log error and use default discovery
                \Log::warning('DashboardConfigurationService failed: ' . $e->getMessage());
            }
        }

        return $widgets;
    }
}
