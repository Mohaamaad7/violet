<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\Actions\ResetPasswordAction;
use App\Filament\Resources\Customers\Actions\SendEmailAction;
use App\Filament\Resources\Customers\Actions\ViewWishlistAction;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            SendEmailAction::make(),
            ResetPasswordAction::make(),
            ViewWishlistAction::make(),
            Action::make('block')
                ->label(fn(Customer $record) => $record->status === 'blocked' 
                    ? trans_db('admin.customers.actions.activate') 
                    : trans_db('admin.customers.actions.block'))
                ->icon(fn(Customer $record) => $record->status === 'blocked' 
                    ? 'heroicon-o-check-circle' 
                    : 'heroicon-o-no-symbol')
                ->color(fn(Customer $record) => $record->status === 'blocked' 
                    ? 'success' 
                    : 'danger')
                ->requiresConfirmation()
                ->action(function (Customer $record) {
                    $newStatus = $record->status === 'blocked' ? 'active' : 'blocked';
                    $record->update(['status' => $newStatus]);
                    
                    $this->refreshFormData([
                        'status',
                    ]);
                })
                ->visible(fn(Customer $record) => in_array($record->status, ['active', 'blocked'])),
            DeleteAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // معلومات العميل الأساسية
                Section::make(trans_db('admin.customers.sections.customer_info'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                ImageEntry::make('profile_photo_path')
                                    ->label(trans_db('admin.customers.fields.profile_photo'))
                                    ->circular()
                                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF')
                                    ->height(100)
                                    ->width(100),
                                
                                TextEntry::make('name')
                                    ->label(trans_db('admin.customers.fields.name'))
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large),
                                
                                TextEntry::make('email')
                                    ->label(trans_db('admin.customers.fields.email'))
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->copyMessage(trans_db('messages.copied')),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('phone')
                                    ->label(trans_db('admin.customers.fields.phone'))
                                    ->icon('heroicon-o-phone')
                                    ->placeholder(trans_db('messages.not_available')),
                                
                                TextEntry::make('status')
                                    ->label(trans_db('admin.customers.fields.status'))
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'active' => 'success',
                                        'blocked' => 'danger',
                                        'inactive' => 'warning',
                                    })
                                    ->formatStateUsing(fn(string $state): string => trans_db("admin.customers.status.{$state}")),
                                
                                TextEntry::make('locale')
                                    ->label(trans_db('admin.customers.fields.locale'))
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'ar' => '🇪🇬 العربية',
                                        'en' => '🇬🇧 English',
                                        default => $state,
                                    }),
                            ]),
                    ])
                    ->columns(1),

                // الإحصائيات
                Section::make(trans_db('admin.customers.sections.statistics'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('total_orders')
                                    ->label(trans_db('admin.customers.fields.total_orders'))
                                    ->icon('heroicon-o-shopping-bag')
                                    ->badge()
                                    ->color('info')
                                    ->formatStateUsing(fn($state) => number_format($state)),
                                
                                TextEntry::make('total_spent')
                                    ->label(trans_db('admin.customers.fields.total_spent'))
                                    ->icon('heroicon-o-currency-dollar')
                                    ->money('EGP')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large),
                                
                                TextEntry::make('last_order_at')
                                    ->label(trans_db('admin.customers.fields.last_order_at'))
                                    ->icon('heroicon-o-calendar')
                                    ->dateTime('d/m/Y - h:i A')
                                    ->placeholder(trans_db('messages.no_orders_yet')),
                            ]),
                    ])
                    ->columns(1),

                // آخر 5 طلبات
                Section::make(trans_db('admin.customers.sections.recent_orders'))
                    ->schema([
                        RepeatableEntry::make('orders')
                            ->label('')
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        TextEntry::make('order_number')
                                            ->label(trans_db('admin.orders.fields.order_number'))
                                            ->weight(FontWeight::Bold)
                                            ->url(fn($record) => route('filament.admin.resources.orders.view', $record)),
                                        
                                        TextEntry::make('total')
                                            ->label(trans_db('admin.orders.fields.total'))
                                            ->money('EGP'),
                                        
                                        TextEntry::make('status')
                                            ->label(trans_db('admin.orders.fields.status'))
                                            ->badge()
                                            ->formatStateUsing(fn($state) => $state->label()),
                                        
                                        TextEntry::make('payment_status')
                                            ->label(trans_db('admin.orders.fields.payment_status'))
                                            ->badge()
                                            ->formatStateUsing(fn($state) => trans_db("admin.orders.payment_status.{$state}")),
                                        
                                        TextEntry::make('created_at')
                                            ->label(trans_db('admin.orders.fields.created_at'))
                                            ->dateTime('d/m/Y'),
                                    ]),
                            ])
                            ->contained(false)
                            ->state(fn(Customer $record) => $record->orders()->latest()->take(5)->get()),
                    ])
                    ->collapsible()
                    ->visible(fn(Customer $record) => $record->orders()->count() > 0),

                // العناوين المحفوظة
                Section::make(trans_db('admin.customers.sections.addresses'))
                    ->schema([
                        RepeatableEntry::make('shippingAddresses')
                            ->label('')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('full_name')
                                            ->label(trans_db('admin.shipping_addresses.fields.full_name'))
                                            ->icon('heroicon-o-user')
                                            ->weight(FontWeight::Bold),
                                        
                                        TextEntry::make('phone')
                                            ->label(trans_db('admin.shipping_addresses.fields.phone'))
                                            ->icon('heroicon-o-phone'),
                                        
                                        TextEntry::make('is_default')
                                            ->label(trans_db('admin.shipping_addresses.fields.is_default'))
                                            ->badge()
                                            ->color('success')
                                            ->formatStateUsing(fn($state) => $state ? trans_db('messages.yes') : trans_db('messages.no'))
                                            ->visible(fn($state) => $state),
                                    ]),
                                
                                TextEntry::make('formatted_address')
                                    ->label(trans_db('admin.shipping_addresses.fields.address'))
                                    ->icon('heroicon-o-map-pin')
                                    ->columnSpanFull()
                                    ->state(function($record) {
                                        return sprintf(
                                            '%s، %s، %s',
                                            $record->street_address ?? '',
                                            $record->city ?? '',
                                            $record->governorate ?? ''
                                        );
                                    }),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible()
                    ->visible(fn(Customer $record) => $record->shippingAddresses()->count() > 0),

                // التواريخ
                Section::make(trans_db('admin.customers.sections.timestamps'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(trans_db('admin.customers.fields.created_at'))
                                    ->dateTime('d/m/Y - h:i A'),
                                
                                TextEntry::make('email_verified_at')
                                    ->label(trans_db('admin.customers.fields.email_verified_at'))
                                    ->dateTime('d/m/Y - h:i A')
                                    ->placeholder(trans_db('messages.not_verified')),
                                
                                TextEntry::make('updated_at')
                                    ->label(trans_db('admin.customers.fields.updated_at'))
                                    ->dateTime('d/m/Y - h:i A'),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }
}
