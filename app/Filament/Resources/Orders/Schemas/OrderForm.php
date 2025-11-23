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
                    ->label(__('admin.form.order_number'))
                    ->required(),
                Select::make('user_id')
                    ->label(__('admin.form.customer'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->default(null),
                Select::make('discount_code_id')
                    ->label(__('admin.form.discount_code'))
                    ->relationship('discountCode', 'id')
                    ->default(null),
                Select::make('status')
                    ->label(__('admin.table.order_status'))
                    ->options([
                        'pending' => __('admin.orders.status.pending'),
                        'processing' => __('admin.orders.status.processing'),
                        'shipped' => __('admin.orders.status.shipped'),
                        'delivered' => __('admin.orders.status.delivered'),
                        'cancelled' => __('admin.orders.status.cancelled'),
                    ])
                    ->default('pending')
                    ->required(),
                Select::make('payment_status')
                    ->label(__('admin.table.payment_status'))
                    ->options([
                        'unpaid' => __('admin.orders.payment.unpaid'),
                        'paid' => __('admin.orders.payment.paid'),
                        'failed' => __('admin.orders.payment.failed'),
                        'refunded' => __('admin.orders.payment.refunded'),
                    ])
                    ->default('unpaid')
                    ->required(),
                Select::make('payment_method')
                    ->label(__('admin.table.payment_method'))
                    ->options([
                        'cod' => __('admin.orders.method.cod'),
                        'card' => __('admin.orders.method.card'),
                        'instapay' => __('admin.orders.method.instapay'),
                    ])
                    ->default('cod')
                    ->required(),
                TextInput::make('subtotal')
                    ->label(__('admin.form.subtotal'))
                    ->required()
                    ->numeric(),
                TextInput::make('discount_amount')
                    ->label(__('admin.form.discount_amount'))
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('shipping_cost')
                    ->label(__('admin.form.shipping_cost'))
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_amount')
                    ->label(__('admin.form.tax_amount'))
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->label(__('admin.form.total'))
                    ->required()
                    ->numeric(),
                Textarea::make('notes')
                    ->label(__('admin.form.notes'))
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('admin_notes')
                    ->label(__('admin.form.admin_notes'))
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('payment_transaction_id')
                    ->label(__('admin.form.payment_transaction_id'))
                    ->default(null),
                DateTimePicker::make('paid_at')
                    ->label(__('admin.form.paid_at')),
                DateTimePicker::make('shipped_at')
                    ->label(__('admin.form.shipped_at')),
                DateTimePicker::make('delivered_at')
                    ->label(__('admin.form.delivered_at')),
                DateTimePicker::make('cancelled_at')
                    ->label(__('admin.form.cancelled_at')),
                Textarea::make('cancellation_reason')
                    ->label(__('admin.form.cancellation_reason'))
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
