<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\ProductImageUploader;
use App\Services\ProductService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * Mutate form data before creating the record
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Filament Repeater with relationship() will handle images automatically
        return $data;
    }

    /**
     * Handle record creation using ProductService
     */
    protected function handleRecordCreation(array $data): Model
    {
        $productService = app(ProductService::class);
        
        // Extract variants data
        $variants = $data['variants'] ?? [];
        unset($data['variants']);
        
        // Extract images - Filament Repeater handles this via relationship
        // But we need to ensure at least one is primary
        $images = $data['images'] ?? [];
        
        // Ensure at least one image is marked as primary
        if (!empty($images)) {
            $hasPrimary = false;
            foreach ($images as $index => &$image) {
                if ($image['is_primary'] ?? false) {
                    $hasPrimary = true;
                    break;
                }
            }
            
            // If no primary image is set, make the first one primary
            if (!$hasPrimary) {
                $images[0]['is_primary'] = true;
            }
            
            $data['images'] = $images;
        }
        
        // Create product - Filament will handle the images relationship
        $product = parent::handleRecordCreation($data);
        
        // Sync variants if provided
        if (!empty($variants)) {
            $productService->syncVariants($product, $variants);
        }
        
        return $product->fresh(['images', 'variants']);
    }

    /**
     * Get the created notification title
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Product created successfully';
    }

    /**
     * Redirect after creation
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
