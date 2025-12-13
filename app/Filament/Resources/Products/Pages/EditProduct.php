<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\ProductService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            
            Action::make('add_stock')
                ->label(__('inventory.add_stock'))
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
                    TextInput::make('quantity')
                        ->label(__('inventory.quantity'))
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->default(1),
                    
                    Textarea::make('notes')
                        ->label(__('inventory.notes'))
                        ->placeholder(__('inventory.notes_placeholder'))
                        ->maxLength(500),
                ])
                ->action(function (array $data, ProductService $productService) {
                    $productService->addStock(
                        $this->record->id,
                        $data['quantity'],
                        $data['notes'] ?? null,
                        null
                    );
                    
                    Notification::make()
                        ->success()
                        ->title(__('inventory.stock_added_successfully'))
                        ->body(__('inventory.added_units', ['quantity' => $data['quantity']]))
                        ->send();
                    
                    $this->refreshFormData(['stock']);
                }),
            
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * Mutate form data before filling the form
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Repeater will automatically load images from the relationship
        return $data;
    }

    /**
     * Mutate form data before saving
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure at least one image is marked as primary
        if (isset($data['images']) && !empty($data['images'])) {
            $hasPrimary = false;
            foreach ($data['images'] as $index => &$image) {
                if ($image['is_primary'] ?? false) {
                    $hasPrimary = true;
                    break;
                }
            }
            
            // If no primary image is set, make the first one primary
            if (!$hasPrimary) {
                $data['images'][0]['is_primary'] = true;
            }
        }
        
        return $data;
    }

    /**
     * Handle record update using ProductService
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $productService = app(ProductService::class);
        
        // Extract variants data
        $variants = $data['variants'] ?? [];
        unset($data['variants']);
        
        // Filament Repeater handles images relationship automatically
        // Just update the product
        $record->update($data);
        
        // Sync variants if provided
        if (isset($variants)) {
            $productService->syncVariants($record, $variants);
        }
        
        return $record->fresh(['images', 'variants']);
    }

    /**
     * Get the saved notification title
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Product updated successfully';
    }

    /**
     * Redirect to index page after update
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
