<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\ProductService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
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
        // Load existing images for the form
        $product = $this->record;
        $images = $product->images()->orderBy('order')->get();
        
        if ($images->isNotEmpty()) {
            $data['images'] = $images->pluck('image_path')->toArray();
        }
        
        return $data;
    }

    /**
     * Mutate form data before saving
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Filament will handle image uploads automatically
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
        
        // Extract and process image uploads
        $images = [];
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $index => $imagePath) {
                // Filament stores the file and returns the final path
                $images[] = [
                    'image_path' => $imagePath,
                    'is_primary' => $index === 0,
                    'order' => $index,
                ];
            }
            $data['images'] = $images;
        }
        
        // Update product with images using service
        $product = $productService->updateWithImages($record, $data);
        
        // Sync variants if provided
        if (isset($variants)) {
            $productService->syncVariants($product, $variants);
        }
        
        return $product->fresh(['images', 'variants']);
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
