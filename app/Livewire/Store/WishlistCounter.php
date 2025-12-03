<?php

namespace App\Livewire\Store;

use App\Services\WishlistService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WishlistCounter extends Component
{
    public int $count = 0;
    
    protected $listeners = ['wishlist-updated' => 'updateCount'];
    
    protected WishlistService $wishlistService;
    
    public function boot(WishlistService $wishlistService): void
    {
        $this->wishlistService = $wishlistService;
    }
    
    public function mount(): void
    {
        $this->updateCount();
    }
    
    public function updateCount(): void
    {
        $this->count = $this->wishlistService->getWishlistCount();
    }
    
    public function render()
    {
        return view('livewire.store.wishlist-counter');
    }
}
