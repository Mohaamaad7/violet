<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AnalyticsTopReferrersWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 3;

    protected static ?string $heading = 'مصادر الزيارات (آخر 30 يوم)';

    protected function getTableRecords(): \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator
    {
        $referrers = AnalyticsService::getTopReferrers();
        
        return collect($referrers)->map(function ($item, $index) {
            return (object) [
                'id' => $index,
                'pageReferrer' => $item['pageReferrer'] ?? 'مباشر (Direct) أو غير معروف',
                'screenPageViews' => $item['screenPageViews'] ?? 0,
            ];
        });
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\User::query()->whereRaw('1 = 0')) // Dummy query
            ->columns([
                Tables\Columns\TextColumn::make('pageReferrer')
                    ->label('المصدر')
                    ->limit(50),
                Tables\Columns\TextColumn::make('screenPageViews')
                    ->label('المشاهدات')
                    ->numeric()
                    ->badge()
                    ->color('info'),
            ])
            ->paginated(false);
    }
}
