<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AnalyticsTopCountriesWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 4;

    protected static ?string $heading = 'أكثر الدول زيارة (آخر 30 يوم)';

    public function getTableRecords(): \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator
    {
        $countries = AnalyticsService::getTopCountries();
        
        return collect($countries)->map(function ($item, $index) {
            return (object) [
                'id' => $index,
                'country' => $item['country'] ?? 'غير معروف',
                'activeUsers' => $item['activeUsers'] ?? 0,
            ];
        });
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\User::query()->whereRaw('1 = 0')) // Dummy query
            ->columns([
                Tables\Columns\TextColumn::make('country')
                    ->label('الدولة')
                    ->limit(50),
                Tables\Columns\TextColumn::make('activeUsers')
                    ->label('الزوار')
                    ->numeric()
                    ->badge()
                    ->color('warning'),
            ])
            ->paginated(false);
    }
}
