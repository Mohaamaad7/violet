<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsVisitorsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null; // Do not poll to save API requests

    public function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        $visitors = AnalyticsService::getDailyVisitors();
        
        if (empty($visitors) || count($visitors) === 0) {
            return [
                Stat::make('بيانات غير متوفرة', 'يرجى مراجعة الإعدادات أو الانتظار')
                    ->description('لم نتمكن من جلب بيانات الزوار')
                    ->color('danger')
                    ->icon('heroicon-o-exclamation-triangle'),
            ];
        }

        // Collection comes back with 'date', 'pageTitle', 'visitors', 'pageViews'
        $totalVisitors = collect($visitors)->sum('activeUsers');
        $totalViews = collect($visitors)->sum('screenPageViews');

        // Spatie V5 structure is slightly different. If we use fetchTotalVisitorsAndPageViews
        // It returns a collection of associative arrays with 'date', 'activeUsers', 'screenPageViews'

        return [
            Stat::make('إجمالي الزوار (آخر 30 يوم)', number_format($totalVisitors))
                ->description('الزوار النشطين')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->icon('heroicon-o-users'),
                
            Stat::make('مشاهدات الصفحات (آخر 30 يوم)', number_format($totalViews))
                ->description('إجمالي المشاهدات')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success')
                ->icon('heroicon-o-eye'),
        ];
    }
}
