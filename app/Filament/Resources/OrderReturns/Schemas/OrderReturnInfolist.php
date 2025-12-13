<?php

namespace App\Filament\Resources\OrderReturns\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderReturnInfolist
{
    public static function configure(Schema $schema): Schema
    {
        // The infolist is now configured in ViewOrderReturn page
        // This file is kept for backwards compatibility
        return $schema->components([]);
    }
}
