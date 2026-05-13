<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Slider;
use Spatie\ResponseCache\Facades\ResponseCache;

class HeroSliderTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_slider_is_not_displayed()
    {
        ResponseCache::clear();
        
        $activeSlider = Slider::create([
            'title' => 'Active Slider Title Test',
            'image_path' => 'sliders/active.jpg',
            'is_active' => true,
            'order' => 1,
        ]);
        
        $inactiveSlider = Slider::create([
            'title' => 'Inactive Slider Title Test',
            'image_path' => 'sliders/inactive.jpg',
            'is_active' => false,
            'order' => 2,
        ]);

        $response = $this->get('/');

        $response->assertSee($activeSlider->title);
        $response->assertDontSee($inactiveSlider->title);
    }

    public function test_updating_slider_clears_response_cache()
    {
        ResponseCache::clear();

        $slider = Slider::create([
            'title' => 'First Title Cache Test',
            'image_path' => 'sliders/test.jpg',
            'is_active' => true,
            'order' => 1,
        ]);
        
        // Initial request should be cached
        $this->get('/')->assertSee('First Title Cache Test');

        // Update slider
        $slider->update(['title' => 'Updated Title Cache Test']);

        // Next request should see the update because cache was cleared
        $this->get('/')->assertSee('Updated Title Cache Test');
    }
}
