<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    public function schema(Schema $schema): Schema
    {
        return CustomerForm::make($schema);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions moved to ViewCustomer page
        ];
    }
}
