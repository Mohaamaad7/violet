<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class AnalyticsTopPagesWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 2;

    protected static ?string $heading = 'أكثر الصفحات زيارة (آخر 30 يوم)';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|null
    {
        return null;
    }

    public function getTableRecords(): \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator
    {
        $pages = AnalyticsService::getTopPages();
        
        // Convert to pseudo-models or objects for Filament Table
        return collect($pages)->map(function ($item, $index) {
            return (object) [
                'id' => $index,
                'pageTitle' => $item['pageTitle'] ?? 'غير معروف',
                'fullPageUrl' => $item['fullPageUrl'] ?? '/',
                'screenPageViews' => $item['screenPageViews'] ?? 0,
            ];
        });
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // We provide an empty query since we're using getTableRecords directly.
                // However, Filament requires a valid builder if query is missing. Let's work around it:
                \App\Models\User::query()->whereRaw('1 = 0') // Dummy query
            )
            ->columns([
                Tables\Columns\TextColumn::make('pageTitle')
                    ->label('الصفحة')
                    ->limit(50),
                Tables\Columns\TextColumn::make('fullPageUrl')
                    ->label('الرابط')
                    ->limit(40)
                    ->color('gray'),
                Tables\Columns\TextColumn::make('screenPageViews')
                    ->label('المشاهدات')
                    ->numeric()
                    ->badge()
                    ->color('success'),
            ])
            ->paginated(false);
    }
}
