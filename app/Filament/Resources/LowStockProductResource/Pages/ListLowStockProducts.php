<?php

namespace App\Filament\Resources\LowStockProductResource\Pages;

use App\Filament\Resources\LowStockProductResource;
use Filament\Resources\Pages\ListRecords;

class ListLowStockProducts extends ListRecords
{
    protected static string $resource = LowStockProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions - this is a read-only filtered view
        ];
    }
}
