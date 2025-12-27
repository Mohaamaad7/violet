<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * إضافة إعدادات Paymob وإعدادات البوابة النشطة
     */
    public function up(): void
    {
        $settings = [
            // Active Gateway Selection
            [
                'key' => 'active_gateway',
                'value' => 'kashier',
                'group' => 'general',
            ],

            // Paymob Settings
            [
                'key' => 'paymob_secret_key',
                'value' => '',
                'group' => 'paymob',
            ],
            [
                'key' => 'paymob_public_key',
                'value' => '',
                'group' => 'paymob',
            ],
            [
                'key' => 'paymob_hmac_secret',
                'value' => '',
                'group' => 'paymob',
            ],
            [
                'key' => 'paymob_integration_id_card',
                'value' => '',
                'group' => 'paymob',
            ],
            [
                'key' => 'paymob_integration_id_wallet',
                'value' => '',
                'group' => 'paymob',
            ],
            [
                'key' => 'paymob_integration_id_kiosk',
                'value' => '',
                'group' => 'paymob',
            ],

            // Additional Payment Methods
            [
                'key' => 'payment_kiosk_enabled',
                'value' => '0',
                'group' => 'methods',
            ],
            [
                'key' => 'payment_instapay_enabled',
                'value' => '0',
                'group' => 'methods',
            ],
        ];

        foreach ($settings as $setting) {
            // Only insert if not exists
            $exists = DB::table('payment_settings')
                ->where('key', $setting['key'])
                ->exists();

            if (!$exists) {
                DB::table('payment_settings')->insert([
                    'key' => $setting['key'],
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keysToDelete = [
            'active_gateway',
            'paymob_secret_key',
            'paymob_public_key',
            'paymob_hmac_secret',
            'paymob_integration_id_card',
            'paymob_integration_id_wallet',
            'paymob_integration_id_kiosk',
            'payment_kiosk_enabled',
            'payment_instapay_enabled',
        ];

        DB::table('payment_settings')
            ->whereIn('key', $keysToDelete)
            ->delete();
    }
};
