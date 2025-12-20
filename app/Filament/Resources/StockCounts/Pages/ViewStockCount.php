<?php

namespace App\Filament\Resources\StockCounts\Pages;

use App\Enums\StockCountStatus;
use App\Enums\VarianceReasonType;
use App\Filament\Resources\StockCounts\StockCountResource;
use App\Models\User;
use App\Services\StockCountService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Livewire\Attributes\On;

class ViewStockCount extends ViewRecord
{
    protected static string $resource = StockCountResource::class;

    /**
     * Listen for item updates to refresh page
     */
    #[On('stock-count-item-updated')]
    public function refreshPage(): void
    {
        $this->record->refresh();
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('inventory.stock_count_info'))
                    ->schema([
                        TextEntry::make('code')
                            ->label(__('inventory.count_code'))
                            ->badge()
                            ->color('primary')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('warehouse.name')
                            ->label(__('inventory.warehouse')),

                        TextEntry::make('type')
                            ->label(__('inventory.count_type'))
                            ->formatStateUsing(fn($state) => $state?->label())
                            ->badge(),

                        TextEntry::make('status')
                            ->label('الحالة')
                            ->formatStateUsing(fn($state) => $state?->label())
                            ->badge()
                            ->color(fn($state) => $state?->color()),

                        TextEntry::make('total_items')
                            ->label(__('inventory.total_items'))
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('progress')
                            ->label(__('inventory.progress'))
                            ->formatStateUsing(fn($state) => $state . '%')
                            ->badge()
                            ->color(fn($state) => $state >= 100 ? 'success' : 'warning'),

                        TextEntry::make('createdBy.name')
                            ->label(__('admin.created_by')),

                        TextEntry::make('created_at')
                            ->label(__('admin.created_at'))
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(4),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // PDF: Count Sheet (only during counting - IN_PROGRESS)
            Action::make('printCountSheet')
                ->label('طباعة ورقة الجرد')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->visible(fn() => $this->record->status === StockCountStatus::IN_PROGRESS)
                ->url(fn() => route('admin.stock-counts.count-sheet', $this->record))
                ->openUrlInNewTab(),

            // PDF: Results Report (after completion/approval)
            Action::make('printResults')
                ->label('طباعة تقرير الجرد')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->visible(fn() => in_array($this->record->status, [StockCountStatus::COMPLETED, StockCountStatus::APPROVED]))
                ->url(fn() => route('admin.stock-counts.results', $this->record))
                ->openUrlInNewTab(),

            // PDF: Shortage Report (only if there are shortages)
            Action::make('printShortage')
                ->label('تقرير العجز')
                ->icon('heroicon-o-arrow-trending-down')
                ->color('danger')
                ->visible(fn() => $this->record->status === StockCountStatus::APPROVED
                    && $this->record->items()->where('difference', '<', 0)->exists())
                ->url(fn() => route('admin.stock-counts.shortage', $this->record))
                ->openUrlInNewTab(),

            // PDF: Excess Report (only if there are excesses)
            Action::make('printExcess')
                ->label('تقرير الزيادة')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('warning')
                ->visible(fn() => $this->record->status === StockCountStatus::APPROVED
                    && $this->record->items()->where('difference', '>', 0)->exists())
                ->url(fn() => route('admin.stock-counts.excess', $this->record))
                ->openUrlInNewTab(),

            // Start Count - only visible in DRAFT
            Action::make('start')
                ->label(__('inventory.start_count'))
                ->icon('heroicon-o-play')
                ->color('info')
                ->visible(fn() => $this->record->canStart())
                ->requiresConfirmation()
                ->modalHeading('بدء الجرد')
                ->modalDescription('هل أنت مستعد لبدء عملية الجرد؟ بعد البدء ستتمكن من إدخال الكميات المعدودة.')
                ->action(function () {
                    $service = app(StockCountService::class);
                    $service->startCount($this->record->id);

                    Notification::make()
                        ->success()
                        ->title(__('inventory.count_started'))
                        ->body('يمكنك الآن إدخال الكميات المعدودة في الجدول أدناه')
                        ->send();

                    $this->redirect(StockCountResource::getUrl('view', ['record' => $this->record]));
                }),

            // Complete Count - only visible in IN_PROGRESS, DISABLED if not 100%
            Action::make('complete')
                ->label(function () {
                    if (!$this->record->isFullyCounted()) {
                        $remaining = $this->record->total_items - $this->record->counted_items;
                        return __('inventory.complete_count') . " (متبقي {$remaining})";
                    }
                    return __('inventory.complete_count');
                })
                ->icon('heroicon-o-check')
                ->color('warning')
                ->visible(fn() => $this->record->canComplete())
                ->disabled(fn() => !$this->record->isFullyCounted())
                ->requiresConfirmation()
                ->modalHeading('إنهاء الجرد')
                ->modalDescription('سيتم إغلاق الجرد ولن تتمكن من تعديل الكميات بعد ذلك.')
                ->action(function () {

                    try {
                        $service = app(StockCountService::class);
                        $service->completeCount($this->record->id);

                        Notification::make()
                            ->success()
                            ->title(__('inventory.count_completed'))
                            ->body('يمكنك الآن مراجعة الفروقات واعتماد الجرد')
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title(__('admin.error'))
                            ->body($e->getMessage())
                            ->send();
                    }

                    $this->redirect(StockCountResource::getUrl('view', ['record' => $this->record]));
                }),

            // Approve Count - with variance reasons modal
            Action::make('approve')
                ->label(__('inventory.approve_count'))
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn() => $this->record->status === StockCountStatus::COMPLETED)
                ->modalHeading(__('inventory.approve_with_reasons'))
                ->modalDescription(__('inventory.select_reason_for_variance'))
                ->form(function () {
                    $varianceItems = $this->record->items()
                        ->whereNotNull('difference')
                        ->where('difference', '!=', 0)
                        ->with('product')
                        ->get();

                    if ($varianceItems->isEmpty()) {
                        return [
                            Placeholder::make('no_variance')
                                ->label('')
                                ->content(__('inventory.no_variance_items')),
                        ];
                    }

                    $formFields = [];
                    foreach ($varianceItems as $item) {
                        $isShortage = $item->difference < 0;
                        $options = $isShortage
                            ? VarianceReasonType::shortageOptions()
                            : VarianceReasonType::excessOptions();

                        $formFields[] = Section::make($item->display_name)
                            ->description(
                                'النظام: ' . $item->system_quantity .
                                ' | الفعلي: ' . $item->counted_quantity .
                                ' | الفرق: ' . ($item->difference > 0 ? '+' : '') . $item->difference
                            )
                            ->schema([
                                Hidden::make("items.{$item->id}.item_id")
                                    ->default($item->id),

                                Select::make("items.{$item->id}.reason_type")
                                    ->label(__('inventory.variance_reason'))
                                    ->options($options)
                                    ->required()
                                    ->live(),

                                Select::make("items.{$item->id}.responsible_id")
                                    ->label(__('inventory.responsible_employee'))
                                    ->options(User::pluck('name', 'id'))
                                    ->searchable()
                                    ->visible(function (callable $get) use ($item) {
                                        return $get("items.{$item->id}.reason_type") === VarianceReasonType::EMPLOYEE_LIABILITY->value;
                                    })
                                    ->required(function (callable $get) use ($item) {
                                        return $get("items.{$item->id}.reason_type") === VarianceReasonType::EMPLOYEE_LIABILITY->value;
                                    }),
                            ])
                            ->columns(2)
                            ->compact();
                    }

                    return $formFields;
                })
                ->action(function (array $data) {
                    $varianceReasons = [];

                    if (isset($data['items'])) {
                        foreach ($data['items'] as $itemId => $reasons) {
                            $varianceReasons[$itemId] = [
                                'reason_type' => $reasons['reason_type'] ?? null,
                                'responsible_id' => $reasons['responsible_id'] ?? null,
                            ];
                        }
                    }

                    $service = app(StockCountService::class);
                    $service->approveCount($this->record->id, $varianceReasons);

                    Notification::make()
                        ->success()
                        ->title(__('inventory.count_approved'))
                        ->body(__('inventory.stock_updated'))
                        ->send();

                    // Redirect to list page after approval
                    $this->redirect(StockCountResource::getUrl('index'));
                }),

            // Cancel Count - with reason required
            Action::make('cancel')
                ->label(__('inventory.cancel_count'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => $this->record->canCancel())
                ->modalHeading('إلغاء الجرد')
                ->modalDescription('يرجى توضيح سبب إلغاء هذا الجرد.')
                ->form([
                    \Filament\Forms\Components\Textarea::make('cancellation_reason')
                        ->label('سبب الإلغاء')
                        ->required()
                        ->rows(3)
                        ->placeholder('اكتب سبب إلغاء الجرد هنا...')
                ])
                ->action(function (array $data) {
                    $service = app(StockCountService::class);
                    $service->cancelCount($this->record->id, $data['cancellation_reason'] ?? null);

                    Notification::make()
                        ->warning()
                        ->title(__('inventory.count_cancelled'))
                        ->send();

                    // Redirect to list page
                    $this->redirect(StockCountResource::getUrl('index'));
                }),
        ];
    }
}
