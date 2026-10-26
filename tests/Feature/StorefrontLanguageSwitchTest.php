<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\StorefrontLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\ResponseCache\Events\ResponseCacheHit;
use Tests\TestCase;

class StorefrontLanguageSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['responsecache.enabled' => true, 'responsecache.cache_store' => 'array']);
    }

    public static function storefrontPages(): array
    {
        return [
            'classic home' => ['classic', '/'],
            'classic products' => ['classic', '/products'],
            'category first home' => ['category_first', '/'],
            'category first products' => ['category_first', '/products'],
        ];
    }

    #[DataProvider('storefrontPages')]
    public function test_language_switch_serves_and_reuses_the_correct_cached_translation(string $template, string $url): void
    {
        Setting::set(StorefrontLayout::SETTING_KEY, $template);
        Event::fake([ResponseCacheHit::class]);

        $this->withSession(['locale' => 'ar']);
        $this->assertPageLanguage($url, 'ar');
        $this->assertPageLanguage($url, 'ar');
        Event::assertDispatchedTimes(ResponseCacheHit::class, 1);

        $this->from($url)->get('/locale/en')->assertRedirect($url)->assertSessionHas('locale', 'en');
        $this->assertPageLanguage($url, 'en');
        $this->assertPageLanguage($url, 'en');
        Event::assertDispatchedTimes(ResponseCacheHit::class, 2);

        // The legacy language URL must reuse Arabic's existing cache entry.
        $this->from($url)->get('/language/ar')->assertRedirect($url)->assertSessionHas('locale', 'ar');
        $this->assertPageLanguage($url, 'ar');
        Event::assertDispatchedTimes(ResponseCacheHit::class, 3);
    }

    private function assertPageLanguage(string $url, string $locale): void
    {
        $response = $this->get($url)->assertOk();
        preg_match('/<html\s+lang="([^"]+)"\s+dir="([^"]+)"/', $response->getContent(), $matches);
        $this->assertSame($locale, $matches[1] ?? null, 'Cached page language must match the session.');
        $this->assertSame($locale === 'ar' ? 'rtl' : 'ltr', $matches[2] ?? null);
        $this->assertStringContainsString('/locale/'.($locale === 'ar' ? 'en' : 'ar'), $response->getContent());
    }
}
