<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\Pages\ViewPayment;
use App\Models\Payment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use App\Services\KashierService;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'reference';

    protected static ?int $navigationSort = 25;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.sales');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.payments.title');
    }

    public static function getModelLabel(): string
    {
        return __('admin.payments.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.payments.plural');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::pending()->count() ?: null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الدفعة')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('reference')
                                ->label('المرجع')
                                ->copyable(),
                            TextEntry::make('status')
                                ->label('الحالة')
                                ->badge()
                                ->color(fn($state) => match ($state) {
                                    'completed' => 'success',
                                    'pending' => 'warning',
                                    'failed' => 'danger',
                                    'refunded' => 'gray',
                                    default => 'primary',
                                })
                                ->formatStateUsing(fn($state) => match ($state) {
                                    'completed' => 'مكتمل',
                                    'pending' => 'معلق',
                                    'failed' => 'فاشل',
                                    'refunded' => 'مسترد',
                                    default => $state,
                                }),
                            TextEntry::make('amount')
                                ->label('المبلغ')
                                ->money('EGP'),
                        ]),
                        Grid::make(3)->schema([
                            TextEntry::make('payment_method')
                                ->label('طريقة الدفع')
                                ->formatStateUsing(fn($state) => match ($state) {
                                    'card' => 'بطاقة ائتمان',
                                    'vodafone_cash' => 'فودافون كاش',
                                    'instapay' => 'InstaPay',
                                    default => $state,
                                }),
                            TextEntry::make('gateway')
                                ->label('بوابة الدفع'),
                            TextEntry::make('created_at')
                                ->label('تاريخ الإنشاء')
                                ->dateTime('d/m/Y H:i'),
                        ]),
                    ]),

                Section::make('بيانات الطلب')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('order.order_number')
                                ->label('رقم الطلب')
                                ->url(fn($record) => $record->order_id
                                    ? route('filament.admin.resources.orders.view', $record->order_id)
                                    : null),
                            TextEntry::make('customer.name')
                                ->label('العميل'),
                            TextEntry::make('customer.email')
                                ->label('البريد الإلكتروني'),
                        ]),
                    ]),

                Section::make('بيانات البوابة')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('transaction_id')
                                ->label('رقم العملية')
                                ->copyable(),
                            TextEntry::make('gateway_transaction_id')
                                ->label('رقم عملية البوابة')
                                ->copyable(),
                        ]),
                        TextEntry::make('paid_at')
                            ->label('تاريخ الدفع')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->collapsed(),

                Section::make('بيانات الاسترداد')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('refunded_amount')
                                ->label('المبلغ المسترد')
                                ->money('EGP'),
                            TextEntry::make('refund_reference')
                                ->label('مرجع الاسترداد'),
                            TextEntry::make('refunded_at')
                                ->label('تاريخ الاسترداد')
                                ->dateTime('d/m/Y H:i'),
                        ]),
                    ])
                    ->visible(fn($record) => $record->refunded_amount > 0),

                Section::make('سبب الفشل')
                    ->schema([
                        TextEntry::make('failure_reason')
                            ->label('السبب'),
                        TextEntry::make('failure_code')
                            ->label('كود الخطأ'),
                    ])
                    ->visible(fn($record) => $record->status === 'failed'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('المرجع')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                TextColumn::make('order.order_number')
                    ->label('رقم الطلب')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('الطريقة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'card' => 'بطاقة',
                        'vodafone_cash' => 'فودافون كاش',
                        'orange_money' => 'أورانج',
                        'etisalat_cash' => 'اتصالات',
                        'meeza' => 'ميزة',
                        'valu' => 'ڤاليو',
                        default => $state,
                    }),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'processing' => 'info',
                        'failed' => 'danger',
                        'refunded', 'partially_refunded' => 'gray',
                        'expired', 'cancelled' => 'secondary',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'completed' => 'مكتمل',
                        'pending' => 'معلق',
                        'processing' => 'جاري',
                        'failed' => 'فاشل',
                        'refunded' => 'مسترد',
                        'partially_refunded' => 'مسترد جزئياً',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغي',
                        default => $state,
                    }),

                TextColumn::make('transaction_id')
                    ->label('رقم العملية')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'completed' => 'مكتمل',
                        'failed' => 'فاشل',
                        'refunded' => 'مسترد',
                    ]),

                SelectFilter::make('payment_method')
                    ->label('الطريقة')
                    ->options([
                        'card' => 'بطاقة',
                        'vodafone_cash' => 'فودافون كاش',
                        'orange_money' => 'أورانج',
                        'etisalat_cash' => 'اتصالات',
                        'meeza' => 'ميزة',
                        'valu' => 'ڤاليو',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),

                \Filament\Actions\Action::make('refund')
                    ->label('استرداد')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn($record) => $record->canBeRefunded())
                    ->requiresConfirmation()
                    ->modalHeading('استرداد المبلغ')
                    ->modalDescription('هل أنت متأكد من استرداد هذا المبلغ؟')
                    ->action(function (Payment $record) {
                        $kashier = new KashierService();
                        $result = $kashier->refund(
                            $record->gateway_transaction_id,
                            (float) $record->amount
                        );

                        if ($result['success']) {
                            $record->markAsRefunded((float) $record->amount, $result['data']['refundId'] ?? 'manual');

                            Notification::make()
                                ->title('تم استرداد المبلغ')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('فشل الاسترداد')
                                ->body($result['error'])
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->headerActions([
                ExportAction::make()->label('تصدير Excel'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'view' => ViewPayment::route('/{record}'),
        ];
    }
}
