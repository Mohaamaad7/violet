<?php

namespace App\Livewire\Store;

use App\Models\Product;
use App\Models\ProductVariant;
use Livewire\Component;

class ProductDetails extends Component
{
    // Product instance
    public Product $product;
    
    // Selected variant
    public $selectedVariantId = null;
    public $selectedVariant = null;
    
    // Quantity
    public $quantity = 1;
    
    // Current displayed image
    public $currentImage;
    
    // Selected attributes (for variants)
    public $selectedAttributes = [];

    /**
     * Mount component with product
     */
    public function mount(Product $product)
    {
        $this->product = $product;
        
        // Get primary image from Spatie Media Library
        $primaryMedia = $product->getMedia('product-images')
            ->filter(fn($media) => $media->getCustomProperty('is_primary') === true)
            ->first();
        
        // Fallback to first media if no primary
        if (!$primaryMedia) {
            $primaryMedia = $product->getFirstMedia('product-images');
        }
        
        // Set current image (use placeholder if no media)
        $this->currentImage = $primaryMedia ? $primaryMedia->getUrl() : asset('images/default-product.png');
        
        // If product has variants, select first in-stock variant by default
        if ($product->variants->count() > 0) {
            $firstVariant = $product->variants->first();
            if ($firstVariant) {
                $this->selectVariant($firstVariant->id);
            }
        }
    }

    /**
     * Select a variant
     */
    public function selectVariant($variantId)
    {
        $variant = $this->product->variants()->find($variantId);
        
        if (!$variant) {
            return;
        }
        
        $this->selectedVariantId = $variantId;
        $this->selectedVariant = $variant;
        $this->selectedAttributes = $variant->attributes ?? [];
        
        // Update image if variant has associated image
        // (We'll assume variant attributes might include an image reference)
        // For now, keep current product image
        
        // Reset quantity if variant is out of stock
        if ($variant->stock <= 0) {
            $this->quantity = 0;
        } elseif ($this->quantity > $variant->stock) {
            $this->quantity = $variant->stock;
        }
    }

    /**
     * Change the displayed image
     */
    public function changeImage($imageUrl)
    {
        $this->currentImage = $imageUrl;
    }

    /**
     * Increment quantity
     */
    public function incrementQuantity()
    {
        $maxStock = $this->getMaxStock();
        
        if ($this->quantity < $maxStock) {
            $this->quantity++;
        }
    }

    /**
     * Decrement quantity
     */
    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    /**
     * Update quantity directly
     */
    public function updatedQuantity($value)
    {
        $maxStock = $this->getMaxStock();
        
        if ($value < 1) {
            $this->quantity = 1;
        } elseif ($value > $maxStock) {
            $this->quantity = $maxStock;
        }
    }

    /**
     * Get max available stock
     */
    protected function getMaxStock()
    {
        if ($this->selectedVariant) {
            return $this->selectedVariant->stock;
        }
        
        return $this->product->stock;
    }

    /**
     * Get current price (considering variant or product)
     */
    public function getCurrentPrice()
    {
        if ($this->selectedVariant) {
            return $this->selectedVariant->price;
        }
        
        return $this->product->final_price;
    }

    /**
     * Check if product/variant is in stock
     */
    public function isInStock()
    {
        if ($this->selectedVariant) {
            return $this->selectedVariant->stock > 0;
        }
        
        return $this->product->is_in_stock;
    }

    /**
     * Add to cart
     */
    public function addToCart()
    {
        if (!$this->isInStock()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Product is out of stock'
            ]);
            return;
        }

        // TODO: Implement actual cart logic
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Product added to cart successfully!'
        ]);
    }

    /**
     * Add to wishlist
     */
    public function addToWishlist()
    {
        // TODO: Implement actual wishlist logic
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Product added to wishlist!'
        ]);
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.store.product-details');
    }
}
