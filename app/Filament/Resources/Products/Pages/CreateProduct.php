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
        // Filament will handle image uploads automatically
        // Just keep the temporary paths for now
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
        
        // Extract images (Filament already stored them in storage/app/public/products/)
        // The paths here are relative: "products/filename.jpg"
        $imagesPaths = $data['images'] ?? [];
        unset($data['images']);
        
        // Prepare images array for ProductService
        $imagesData = [];
        if (!empty($imagesPaths)) {
            foreach ($imagesPaths as $index => $imagePath) {
                $imagesData[] = [
                    'image_path' => $imagePath, // Already stored by Filament
                    'is_primary' => $index === 0,
                    'order' => $index,
                ];
            }
            $data['images'] = $imagesData;
        }
        
        // Create product with images using service
        $product = $productService->createWithImages($data);
        
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
