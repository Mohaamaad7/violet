<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\ProductImage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ProductImageGallery extends Component
{
    use WithFileUploads;

    public Product $product;
    public $images = [];
    public $newImages = [];
    public $editingImageId = null;

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->loadImages();
    }

    public function loadImages()
    {
        $this->images = $this->product->images()
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    public function updatedNewImages()
    {
        $this->validate([
            'newImages.*' => 'image|max:5120',
        ]);

        foreach ($this->newImages as $image) {
            $path = $image->store('products', 'public');
            
            $order = ProductImage::where('product_id', $this->product->id)->max('order') ?? -1;
            
            ProductImage::create([
                'product_id' => $this->product->id,
                'image_path' => $path,
                'is_primary' => $this->images === [], // First image is primary
                'order' => $order + 1,
            ]);
        }

        $this->newImages = [];
        $this->loadImages();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Images uploaded successfully'
        ]);
    }

    public function setPrimary($imageId)
    {
        // Unset all primaries
        ProductImage::where('product_id', $this->product->id)
            ->update(['is_primary' => false]);

        // Set new primary
        ProductImage::where('id', $imageId)
            ->update(['is_primary' => true]);

        $this->loadImages();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Primary image updated'
        ]);
    }

    public function deleteImage($imageId)
    {
        $image = ProductImage::find($imageId);
        
        if ($image) {
            // Delete file from storage
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            $wasPrimary = $image->is_primary;
            $image->delete();

            // If deleted image was primary, set first image as primary
            if ($wasPrimary) {
                $firstImage = ProductImage::where('product_id', $this->product->id)
                    ->orderBy('order')
                    ->first();
                    
                if ($firstImage) {
                    $firstImage->update(['is_primary' => true]);
                }
            }

            $this->loadImages();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Image deleted'
            ]);
        }
    }

    public function updateOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            ProductImage::where('id', $id)
                ->update(['order' => $index]);
        }

        $this->loadImages();
    }

    public function openEdit($imageId)
    {
        $this->editingImageId = $imageId;
    }

    public function closeEdit()
    {
        $this->editingImageId = null;
    }

    public function render()
    {
        return view('livewire.admin.product-image-gallery');
    }
}
