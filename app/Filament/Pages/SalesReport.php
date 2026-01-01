<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ChecksPageAccess;
use App\Filament\Widgets\SalesReportStatsWidget;
use App\Models\Order;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class SalesReport extends Page implements HasTable
{
    use InteractsWithTable;
    use ChecksPageAccess;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.sales-report';

    public static function getNavigationLabel(): string
    {
        return __('admin.pages.sales_report.title');
    }

    public function getTitle(): string
    {
        return __('admin.pages.sales_report.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.sales');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesReportStatsWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->where('payment_status', 'paid')->with(['customer']))
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable(),

                TextColumn::make('customer_name')
                    ->label('العميل')
                    ->searchable(['customer.name', 'guest_name']),

                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cod' => 'عند الاستلام',
                        'card' => 'بطاقة',
                        'instapay' => 'InstaPay',
                        default => $state,
                    })
                    ->color('info'),

                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->color('success')
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->money('EGP')
                            ->label('إجمالي المبيعات'),
                    ]),

                TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // فلتر تاريخ البدء
                Filter::make('date_from')
                    ->form([
                        DatePicker::make('value')
                            ->label('تاريخ البدء'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        );
                    })
                    ->indicateUsing(
                        fn(array $data): ?string =>
                        $data['value'] ? 'من: ' . \Carbon\Carbon::parse($data['value'])->format('d/m/Y') : null
                    ),

                // فلتر تاريخ الانتهاء
                Filter::make('date_to')
                    ->form([
                        DatePicker::make('value')
                            ->label('تاريخ الانتهاء'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                    })
                    ->indicateUsing(
                        fn(array $data): ?string =>
                        $data['value'] ? 'إلى: ' . \Carbon\Carbon::parse($data['value'])->format('d/m/Y') : null
                    ),

                // فلتر طريقة الدفع
                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'cod' => 'الدفع عند الاستلام',
                        'card' => 'بطاقة ائتمان',
                        'instapay' => 'InstaPay',
                    ])
                    ->placeholder('الكل'),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير Excel')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('sales-report-' . now()->format('Y-m-d'))
                    ]),
            ]);
    }
}
