<?php

namespace App\Filament\Resources\Products\Tables;

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

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primary_image')
                    ->label('Image')
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
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('SKU copied!')
                    ->toggleable(),
                
                TextColumn::make('category.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('price')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                
                TextColumn::make('sale_price')
                    ->label('Sale')
                    ->money('USD')
                    ->sortable()
                    ->color('warning')
                    ->toggleable()
                    ->default('—'),
                
                TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    })
                    ->icon(fn (int $state): string => match (true) {
                        $state === 0 => 'heroicon-o-x-circle',
                        $state < 10 => 'heroicon-o-exclamation-triangle',
                        default => 'heroicon-o-check-circle',
                    }),
                
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        'inactive' => 'danger',
                    }),
                
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->toggleable(),
                
                TextColumn::make('created_at')
                    ->label('Created')
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
                    ->label('Category'),
                
                // Status Filter
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'draft' => 'Draft',
                        'inactive' => 'Inactive',
                    ])
                    ->multiple()
                    ->label('Status'),
                
                // Is Active/Featured Filter
                Filter::make('is_featured')
                    ->label('Featured Only')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
                    ->toggle(),
                
                // Price Range Filter
                Filter::make('price_range')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('price_from')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('Min'),
                        \Filament\Forms\Components\TextInput::make('price_to')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('Max'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['price_from'] ?? null) {
                            $indicators['price_from'] = 'Min: $' . $data['price_from'];
                        }
                        if ($data['price_to'] ?? null) {
                            $indicators['price_to'] = 'Max: $' . $data['price_to'];
                        }
                        return $indicators;
                    }),
                
                // Low Stock Filter
                Filter::make('low_stock')
                    ->label('Low Stock Only')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereColumn('stock', '<=', 'low_stock_threshold')
                    )
                    ->toggle(),
                
                // Trashed Filter
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record) => auth()->user()->can('update', $record)),
                ReplicateAction::make()
                    ->label('Duplicate')
                    ->visible(fn ($record) => auth()->user()->can('create', $record))
                    ->excludeAttributes(['sku', 'slug'])
                    ->beforeReplicaSaved(function (Product $replica): void {
                        $replica->name = $replica->name . ' (Copy)';
                        $replica->slug = $replica->slug . '-copy-' . time();
                        $replica->sku = null; // Will auto-generate
                        $replica->status = 'draft';
                    }),
                DeleteAction::make()
                    ->visible(fn ($record) => auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Publish/Unpublish Actions
                    BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn () => auth()->user()->can('edit products'))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'active']);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Products published successfully'),
                    
                    BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->visible(fn () => auth()->user()->can('edit products'))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'inactive']);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Products unpublished successfully'),
                    
                    BulkAction::make('set_featured')
                        ->label('Mark as Featured')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->visible(fn () => auth()->user()->can('edit products'))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['is_featured' => true]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Products marked as featured'),
                    
                    BulkAction::make('unset_featured')
                        ->label('Remove from Featured')
                        ->icon('heroicon-o-minus-circle')
                        ->color('gray')
                        ->visible(fn () => auth()->user()->can('edit products'))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['is_featured' => false]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Products removed from featured'),
                    
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('delete products')),
                    ForceDeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('delete products')),
                    RestoreBulkAction::make()
                        ->visible(fn () => auth()->user()->can('edit products')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }
}
