<?php

namespace App\Filament\Resources\Products\Tables;

use App\Exports\ProductTemplateExport;
use App\Models\Product;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primary_image')
                    ->label(__('admin.table.image'))
                    ->getStateUsing(function (Product $record) {
                        // Try Spatie Media Library first
                        $primaryMedia = $record->getMedia('product-images')
                            ->filter(fn($media) => $media->getCustomProperty('is_primary') === true)
                            ->first();

                        if (!$primaryMedia) {
                            $primaryMedia = $record->getFirstMedia('product-images');
                        }

                        if ($primaryMedia) {
                            return $primaryMedia->hasGeneratedConversion('thumbnail')
                                ? $primaryMedia->getUrl('thumbnail')
                                : $primaryMedia->getUrl();
                        }

                        // Fallback to old system
                        $primaryImage = $record->images()->where('is_primary', true)->first();
                        return $primaryImage?->image_path
                            ? asset('storage/' . $primaryImage->image_path)
                            : asset('images/default-product.png');
                    })
                    ->size(50)
                    ->circular(),

                TextColumn::make('name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('sku')
                    ->label(__('admin.table.sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('SKU copied!')
                    ->toggleable(),

                TextColumn::make('category.name')
                    ->label(__('admin.table.category'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('price')
                    ->label(__('admin.table.price'))
                    ->money('EGP')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('sale_price')
                    ->label(__('admin.table.sale'))
                    ->money('EGP')
                    ->sortable()
                    ->color('warning')
                    ->toggleable()
                    ->default('—'),

                TextColumn::make('stock')
                    ->label(__('admin.table.stock'))
                    ->numeric()
                    ->sortable()
                    ->color(fn(int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    })
                    ->icon(fn(int $state): string => match (true) {
                        $state === 0 => 'heroicon-o-x-circle',
                        $state < 10 => 'heroicon-o-exclamation-triangle',
                        default => 'heroicon-o-check-circle',
                    }),

                TextColumn::make('status')
                    ->label(__('admin.table.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        'inactive' => 'danger',
                    }),

                IconColumn::make('is_featured')
                    ->label(__('admin.table.featured'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('admin.table.created_at'))
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Category Filter
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label(__('admin.table.category')),

                // Status Filter
                SelectFilter::make('status')
                    ->options([
                        'active' => __('admin.status.active'),
                        'draft' => __('admin.status.draft'),
                        'inactive' => __('admin.status.inactive'),
                    ])
                    ->multiple()
                    ->label(__('admin.table.status')),

                // Is Active/Featured Filter
                Filter::make('is_featured')
                    ->label(__('admin.filters.featured_only'))
                    ->query(fn(Builder $query): Builder => $query->where('is_featured', true))
                    ->toggle(),

                // Price Range Filter
                Filter::make('price_range')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('price_from')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder(__('admin.filters.min')),
                        \Filament\Forms\Components\TextInput::make('price_to')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder(__('admin.filters.max')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn(Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn(Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['price_from'] ?? null) {
                            $indicators['price_from'] = __('admin.filters.min') . ': $' . $data['price_from'];
                        }
                        if ($data['price_to'] ?? null) {
                            $indicators['price_to'] = __('admin.filters.max') . ': $' . $data['price_to'];
                        }
                        return $indicators;
                    }),

                // Stock Status Filter (for widget deep linking)
                SelectFilter::make('stock_status')
                    ->label(__('admin.filters.stock_status'))
                    ->options([
                        'low' => __('inventory.low_stock_products'),
                        'out' => __('inventory.out_of_stock'),
                        'available' => __('inventory.in_stock'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn(Builder $query, $value): Builder => match ($value) {
                                'low' => $query->whereColumn('stock', '<=', 'low_stock_threshold'),
                                'out' => $query->where('stock', 0),
                                'available' => $query->where('stock', '>', 0)->whereColumn('stock', '>', 'low_stock_threshold'),
                                default => $query,
                            }
                        );
                    }),

                // Low Stock Toggle Filter (legacy)
                Filter::make('low_stock')
                    ->label(__('admin.filters.low_stock_only'))
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->whereColumn('stock', '<=', 'low_stock_threshold')
                    )
                    ->toggle(),

                // Trashed Filter
                TrashedFilter::make(),
            ])
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->label('تصدير Excel')
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                            ->fromModel()
                            ->withFilename('products-' . now()->format('Y-m-d'))
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn($record) => auth()->user()->can('update', $record)),
                ReplicateAction::make()
                    ->label(__('admin.action.duplicate'))
                    ->visible(fn($record) => auth()->user()->can('create', $record))
                    ->excludeAttributes(['sku', 'slug'])
                    ->beforeReplicaSaved(function (Product $replica): void {
                        $replica->name = $replica->name . ' (Copy)';
                        $replica->slug = $replica->slug . '-copy-' . time();
                        $replica->sku = null; // Will auto-generate
                        $replica->status = 'draft';
                    }),
                DeleteAction::make()
                    ->visible(fn($record) => auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Publish/Unpublish Actions
                    BulkAction::make('publish')
                        ->label(__('admin.action.publish_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn() => auth()->user()->can('edit products'))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'active']);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('admin.message.published')),

                    BulkAction::make('unpublish')
                        ->label(__('admin.action.unpublish_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->visible(fn() => auth()->user()->can('edit products'))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'inactive']);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('admin.message.unpublished')),

                    BulkAction::make('set_featured')
                        ->label(__('admin.action.mark_featured'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->visible(fn() => auth()->user()->can('edit products'))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['is_featured' => true]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('admin.message.featured')),

                    BulkAction::make('unset_featured')
                        ->label(__('admin.action.remove_featured'))
                        ->icon('heroicon-o-minus-circle')
                        ->color('gray')
                        ->visible(fn() => auth()->user()->can('edit products'))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['is_featured' => false]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('admin.message.unfeatured')),

                    // Export Selected as Template (for updates)
                    BulkAction::make('export_template')
                        ->label(__('admin.import.export_selected_template'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function (Collection $records) {
                            return Excel::download(
                                new ProductTemplateExport('update', $records),
                                'products-selected-' . now()->format('Y-m-d-His') . '.xlsx'
                            );
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->can('delete products')),
                    ForceDeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->can('delete products')),
                    RestoreBulkAction::make()
                        ->visible(fn() => auth()->user()->can('edit products')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }
}
