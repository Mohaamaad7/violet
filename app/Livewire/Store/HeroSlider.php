<?php

namespace App\Livewire\Store;

use App\Models\Slider;
use Livewire\Component;

class HeroSlider extends Component
{
    public function render()
    {
        $sliders = Slider::active()->get();
        
        return view('livewire.store.hero-slider', [
            'sliders' => $sliders,
        ]);
    }
}
