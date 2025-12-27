<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class PaymentSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Keys that should be encrypted
     */
    protected static array $encryptedKeys = [
        // Kashier Keys
        'kashier_test_secret_key',
        'kashier_test_api_key',
        'kashier_live_secret_key',
        'kashier_live_api_key',
        // Paymob Keys
        'paymob_secret_key',
        'paymob_hmac_secret',
    ];

    // ==================== Get Setting ====================

    /**
     * Get a setting value with caching
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("payment_settings.{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            // Decrypt if needed
            if (in_array($key, self::$encryptedKeys) && $setting->value) {
                try {
                    return Crypt::decryptString($setting->value);
                } catch (\Exception $e) {
                    return $setting->value; // Return as-is if decryption fails
                }
            }

            return $setting->value;
        });
    }

    // ==================== Set Setting ====================

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $group = 'general'): void
    {
        // Encrypt if needed
        $valueToStore = $value;
        if (in_array($key, self::$encryptedKeys) && $value) {
            $valueToStore = Crypt::encryptString($value);
        }

        self::updateOrCreate(
            ['key' => $key],
            ['value' => $valueToStore, 'group' => $group]
        );

        // Clear cache
        Cache::forget("payment_settings.{$key}");
    }

    // ==================== Bulk Operations ====================

    /**
     * Get all settings for a group
     */
    public static function getGroup(string $group): array
    {
        return Cache::remember("payment_settings.group.{$group}", 3600, function () use ($group) {
            $settings = self::where('group', $group)->pluck('value', 'key')->toArray();

            // Decrypt encrypted values
            foreach ($settings as $key => $value) {
                if (in_array($key, self::$encryptedKeys) && $value) {
                    try {
                        $settings[$key] = Crypt::decryptString($value);
                    } catch (\Exception $e) {
                        // Keep as-is
                    }
                }
            }

            return $settings;
        });
    }

    /**
     * Set multiple settings at once
     */
    public static function setMany(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $value) {
            self::set($key, $value, $group);
        }

        // Clear group cache
        Cache::forget("payment_settings.group.{$group}");
    }

    // ==================== Payment Methods ====================

    /**
     * Check if a payment method is enabled
     */
    public static function isMethodEnabled(string $method): bool
    {
        return (bool) self::get("payment_{$method}_enabled", false);
    }

    /**
     * Get all enabled payment methods
     */
    public static function getEnabledMethods(): array
    {
        $methods = ['card', 'vodafone_cash', 'orange_money', 'etisalat_cash', 'meeza', 'valu', 'kiosk', 'instapay'];
        $enabled = [];

        foreach ($methods as $method) {
            if (self::isMethodEnabled($method)) {
                $enabled[] = $method;
            }
        }

        return $enabled;
    }

    // ==================== Kashier Config ====================

    /**
     * Get Kashier configuration based on mode
     */
    public static function getKashierConfig(): array
    {
        $mode = self::get('kashier_mode', 'test');

        return [
            'mode' => $mode,
            'merchant_id' => self::get("kashier_{$mode}_mid") ?? env('KASHIER_TEST_MID'),
            'secret_key' => self::get("kashier_{$mode}_secret_key") ?? env('KASHIER_TEST_SECRET_KEY'),
            'api_key' => self::get("kashier_{$mode}_api_key") ?? env('KASHIER_TEST_API_KEY'),
        ];
    }

    // ==================== Gateway Selection ====================

    /**
     * Get the active payment gateway name
     */
    public static function getActiveGateway(): string
    {
        return self::get('active_gateway', 'kashier');
    }

    /**
     * Set the active payment gateway
     */
    public static function setActiveGateway(string $gateway): void
    {
        self::set('active_gateway', $gateway, 'general');
    }

    // ==================== Paymob Config ====================

    /**
     * Get Paymob configuration
     */
    public static function getPaymobConfig(): array
    {
        return [
            'secret_key' => self::get('paymob_secret_key'),
            'public_key' => self::get('paymob_public_key'),
            'hmac_secret' => self::get('paymob_hmac_secret'),
            'integration_id_card' => self::get('paymob_integration_id_card'),
            'integration_id_wallet' => self::get('paymob_integration_id_wallet'),
            'integration_id_kiosk' => self::get('paymob_integration_id_kiosk'),
        ];
    }
}
