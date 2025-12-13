<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\OrderService;
use App\Services\ReturnService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\HtmlString;

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
            'statusHistory.user',
            'returns'
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
                ->visible(fn() => auth()->user()->can('manage order status'))
                ->form([
                    Select::make('status')
                        ->label('الحالة الجديدة')
                        ->options(function () {
                            $currentStatus = $this->record->status?->toString() ?? 'pending';
                            $allStatuses = [
                                'pending' => 'قيد الانتظار',
                                'processing' => 'قيد التجهيز',
                                'shipped' => 'تم الشحن',
                                'delivered' => 'تم التسليم',
                                'cancelled' => 'ملغي',
                                'rejected' => 'مرفوض',
                            ];

                            // Remove statuses that cannot be reverted to
                            $disabledStatuses = [];

                            // If already shipped, can only move forward to delivered
                            if ($currentStatus === 'shipped') {
                                return ['delivered' => 'تم التسليم'];
                            }

                            // If delivered, cannot change (final state)
                            if ($currentStatus === 'delivered') {
                                return [$currentStatus => $allStatuses[$currentStatus]];
                            }

                            // If cancelled or rejected, cannot change (final state)
                            if (in_array($currentStatus, ['cancelled', 'rejected'])) {
                                return [$currentStatus => $allStatuses[$currentStatus]];
                            }

                            // For pending/processing, show next logical states
                            if ($currentStatus === 'pending') {
                                return [
                                    'processing' => 'قيد التجهيز',
                                    'shipped' => 'تم الشحن',
                                    'cancelled' => 'ملغي',
                                ];
                            }

                            if ($currentStatus === 'processing') {
                                return [
                                    'shipped' => 'تم الشحن',
                                    'cancelled' => 'ملغي',
                                ];
                            }

                            return $allStatuses;
                        })
                        ->default(fn() => $this->record->status?->toString() ?? 'pending')
                        ->required()
                        ->native(false)
                        ->disableOptionWhen(fn($value) => $value === ($this->record->status?->toString() ?? '')),
                ])
                ->modalSubmitActionLabel('تأكيد التغيير')
                ->before(function (array $data, OrderService $orderService): void {
                    // Prevent shipment if stock is insufficient
                    $currentStatusString = $this->record->status?->toString() ?? '';
                    if (isset($data['status']) && $data['status'] === 'shipped' && $currentStatusString !== 'shipped') {
                        $validation = $orderService->validateStockForShipment($this->record);

                        if (!$validation['canShip']) {
                            Notification::make()
                                ->title('لا يمكن شحن الطلب')
                                ->body('المخزون غير كافي لبعض المنتجات')
                                ->danger()
                                ->send();

                            $this->halt();
                        }
                    }
                })
                ->action(function (array $data, OrderService $orderService): void {
                    $orderService->updateStatus($this->record->id, $data['status']);

                    Notification::make()
                        ->title('تم تحديث حالة الطلب بنجاح')
                        ->success()
                        ->send();

                    // Refresh the entire page to show updated stock status
                    redirect()->to($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),

            // Create Return Request Action
            Action::make('createReturnRequest')
                ->label(function () {
                    return match ($this->record->status) {
                        OrderStatus::SHIPPED => 'رفض الاستلام',
                        OrderStatus::DELIVERED => 'طلب مرتجع',
                        default => 'إنشاء طلب مرتجع'
                    };
                })
                ->icon('heroicon-o-arrow-uturn-left')
                ->color(fn() => $this->record->status === OrderStatus::SHIPPED ? 'danger' : 'warning')
                ->visible(
                    fn() =>
                    in_array($this->record->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED]) &&
                    $this->record->return_status === 'none'
                )
                ->modalHeading(function () {
                    return match ($this->record->status) {
                        OrderStatus::SHIPPED => 'رفض استلام الطلب',
                        OrderStatus::DELIVERED => 'طلب استرجاع بعد التسليم',
                        default => 'إنشاء طلب مرتجع'
                    };
                })
                ->modalDescription(function () {
                    return match ($this->record->status) {
                        OrderStatus::SHIPPED => 'املأ البيانات التالية لرفض استلام الطلب',
                        OrderStatus::DELIVERED => 'املأ البيانات التالية لإنشاء طلب استرجاع بعد التسليم',
                        default => 'املأ البيانات التالية لإنشاء طلب مرتجع'
                    };
                })
                ->modalIcon('heroicon-o-arrow-uturn-left')
                ->modalWidth('lg')
                ->form([
                    // نوع المرتجع يُحدد تلقائيًا حسب حالة الطلب
                    Select::make('type')
                        ->label('نوع المرتجع')
                        ->options(function () {
                            return match ($this->record->status) {
                                OrderStatus::SHIPPED => ['rejection' => '🔴 رفض استلام'],
                                OrderStatus::DELIVERED => ['return_after_delivery' => '🟡 استرجاع بعد التسليم'],
                                default => [
                                    'rejection' => '🔴 رفض استلام',
                                    'return_after_delivery' => '🟡 استرجاع بعد التسليم',
                                ]
                            };
                        })
                        ->default(function () {
                            return match ($this->record->status) {
                                OrderStatus::SHIPPED => 'rejection',
                                OrderStatus::DELIVERED => 'return_after_delivery',
                                default => null
                            };
                        })
                        ->required()
                        ->native(false)
                        ->disabled(fn() => in_array($this->record->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED]))
                        ->dehydrated()
                        ->helperText(function () {
                            return match ($this->record->status) {
                                OrderStatus::SHIPPED => 'نوع المرتجع محدد تلقائيًا: رفض الاستلام',
                                OrderStatus::DELIVERED => 'نوع المرتجع محدد تلقائيًا: استرجاع بعد التسليم',
                                default => 'اختر نوع المرتجع حسب حالة الطلب'
                            };
                        }),

                    Textarea::make('reason')
                        ->label(fn() => $this->record->status === 'shipped' ? 'سبب رفض الاستلام' : 'سبب المرتجع')
                        ->required()
                        ->rows(3)
                        ->placeholder(fn() => $this->record->status === 'shipped'
                            ? 'اذكر سبب رفض العميل للاستلام (مثل: تأخير الشحن، العميل لا يرد، ألغى الطلب...)'
                            : 'اذكر سبب المرتجع بالتفصيل...')
                        ->maxLength(500),

                    // ملاحظة توضيحية عند رفض الاستلام
                    \Filament\Forms\Components\Placeholder::make('rejection_notice')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString('
                            <div class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">
                                <p class="text-warning-700 dark:text-warning-400 font-medium">
                                    ⚠️ سيتم إرجاع <strong>جميع أصناف الطلب</strong> تلقائياً عند رفض الاستلام.
                                </p>
                                <p class="text-sm text-warning-600 dark:text-warning-500 mt-1">
                                    المخزون سيُحدَّث عند موافقة المدير على طلب الرفض.
                                </p>
                            </div>
                        '))
                        ->visible(fn() => $this->record->status === 'shipped'),

                    // استخدام Repeater بدلاً من CheckboxList لدعم الكميات الجزئية
                    // يظهر فقط في حالة الاسترجاع بعد التسليم (delivered)
                    \Filament\Forms\Components\Repeater::make('items')
                        ->label('المنتجات المراد إرجاعها')
                        ->schema([
                            Select::make('order_item_id')
                                ->label('المنتج')
                                ->options(function () {
                                    return $this->record->items->mapWithKeys(function ($item) {
                                        return [
                                            $item->id => "{$item->product_name} (السعر: {$item->price} ج.م)"
                                        ];
                                    });
                                })
                                ->required()
                                ->reactive()
                                ->searchable()
                                ->native(false)
                                ->disableOptionWhen(function ($value, $get, $component) {
                                    // منع اختيار نفس المنتج أكثر من مرة
                                    $selectedItems = collect($component->getContainer()->getParentComponent()->getState())
                                        ->pluck('order_item_id')
                                        ->filter();
                                    return $selectedItems->contains($value) && $get('order_item_id') != $value;
                                }),

                            \Filament\Forms\Components\TextInput::make('quantity')
                                ->label('الكمية المراد إرجاعها')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->reactive()
                                ->default(function ($get) {
                                    $orderItemId = $get('order_item_id');
                                    if ($orderItemId) {
                                        $orderItem = $this->record->items->find($orderItemId);
                                        return $orderItem?->quantity ?? 1;
                                    }
                                    return 1;
                                })
                                ->maxValue(function ($get) {
                                    $orderItemId = $get('order_item_id');
                                    if ($orderItemId) {
                                        $orderItem = $this->record->items->find($orderItemId);
                                        return $orderItem?->quantity ?? 1;
                                    }
                                    return 1;
                                })
                                ->helperText(function ($get) {
                                    $orderItemId = $get('order_item_id');
                                    if ($orderItemId) {
                                        $orderItem = $this->record->items->find($orderItemId);
                                        if ($orderItem && $orderItem->quantity > 1) {
                                            return "الكمية المطلوبة في الطلب: {$orderItem->quantity} قطعة";
                                        }
                                    }
                                    return null;
                                })
                                ->visible(function ($get) {
                                    // إظهار حقل الكمية فقط إذا كانت الكمية المطلوبة > 1
                                    $orderItemId = $get('order_item_id');
                                    if ($orderItemId) {
                                        $orderItem = $this->record->items->find($orderItemId);
                                        return $orderItem && $orderItem->quantity > 1;
                                    }
                                    return false;
                                }),
                        ])
                        ->columns(2)
                        ->defaultItems(1)
                        ->addActionLabel('إضافة منتج آخر')
                        ->reorderable(false)
                        ->collapsible()
                        ->itemLabel(
                            fn(array $state): ?string =>
                            $state['order_item_id']
                            ? $this->record->items->find($state['order_item_id'])?->product_name
                            : 'منتج جديد'
                        )
                        ->helperText('اختر المنتجات والكميات التي يرغب العميل في إرجاعها')
                        ->minItems(1)
                        ->required(fn() => $this->record->status === 'delivered')
                        ->visible(fn() => $this->record->status === 'delivered'),

                    Textarea::make('customer_notes')
                        ->label('ملاحظات العميل')
                        ->rows(2)
                        ->placeholder('أي ملاحظات إضافية من العميل...')
                        ->maxLength(500),
                ])
                ->action(function (array $data, ReturnService $returnService) {
                    try {
                        $return = $returnService->createReturnRequest($this->record->id, $data);

                        Notification::make()
                            ->success()
                            ->title('تم إنشاء طلب المرتجع بنجاح')
                            ->body("رقم المرتجع: {$return->return_number}")
                            ->send();

                        // Redirect to view return page
                        redirect()->to(route('filament.admin.resources.order-returns.view', $return));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('خطأ في إنشاء المرتجع')
                            ->body($e->getMessage())
                            ->send();
                    }
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
                                TextEntry::make('customer_name')
                                    ->label('اسم العميل')
                                    ->icon('heroicon-o-user')
                                    ->color('primary')
                                    ->weight('bold')
                                    ->state(fn($record) => $record->user?->name
                                        ?? $record->shippingAddress?->full_name
                                        ?? $record->guest_name
                                        ?? 'زائر'),

                                TextEntry::make('customer_email')
                                    ->label('البريد الإلكتروني')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->state(fn($record) => $record->user?->email
                                        ?? $record->shippingAddress?->email
                                        ?? $record->guest_email
                                        ?? 'غير متوفر'),

                                TextEntry::make('customer_phone')
                                    ->label('رقم الهاتف')
                                    ->icon('heroicon-o-phone')
                                    ->state(fn($record) => $record->user?->phone
                                        ?? $record->shippingAddress?->phone
                                        ?? $record->guest_phone
                                        ?? 'غير متوفر'),

                                TextEntry::make('order_number')
                                    ->label('رقم الطلب')
                                    ->icon('heroicon-o-hashtag')
                                    ->copyable()
                                    ->weight('bold')
                                    ->color('success'),
                            ]),

                        TextEntry::make('shipping_address_display')
                            ->label('عنوان الشحن')
                            ->icon('heroicon-o-map-pin')
                            ->columnSpanFull()
                            ->state(function ($record) {
                                // Check for linked shipping address first
                                if ($record->shippingAddress) {
                                    $address = $record->shippingAddress;
                                    return sprintf(
                                        '%s، %s، %s، %s - الهاتف: %s',
                                        $address->street_address ?? $address->address_line1 ?? '',
                                        $address->city ?? '',
                                        $address->governorate ?? $address->state ?? '',
                                        $address->country ?? 'مصر',
                                        $address->phone ?? 'غير متوفر'
                                    );
                                }

                                // Fall back to guest address fields
                                if ($record->guest_address || $record->guest_city) {
                                    return sprintf(
                                        '%s، %s، %s - الهاتف: %s',
                                        $record->guest_address ?? '',
                                        $record->guest_city ?? '',
                                        $record->guest_governorate ?? '',
                                        $record->guest_phone ?? 'غير متوفر'
                                    );
                                }

                                return 'لم يتم تحديد عنوان الشحن';
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
                                    ->color(fn($state) => $state?->color() ?? 'gray')
                                    ->formatStateUsing(fn($state) => $state?->label() ?? '-'),

                                TextEntry::make('payment_status')
                                    ->label('حالة الدفع')
                                    ->badge()
                                    ->color(fn(?string $state): string => match ($state) {
                                        'paid' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        'refunded' => 'info',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                                        'paid' => 'مدفوع',
                                        'pending' => 'قيد الانتظار',
                                        'failed' => 'فشل',
                                        'refunded' => 'مسترد',
                                        default => $state ?? 'غير محدد',
                                    }),

                                TextEntry::make('payment_method')
                                    ->label('طريقة الدفع')
                                    ->formatStateUsing(fn(?string $state): string => match ($state) {
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
                                            ->url(fn($record) => $record->product_id
                                                ? route('filament.admin.resources.products.view', ['record' => $record->product_id])
                                                : null)
                                            ->color('primary')
                                            ->formatStateUsing(fn($record) => $record->variant_name
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

                // Stock Status Section (visible after shipment)
                Section::make('حالة المخزون')
                    ->icon('heroicon-o-cube')
                    ->description('تفاصيل خصم واسترجاع المخزون للطلب')
                    ->visible(fn($record) => in_array($record->status, ['shipped', 'delivered', 'rejected']))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('stock_deducted_at')
                                    ->label('تاريخ خصم المخزون')
                                    ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y - h:i A') : 'لم يتم الخصم')
                                    ->icon('heroicon-o-arrow-down-circle')
                                    ->color(fn($state) => $state ? 'success' : 'gray')
                                    ->badge()
                                    ->visible(fn($record) => in_array($record->status, ['shipped', 'delivered'])),

                                TextEntry::make('stock_restored_at')
                                    ->label('تاريخ استرجاع المخزون')
                                    ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y - h:i A') : 'لم يتم الاسترجاع')
                                    ->icon('heroicon-o-arrow-up-circle')
                                    ->color(fn($state) => $state ? 'warning' : 'gray')
                                    ->badge()
                                    ->visible(fn($record) => $record->status === 'rejected'),
                            ]),

                        // Show current stock for each item
                        RepeatableEntry::make('items')
                            ->label('المخزون الحالي للمنتجات')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('product_name')
                                            ->label('المنتج')
                                            ->weight('bold'),

                                        TextEntry::make('quantity')
                                            ->label('الكمية المطلوبة')
                                            ->badge()
                                            ->color('info'),

                                        TextEntry::make('current_stock')
                                            ->label('المخزون الحالي')
                                            ->state(fn($record) => $record->product?->stock ?? 'N/A')
                                            ->badge()
                                            ->color(function ($record) {
                                                if (!$record->product)
                                                    return 'gray';
                                                $stock = $record->product->stock;
                                                if ($stock <= 0)
                                                    return 'danger';
                                                if ($stock < 10)
                                                    return 'warning';
                                                return 'success';
                                            }),

                                        TextEntry::make('stock_status')
                                            ->label('حالة المخزون')
                                            ->state(function ($record) {
                                                if (!$record->product)
                                                    return 'المنتج غير موجود';
                                                $stock = $record->product->stock;
                                                if ($stock <= 0)
                                                    return 'نفذ من المخزون';
                                                if ($stock < $record->quantity)
                                                    return 'غير كافي';
                                                return 'متوفر';
                                            })
                                            ->badge()
                                            ->color(function ($record) {
                                                if (!$record->product)
                                                    return 'gray';
                                                $stock = $record->product->stock;
                                                if ($stock <= 0)
                                                    return 'danger';
                                                if ($stock < $record->quantity)
                                                    return 'warning';
                                                return 'success';
                                            }),
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                // Returns Section
                Section::make('المرتجعات')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->description('طلبات المرتجعات المرتبطة بهذا الطلب')
                    ->visible(fn($record) => $record->returns->isNotEmpty())
                    ->schema([
                        RepeatableEntry::make('returns')
                            ->label('')
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        TextEntry::make('return_number')
                                            ->label('رقم المرتجع')
                                            ->weight('bold')
                                            ->copyable()
                                            ->url(fn($record) => route('filament.admin.resources.order-returns.view', $record))
                                            ->color('primary')
                                            ->icon('heroicon-o-arrow-top-right-on-square'),

                                        TextEntry::make('type')
                                            ->label('النوع')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'rejection' => 'danger',
                                                'return_after_delivery' => 'warning',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                                'rejection' => '🔴 رفض',
                                                'return_after_delivery' => '🟡 استرجاع',
                                                default => $state,
                                            }),

                                        TextEntry::make('status')
                                            ->label('الحالة')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'pending' => 'warning',
                                                'approved' => 'info',
                                                'rejected' => 'danger',
                                                'completed' => 'success',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                                'pending' => 'قيد المراجعة',
                                                'approved' => 'تمت الموافقة',
                                                'rejected' => 'مرفوض',
                                                'completed' => 'مكتمل',
                                                default => $state,
                                            }),

                                        TextEntry::make('refund_amount')
                                            ->label('مبلغ الاسترداد')
                                            ->money('EGP')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('created_at')
                                            ->label('تاريخ الإنشاء')
                                            ->dateTime('d/m/Y')
                                            ->icon('heroicon-o-calendar'),
                                    ]),

                                TextEntry::make('reason')
                                    ->label('السبب')
                                    ->columnSpanFull()
                                    ->color('gray'),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible()
                    ->collapsed(false),

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
                                            ->color(fn($state) => is_object($state) ? $state->color() : match ($state) {
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'shipped' => 'primary',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn($state) => is_object($state) ? $state->label() : match ($state) {
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
                                    ->visible(fn($record) => !empty($record->notes)),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }
}
