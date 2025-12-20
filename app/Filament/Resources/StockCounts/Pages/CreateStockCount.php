<?php

namespace App\Filament\Resources\StockCounts\Pages;

use App\Enums\StockCountScope;
use App\Enums\StockCountType;
use App\Filament\Resources\StockCounts\StockCountResource;
use App\Services\StockCountService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateStockCount extends CreateRecord
{
    protected static string $resource = StockCountResource::class;

    protected Width|string|null $maxContentWidth = 'full';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $service = app(StockCountService::class);

        return $service->createCount(
            warehouseId: $data['warehouse_id'],
            type: StockCountType::from($data['type']),
            scope: isset($data['scope']) ? StockCountScope::from($data['scope']) : StockCountScope::ALL,
            scopeIds: $data['scope_ids'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
