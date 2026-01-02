<?php

namespace App\Providers\Filament;

use App\Filament\Partners\Pages\InfluencerDashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Partners Panel Provider - Dashboard for Influencers
 * 
 * Access: /partners
 * Users: Only users with 'influencer' role and active status
 */
class InfluencerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('partners')
            ->path('partners')
            ->login()
            ->authGuard('web')

            // Brand Identity - Different from Admin
            ->brandName('Flower Violet Partners')
            ->brandLogoHeight('3.5rem')
            ->colors([
                'primary' => Color::Purple,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->font('Cairo', 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap')

            // Simple navigation - only dashboard
            ->pages([
                InfluencerDashboard::class,
            ])

            // Widgets are defined in the dashboard page
            ->widgets([])

            // Disable sidebar (single page app)
            ->sidebarCollapsibleOnDesktop(false)

            // Middleware
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
