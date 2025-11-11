<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(null),
                Select::make('discount_code_id')
                    ->relationship('discountCode', 'id')
                    ->default(null),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ])
                    ->default('pending')
                    ->required(),
                Select::make('payment_status')
                    ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'failed' => 'Failed', 'refunded' => 'Refunded'])
                    ->default('unpaid')
                    ->required(),
                Select::make('payment_method')
                    ->options(['cod' => 'Cod', 'card' => 'Card', 'instapay' => 'Instapay'])
                    ->default('cod')
                    ->required(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('shipping_cost')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('admin_notes')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('payment_transaction_id')
                    ->default(null),
                DateTimePicker::make('paid_at'),
                DateTimePicker::make('shipped_at'),
                DateTimePicker::make('delivered_at'),
                DateTimePicker::make('cancelled_at'),
                Textarea::make('cancellation_reason')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
