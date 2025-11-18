<?php

namespace App\Livewire\Store;

use App\Models\Banner;
use Livewire\Component;

class BannersSection extends Component
{
    public $position = 'homepage_middle';
    
    public function render()
    {
        $banners = Banner::active()
            ->position($this->position)
            ->get();
        
        return view('livewire.store.banners-section', [
            'banners' => $banners,
        ]);
    }
}
