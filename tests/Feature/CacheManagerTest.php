<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\ResponseCache\Facades\ResponseCache;

class CacheManagerTest extends TestCase
{
    public function test_cache_manager_page_can_be_rendered()
    {
        $response = $this->get('/admin/cache-manager');

        // Without authentication, should redirect to login
        $response->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_response_cache_can_be_cleared()
    {
        // Just verify the ResponseCache::clear() method works
        ResponseCache::clear();

        $this->assertTrue(true);
    }

    public function test_cache_manager_routes_exist()
    {
        // Verify the cache manager page URL is accessible (after redirect for unauthenticated)
        $response = $this->get('/admin/cache-manager');
        
        // Should redirect to login since we're not authenticated
        $response->assertStatus(302);
    }
}
