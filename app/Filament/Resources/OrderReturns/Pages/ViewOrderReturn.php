<?php

namespace App\Filament\Resources\OrderReturns\Pages;

use App\Enums\ReturnStatus;
use App\Filament\Resources\OrderReturns\OrderReturnResource;
use App\Services\ReturnService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewOrderReturn extends ViewRecord
{
    protected static string $resource = OrderReturnResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load([
            'order.customer',
            'order.items.product',
            'items.product',
            'items.orderItem',
            'approvedBy',
            'completedBy',
        ]);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Approve Action
            Action::make('approve')
                ->label('الموافقة على المرتجع')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->status === ReturnStatus::PENDING)
                ->requiresConfirmation()
                ->modalHeading('الموافقة على طلب المرتجع')
                ->modalDescription('بعد الموافقة، يمكنك معالجة المرتجع واسترجاع المخزون.')
                ->modalIcon('heroicon-o-check-circle')
                ->form([
                    Textarea::make('admin_notes')
                        ->label('ملاحظات المسؤول')
                        ->placeholder('أي ملاحظات إضافية للفريق...')
                        ->rows(3),
                    Checkbox::make('notify_customer')
                        ->label('إرسال إشعار للعميل')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    app(ReturnService::class)->approveReturn(
                        $this->record->id,
                        auth()->id(),
                        $data['admin_notes'] ?? null
                    );

                    Notification::make()
                        ->success()
                        ->title('تمت الموافقة')
                        ->body('تمت الموافقة على طلب المرتجع. يمكنك الآن معالجته.')
                        ->send();

                    $this->redirect(ViewOrderReturn::getUrl(['record' => $this->record]));
                }),

            // Reject Action
            Action::make('reject')
                ->label('رفض المرتجع')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => $this->record->status === ReturnStatus::PENDING)
                ->requiresConfirmation()
                ->modalHeading('رفض طلب المرتجع')
                ->modalDescription('يرجى تحديد سبب الرفض. سيتم إعلام العميل.')
                ->modalIcon('heroicon-o-x-circle')
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('سبب الرفض')
                        ->required()
                        ->placeholder('اذكر سبب رفض طلب المرتجع...')
                        ->rows(3)
                        ->maxLength(500),
                    Checkbox::make('notify_customer')
                        ->label('إرسال إشعار للعميل')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    app(ReturnService::class)->rejectReturn(
                        $this->record->id,
                        auth()->id(),
                        $data['rejection_reason']
                    );

                    Notification::make()
                        ->success()
                        ->title('تم الرفض')
                        ->body('تم رفض طلب المرتجع.')
                        ->send();

                    $this->redirect(ViewOrderReturn::getUrl(['record' => $this->record]));
                }),

            // Process Action (Most Complex)
            Action::make('process')
                ->label('معالجة المرتجع')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('primary')
                ->visible(fn() => $this->record->status === ReturnStatus::APPROVED)
                ->modalHeading('معالجة طلب المرتجع')
                ->modalDescription('حدد حالة كل منتج واختر ما إذا كنت تريد إعادته للمخزون.')
                ->modalIcon('heroicon-o-cog-6-tooth')
                ->modalWidth('xl')
                ->schema(function () {
                    // Force load items
                    $this->record->load('items.product');
                    $items = $this->record->items;
                    $fields = [];

                    if ($items->isEmpty()) {
                        // Show message if no items
                        return [
                            Section::make('لا توجد أصناف')
                                ->description('لم يتم إضافة أصناف لهذا المرتجع. تحقق من إنشاء المرتجع بشكل صحيح.')
                                ->schema([])
                        ];
                    }

                    foreach ($items as $item) {
                        $fields[] = Section::make($item->product_name)
                            ->description("الكمية: {$item->quantity} | السعر: {$item->price} ج.م")
                            ->schema([
                                SchemaGrid::make(2)
                                    ->schema([
                                        Radio::make("items.{$item->id}.condition")
                                            ->label('حالة المنتج')
                                            ->options([
                                                'good' => '✅ جيد (قابل لإعادة البيع)',
                                                'opened' => '📦 مفتوح (قابل لإعادة البيع بخصم)',
                                                'damaged' => '❌ تالف (غير قابل للبيع)',
                                            ])
                                            ->default('good')
                                            ->required()
                                            ->inline(),
                                        Checkbox::make("items.{$item->id}.restock")
                                            ->label('إعادة للمخزون')
                                            ->default(true)
                                            ->helperText('سيتم إضافة الكمية للمخزون إذا كانت الحالة جيدة أو مفتوحة'),
                                    ]),
                            ])
                            ->collapsible();
                    }

                    return $fields;
                })
                ->action(function (array $data) {
                    $itemConditions = [];

                    foreach ($data['items'] ?? [] as $itemId => $itemData) {
                        $itemConditions[$itemId] = [
                            'condition' => $itemData['condition'] ?? 'good',
                            'restock' => $itemData['restock'] ?? false,
                        ];
                    }

                    $return = app(ReturnService::class)->processReturn(
                        $this->record->id,
                        $itemConditions,
                        auth()->id()
                    );

                    Notification::make()
                        ->success()
                        ->title('تمت المعالجة')
                        ->body("تمت معالجة المرتجع. مبلغ الاسترداد: {$return->refund_amount} ج.م")
                        ->send();

                    $this->redirect(ViewOrderReturn::getUrl(['record' => $this->record]));
                }),

            // View Order Action
            Action::make('viewOrder')
                ->label('عرض الطلب')
                ->icon('heroicon-o-shopping-bag')
                ->color('gray')
                ->url(fn() => $this->record->order ? route('filament.admin.resources.orders.view', $this->record->order) : null),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->record)
            ->columns(3)
            ->schema([
                // Section 1: Return Details
                Section::make('تفاصيل المرتجع')
                    ->icon('heroicon-o-document-text')
                    ->columnSpan(2)
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                TextEntry::make('return_number')
                                    ->label('رقم المرتجع')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->copyable(),

                                TextEntry::make('type')
                                    ->label('النوع')
                                    ->badge()
                                    ->color(fn($state) => $state?->color() ?? 'gray')
                                    ->formatStateUsing(fn($state) => $state?->label() ?? '-'),

                                TextEntry::make('status')
                                    ->label('الحالة')
                                    ->badge()
                                    ->color(fn($state) => $state?->color() ?? 'gray')
                                    ->formatStateUsing(fn($state) => $state?->label() ?? '-'),
                            ]),

                        TextEntry::make('reason')
                            ->label('سبب المرتجع')
                            ->columnSpanFull(),

                        TextEntry::make('customer_notes')
                            ->label('ملاحظات العميل')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),

                        TextEntry::make('admin_notes')
                            ->label('ملاحظات المسؤول')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ]),

                // Section 2: Customer & Order Info
                Section::make('معلومات العميل والطلب')
                    ->icon('heroicon-o-user')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('order.order_number')
                            ->label('رقم الطلب')
                            ->url(fn() => $this->record->order ? route('filament.admin.resources.orders.view', $this->record->order) : null)
                            ->color('primary')
                            ->icon('heroicon-o-arrow-top-right-on-square'),

                        TextEntry::make('order.customer_name')
                            ->label('اسم العميل')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('order.customer_email')
                            ->label('البريد الإلكتروني')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),

                        TextEntry::make('order.customer_phone')
                            ->label('رقم الهاتف')
                            ->icon('heroicon-o-phone')
                            ->copyable(),

                        TextEntry::make('order.total')
                            ->label('قيمة الطلب الأصلية')
                            ->money('EGP'),
                    ]),

                // Section 3: Return Items
                Section::make('المنتجات المرتجعة')
                    ->icon('heroicon-o-cube')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                SchemaGrid::make(6)
                                    ->schema([
                                        TextEntry::make('product_name')
                                            ->label('المنتج')
                                            ->weight('bold'),

                                        TextEntry::make('product_sku')
                                            ->label('SKU')
                                            ->color('gray'),

                                        TextEntry::make('quantity')
                                            ->label('الكمية')
                                            ->badge()
                                            ->color('info'),

                                        TextEntry::make('price')
                                            ->label('السعر')
                                            ->money('EGP'),

                                        TextEntry::make('condition')
                                            ->label('الحالة')
                                            ->badge()
                                            ->color(fn(?string $state): string => match ($state) {
                                                'good' => 'success',
                                                'opened' => 'warning',
                                                'damaged' => 'danger',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn(?string $state): string => match ($state) {
                                                'good' => 'جيد',
                                                'opened' => 'مفتوح',
                                                'damaged' => 'تالف',
                                                default => 'غير محدد',
                                            }),

                                        TextEntry::make('restocked')
                                            ->label('أُعيد للمخزون')
                                            ->badge()
                                            ->color(fn(bool $state): string => $state ? 'success' : 'gray')
                                            ->formatStateUsing(fn(bool $state): string => $state ? '✅ نعم' : '❌ لا'),
                                    ]),
                            ])
                            ->contained(false),

                        // Refund Summary
                        SchemaGrid::make(3)
                            ->schema([
                                TextEntry::make('refund_amount')
                                    ->label('مبلغ الاسترداد')
                                    ->money('EGP')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('success'),

                                TextEntry::make('refund_status')
                                    ->label('حالة الاسترداد')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'completed' => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'pending' => 'معلق',
                                        'completed' => 'تم الاسترداد',
                                        default => $state,
                                    }),
                            ]),
                    ]),

                // Section 4: Timeline
                Section::make('سجل الأحداث')
                    ->icon('heroicon-o-clock')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        SchemaGrid::make(4)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('تاريخ الإنشاء')
                                    ->dateTime('d/m/Y - h:i A')
                                    ->icon('heroicon-o-plus-circle')
                                    ->color('info'),

                                TextEntry::make('approved_at')
                                    ->label('تاريخ الموافقة')
                                    ->dateTime('d/m/Y - h:i A')
                                    ->placeholder('لم تتم الموافقة بعد')
                                    ->icon('heroicon-o-check-circle')
                                    ->color('success'),

                                TextEntry::make('approvedBy.name')
                                    ->label('تمت الموافقة بواسطة')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-user'),

                                TextEntry::make('completed_at')
                                    ->label('تاريخ الإكمال')
                                    ->dateTime('d/m/Y - h:i A')
                                    ->placeholder('لم يكتمل بعد')
                                    ->icon('heroicon-o-check-badge')
                                    ->color('success'),

                                TextEntry::make('completedBy.name')
                                    ->label('تمت المعالجة بواسطة')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-user'),
                            ]),
                    ]),
            ]);
    }
}
