<?php

namespace App\Livewire\Store;

use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WishlistPage extends Component
{
    protected WishlistService $wishlistService;
    protected CartService $cartService;
    
    protected $listeners = ['wishlist-updated' => '$refresh'];
    
    public function boot(WishlistService $wishlistService, CartService $cartService): void
    {
        $this->wishlistService = $wishlistService;
        $this->cartService = $cartService;
    }
    
    public function removeFromWishlist(int $productId): void
    {
        $this->wishlistService->remove($productId);
        
        $this->dispatch('wishlist-updated');
        $this->dispatch('show-toast', message: __('messages.wishlist.removed'), type: 'success');
    }
    
    public function moveToCart(int $productId): void
    {
        try {
            // Add to cart
            $this->cartService->addItem($productId, 1);
            
            // Remove from wishlist
            $this->wishlistService->remove($productId);
            
            $this->dispatch('wishlist-updated');
            $this->dispatch('cart-updated');
            $this->dispatch('show-toast', message: __('messages.wishlist.moved_to_cart'), type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', message: $e->getMessage(), type: 'error');
        }
    }
    
    public function clearWishlist(): void
    {
        $this->wishlistService->clear();
        
        $this->dispatch('wishlist-updated');
        $this->dispatch('show-toast', message: __('messages.wishlist.cleared'), type: 'success');
    }
    
    public function render()
    {
        $items = $this->wishlistService->getWishlistItems();
        
        return view('livewire.store.wishlist-page', [
            'items' => $items,
        ])->layout('layouts.store', ['title' => __('messages.wishlist.title')]);
    }
}
