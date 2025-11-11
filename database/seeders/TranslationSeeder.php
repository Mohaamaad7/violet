<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        $arMessages = require base_path('lang/ar/messages.php');
        $enMessages = require base_path('lang/en/messages.php');

        foreach ($arMessages as $key => $value) {
            Translation::updateOrCreate([
                'key' => "messages.$key",
                'locale' => 'ar',
            ], [
                'value' => $value,
                'group' => 'messages',
                'is_active' => true,
            ]);
        }

        foreach ($enMessages as $key => $value) {
            Translation::updateOrCreate([
                'key' => "messages.$key",
                'locale' => 'en',
            ], [
                'value' => $value,
                'group' => 'messages',
                'is_active' => true,
            ]);
        }

        $this->command->info('Imported ' . count($arMessages) . ' Arabic translations');
        $this->command->info('Imported ' . count($enMessages) . ' English translations');
    }
}
