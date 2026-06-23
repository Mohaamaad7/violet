<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ChecksPageAccess;
use App\Filament\Widgets\AnalyticsTopCountriesWidget;
use App\Filament\Widgets\AnalyticsTopPagesWidget;
use App\Filament\Widgets\AnalyticsTopReferrersWidget;
use App\Filament\Widgets\AnalyticsVisitorsWidget;
use Filament\Pages\Page;

class AnalyticsDashboard extends Page
{
    use ChecksPageAccess;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('admin.pages.analytics_dashboard.title') ?? 'إحصائيات جوجل';
    }

    public function getTitle(): string
    {
        return __('admin.pages.analytics_dashboard.title') ?? 'إحصائيات جوجل (Google Analytics)';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.sales') ?? 'التقارير والإحصائيات'; // Or 'التقارير'
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AnalyticsVisitorsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            AnalyticsTopPagesWidget::class,
            AnalyticsTopReferrersWidget::class,
            AnalyticsTopCountriesWidget::class,
        ];
    }
}
