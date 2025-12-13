<?php

namespace App\Filament\Resources\OutOfStockProductResource\Pages;

use App\Filament\Resources\OutOfStockProductResource;
use Filament\Resources\Pages\ListRecords;

class ListOutOfStockProducts extends ListRecords
{
    protected static string $resource = OutOfStockProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions - this is a read-only filtered view
        ];
    }
}
