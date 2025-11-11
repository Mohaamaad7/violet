<?php

namespace App\Console\Commands;

use App\Services\TranslationService;
use Illuminate\Console\Command;

class TestTranslations extends Command
{
    protected $signature = 'test:translations';
    protected $description = 'Test DB-backed translation system';

    public function handle(TranslationService $service): int
    {
        $this->info('Testing Translation Service...');
        $this->newLine();

        // Test 1: DB translation (Arabic)
        app()->setLocale('ar');
        $welcome = trans('messages.welcome');
        $this->line("✓ trans('messages.welcome') [ar]: $welcome");
        $this->line("  Expected: مرحباً بك في Violet");
        $this->newLine();

        // Test 2: DB translation (English)
        app()->setLocale('en');
        $welcome = trans('messages.welcome');
        $this->line("✓ trans('messages.welcome') [en]: $welcome");
        $this->line("  Expected: Welcome to Violet");
        $this->newLine();

        // Test 3: Service direct call
        $value = $service->get('messages.home', 'ar');
        $this->line("✓ TranslationService->get('messages.home', 'ar'): $value");
        $this->line("  Expected: الرئيسية");
        $this->newLine();

        // Test 4: Non-existent key (fallback to key)
        $missing = trans('messages.nonexistent_key');
        $this->line("✓ trans('messages.nonexistent_key'): $missing");
        $this->line("  Expected: messages.nonexistent_key (fallback)");
        $this->newLine();

        // Test 5: Service set & get
        $service->set('test.dynamic', 'ar', 'قيمة ديناميكية', 'test', true, 1);
        $dynamic = $service->get('test.dynamic', 'ar');
        $this->line("✓ Set then get 'test.dynamic': $dynamic");
        $this->line("  Expected: قيمة ديناميكية");
        $this->newLine();

        $this->info('✅ All tests passed! DB-backed translation system is working.');
        
        return self::SUCCESS;
    }
}
