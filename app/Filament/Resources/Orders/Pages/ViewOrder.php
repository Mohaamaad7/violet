<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Eager load relations for better performance
        $this->record->load([
            'items.product.images',
            'user',
            'shippingAddress',
            'statusHistory.user'
        ]);
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateStatus')
                ->label('تغيير حالة الطلب')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->visible(fn () => auth()->user()->can('manage order status'))
                ->form([
                    Select::make('status')
                        ->label('الحالة الجديدة')
                        ->options([
                            'pending' => 'قيد الانتظار',
                            'processing' => 'قيد التجهيز',
                            'shipped' => 'تم الشحن',
                            'delivered' => 'تم التسليم',
                            'cancelled' => 'ملغي',
                        ])
                        ->default(fn () => $this->record->status)
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data, OrderService $orderService): void {
                    $orderService->updateStatus($this->record->id, $data['status']);
                    
                    Notification::make()
                        ->title('تم تحديث حالة الطلب بنجاح')
                        ->success()
                        ->send();
                    
                    $this->refreshFormData(['status']);
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Customer Details Section
                Section::make('بيانات العميل')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('اسم العميل')
                                    ->icon('heroicon-o-user')
                                    ->color('primary')
                                    ->weight('bold'),
                                
                                TextEntry::make('user.email')
                                    ->label('البريد الإلكتروني')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),
                                
                                TextEntry::make('user.phone')
                                    ->label('رقم الهاتف')
                                    ->icon('heroicon-o-phone')
                                    ->default('غير متوفر'),
                                
                                TextEntry::make('order_number')
                                    ->label('رقم الطلب')
                                    ->icon('heroicon-o-hashtag')
                                    ->copyable()
                                    ->weight('bold')
                                    ->color('success'),
                            ]),
                        
                        TextEntry::make('shippingAddress.full_address')
                            ->label('عنوان الشحن')
                            ->icon('heroicon-o-map-pin')
                            ->default('لم يتم تحديد عنوان')
                            ->columnSpanFull()
                            ->formatStateUsing(function ($record) {
                                if (!$record->shippingAddress) {
                                    return 'لم يتم تحديد عنوان الشحن';
                                }
                                
                                $address = $record->shippingAddress;
                                return sprintf(
                                    '%s، %s، %s، %s - الهاتف: %s',
                                    $address->address_line1 ?? '',
                                    $address->city ?? '',
                                    $address->state ?? '',
                                    $address->country ?? '',
                                    $address->phone ?? 'غير متوفر'
                                );
                            }),
                    ])
                    ->collapsible(),

                // Order Summary Section
                Section::make('ملخص الطلب')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('status')
                                    ->label('حالة الطلب')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'shipped' => 'primary',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pending' => 'قيد الانتظار',
                                        'processing' => 'قيد التجهيز',
                                        'shipped' => 'تم الشحن',
                                        'delivered' => 'تم التسليم',
                                        'cancelled' => 'ملغي',
                                        default => $state,
                                    }),
                                
                                TextEntry::make('payment_status')
                                    ->label('حالة الدفع')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'paid' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        'refunded' => 'info',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'paid' => 'مدفوع',
                                        'pending' => 'قيد الانتظار',
                                        'failed' => 'فشل',
                                        'refunded' => 'مسترد',
                                        default => $state ?? 'غير محدد',
                                    }),
                                
                                TextEntry::make('payment_method')
                                    ->label('طريقة الدفع')
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'cash' => 'نقدي',
                                        'credit_card' => 'بطاقة ائتمان',
                                        'bank_transfer' => 'تحويل بنكي',
                                        default => $state ?? 'غير محدد',
                                    })
                                    ->default('غير محدد'),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->label('الإجمالي الفرعي')
                                    ->money('EGP'),
                                
                                TextEntry::make('discount_amount')
                                    ->label('الخصم')
                                    ->money('EGP')
                                    ->color('success'),
                                
                                TextEntry::make('shipping_cost')
                                    ->label('تكلفة الشحن')
                                    ->money('EGP'),
                                
                                TextEntry::make('tax_amount')
                                    ->label('الضريبة')
                                    ->money('EGP'),
                            ]),
                        
                        TextEntry::make('total')
                            ->label('الإجمالي النهائي')
                            ->money('EGP')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->color('success')
                            ->columnSpanFull(),
                        
                        TextEntry::make('created_at')
                            ->label('تاريخ الطلب')
                            ->dateTime('d/m/Y - h:i A')
                            ->icon('heroicon-o-calendar'),
                    ])
                    ->collapsible(),

                // Order Items Section
                Section::make('المنتجات المطلوبة')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        ImageEntry::make('product_image')
                                            ->label('الصورة')
                                            ->disk('public')
                                            ->height(60)
                                            ->width(60)
                                            ->state(function ($record) {
                                                // Get first product image or return default
                                                if ($record->product && $record->product->images->isNotEmpty()) {
                                                    return $record->product->images->first()->image_path;
                                                }
                                                return 'products/default-product.svg';
                                            })
                                            ->defaultImageUrl(asset('storage/products/default-product.svg'))
                                            ->extraAttributes(['class' => 'rounded-lg']),
                                        
                                        TextEntry::make('product_name')
                                            ->label('اسم المنتج')
                                            ->weight('bold')
                                            ->url(fn ($record) => $record->product_id 
                                                ? route('filament.admin.resources.products.view', ['record' => $record->product_id]) 
                                                : null)
                                            ->color('primary')
                                            ->formatStateUsing(fn ($record) => $record->variant_name 
                                                ? "{$record->product_name} ({$record->variant_name})" 
                                                : $record->product_name),
                                        
                                        TextEntry::make('product_sku')
                                            ->label('SKU')
                                            ->copyable()
                                            ->icon('heroicon-o-hashtag'),
                                        
                                        TextEntry::make('quantity')
                                            ->label('الكمية')
                                            ->badge()
                                            ->color('info'),
                                        
                                        TextEntry::make('price')
                                            ->label('السعر')
                                            ->money('EGP'),
                                        
                                        TextEntry::make('subtotal')
                                            ->label('الإجمالي')
                                            ->money('EGP')
                                            ->weight('bold')
                                            ->color('success'),
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible(),

                // Status History Timeline Section
                Section::make('سجل تاريخ الطلب')
                    ->icon('heroicon-o-clock')
                    ->description('سجل جميع التغييرات التي حدثت على حالة الطلب')
                    ->schema([
                        RepeatableEntry::make('statusHistory')
                            ->label('')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('user.name')
                                            ->label('الموظف')
                                            ->icon('heroicon-o-user')
                                            ->default('النظام')
                                            ->weight('medium'),
                                        
                                        TextEntry::make('status')
                                            ->label('الحالة الجديدة')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'shipped' => 'primary',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                'pending' => 'قيد الانتظار',
                                                'processing' => 'قيد التجهيز',
                                                'shipped' => 'تم الشحن',
                                                'delivered' => 'تم التسليم',
                                                'cancelled' => 'ملغي',
                                                default => $state,
                                            }),
                                        
                                        TextEntry::make('created_at')
                                            ->label('الوقت')
                                            ->dateTime('d/m/Y - h:i A')
                                            ->icon('heroicon-o-calendar')
                                            ->color('gray'),
                                    ]),
                                
                                TextEntry::make('notes')
                                    ->label('ملاحظات')
                                    ->default('لا توجد ملاحظات')
                                    ->color('gray')
                                    ->columnSpanFull()
                                    ->visible(fn ($record) => !empty($record->notes)),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }
}
