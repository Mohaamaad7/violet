<?php

use App\Support\LegacyCatalogTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function fields(string $table): array
    {
        $fields = ['name', 'description', 'meta_title', 'meta_description'];

        return $table === 'products'
            ? [...$fields, 'short_description', 'long_description', 'specifications', 'how_to_use', 'meta_keywords']
            : $fields;
    }

    public function up(): void
    {
        foreach (['products', 'categories'] as $tableName) {
            $fields = $this->fields($tableName);
            Schema::table($tableName, function (Blueprint $table) use ($fields) {
                foreach ($fields as $field) {
                    foreach (['ar', 'en'] as $locale) {
                        if (in_array($field, ['name', 'meta_title'])) {
                            $table->string($field.'_'.$locale)->nullable();
                        } else {
                            $table->longText($field.'_'.$locale)->nullable();
                        }
                    }
                }
            });

            DB::table($tableName)->orderBy('id')->chunkById(200, function ($records) use ($tableName, $fields) {
                foreach ($records as $record) {
                    $updates = [];
                    foreach ($fields as $field) {
                        foreach (LegacyCatalogTranslation::extract($record->{$field} ?? null) as $locale => $text) {
                            $updates[$field.'_'.$locale] = $text;
                        }
                    }
                    if ($updates !== []) {
                        DB::table($tableName)->where('id', $record->id)->update($updates);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['products', 'categories'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columns = [];
                foreach ($this->fields($tableName) as $field) {
                    $columns[] = $field.'_ar';
                    $columns[] = $field.'_en';
                }
                $table->dropColumn($columns);
            });
        }
    }
};
