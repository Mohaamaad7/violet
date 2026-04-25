<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class ShippingDiscountSettingsSeeder extends Seeder
{
    /**
     * Seed the three settings required for Dynamic Shipping Discount.
     * Uses updateOrCreate to be safe on re-run.
     * Note: settings table only has: key, value, type, group — no display_name/description.
     */
    public function run(): void
    {
        $settings = [
            [
                'key'   => 'shipping_discount_enabled',
                'value' => '0',
                'type'  => 'boolean',
                'group' => 'shipping',
            ],
            [
                'key'   => 'shipping_discount_threshold',
                'value' => '250',
                'type'  => 'integer',
                'group' => 'shipping',
            ],
            [
                'key'   => 'shipping_discount_percentage',
                'value' => '50',
                'type'  => 'integer',
                'group' => 'shipping',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
