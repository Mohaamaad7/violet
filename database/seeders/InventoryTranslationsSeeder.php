<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class InventoryTranslationsSeeder extends Seeder
{
    /**
     * Seed inventory translations from language files.
     */
    public function run(): void
    {
        $this->command->info('Seeding inventory translations...');

        // Load translation files
        $arInventory = require base_path('lang/ar/inventory.php');
        $enInventory = require base_path('lang/en/inventory.php');

        // Seed Arabic inventory translations
        foreach ($arInventory as $key => $value) {
            Translation::updateOrCreate([
                'key' => "inventory.$key",
                'locale' => 'ar',
            ], [
                'value' => $value,
                'group' => 'inventory',
                'is_active' => true,
            ]);
        }

        // Seed English inventory translations
        foreach ($enInventory as $key => $value) {
            Translation::updateOrCreate([
                'key' => "inventory.$key",
                'locale' => 'en',
            ], [
                'value' => $value,
                'group' => 'inventory',
                'is_active' => true,
            ]);
        }

        $this->command->info("✅ Imported {$this->getCount($arInventory)} Arabic inventory translations");
        $this->command->info("✅ Imported {$this->getCount($enInventory)} English inventory translations");
    }

    /**
     * Count translations recursively (in case of nested arrays)
     */
    private function getCount(array $translations): int
    {
        $count = 0;
        foreach ($translations as $value) {
            if (is_array($value)) {
                $count += $this->getCount($value);
            } else {
                $count++;
            }
        }
        return $count;
    }
}
