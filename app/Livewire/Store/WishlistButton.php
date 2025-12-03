<?php

namespace App\Livewire\Store;

use App\Services\WishlistService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WishlistButton extends Component
{
    public int $productId;
    public bool $inWishlist = false;
    public bool $showText = false;
    public string $size = 'md'; // sm, md, lg
    
    protected WishlistService $wishlistService;
    
    public function boot(WishlistService $wishlistService): void
    {
        $this->wishlistService = $wishlistService;
    }
    
    public function mount(int $productId, bool $showText = false, string $size = 'md'): void
    {
        $this->productId = $productId;
        $this->showText = $showText;
        $this->size = $size;
        $this->inWishlist = $this->wishlistService->isInWishlist($productId);
    }
    
    public function toggle(): void
    {
        if (!Auth::check()) {
            $this->dispatch('show-toast', message: __('messages.wishlist.login_required'), type: 'error');
            return;
        }
        
        $result = $this->wishlistService->toggle($this->productId);
        
        if ($result['success']) {
            $this->inWishlist = $result['in_wishlist'];
            
            // Dispatch events
            $this->dispatch('wishlist-updated');
            $this->dispatch('show-toast', 
                message: $result['action'] === 'added' 
                    ? __('messages.wishlist.added') 
                    : __('messages.wishlist.removed'),
                type: 'success'
            );
        }
    }
    
    public function render()
    {
        return view('livewire.store.wishlist-button');
    }
}
