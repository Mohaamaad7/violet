<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class ReturnPolicySettingsSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'return_window_days',
                'value' => '14',
                'type' => 'integer',
                'group' => 'returns',
                'description' => 'Number of days allowed for returns after delivery',
            ],
            [
                'key' => 'auto_approve_rejections',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'returns',
                'description' => 'Automatically approve rejection returns',
            ],
            [
                'key' => 'refund_shipping_cost',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'returns',
                'description' => 'Refund shipping costs on returns',
            ],
            [
                'key' => 'max_return_items_percentage',
                'value' => '100',
                'type' => 'integer',
                'group' => 'returns',
                'description' => 'Maximum percentage of items that can be returned',
            ],
            [
                'key' => 'require_return_photos',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'returns',
                'description' => 'Require photos for return requests',
            ],
            [
                'key' => 'allow_partial_returns',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'returns',
                'description' => 'Allow customers to return some items from an order',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => $setting['group'],
                ]
            );
        }

        $this->command->info('✅ Return policy settings seeded successfully!');
        $this->command->info('📊 Total settings: ' . count($settings));
        $this->command->newLine();
        $this->command->info('Settings:');
        foreach ($settings as $setting) {
            $this->command->info("   ✓ {$setting['key']}: {$setting['value']} ({$setting['description']})");
        }
    }
}
