<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return CustomersTable::make($table)
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with(['orders' => function ($query) {
                    $query->latest('created_at')->limit(1);
                }]);
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            // لا نحتاج Create Action - العملاء يسجلون من الموقع
        ];
    }
}
