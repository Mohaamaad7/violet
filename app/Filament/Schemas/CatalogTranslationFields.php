<?php

namespace App\Filament\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class CatalogTranslationFields
{
    public static function make(bool $product = false): Tabs
    {
        $tabs = [];
        foreach (['ar', 'en'] as $locale) {
            $fields = [TextInput::make('name_'.$locale)
                ->label(__('catalog.name'))
                ->extraInputAttributes(['dir' => $locale === 'ar' ? 'rtl' : 'ltr', 'lang' => $locale])
                ->maxLength(255)
                ->required(fn (Get $get) => $product ? $get('status') === 'active' : (bool) $get('is_active'))
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) use ($locale) {
                    if ($operation === 'create' && $locale === 'en' && ! filled($get('slug'))) {
                        $set('slug', Str::slug($state ?? ''));
                    }
                })];

            $contentFields = $product
                ? ['description', 'short_description', 'long_description', 'specifications', 'how_to_use', 'meta_title', 'meta_description', 'meta_keywords']
                : ['description', 'meta_title', 'meta_description'];
            foreach ($contentFields as $field) {
                $component = in_array($field, ['description', 'long_description', 'specifications', 'how_to_use']) && $product
                    ? RichEditor::make($field.'_'.$locale)->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'link'],
                        ['textColor', 'highlight', 'clearFormatting'],
                        ['h1', 'h2', 'h3'], ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'],
                        ['bulletList', 'orderedList', 'blockquote', 'table'], ['undo', 'redo'],
                    ])
                    : ($field === 'meta_title' ? TextInput::make($field.'_'.$locale)->maxLength(255) : Textarea::make($field.'_'.$locale)->rows(3));
                $fields[] = $component->label(__('catalog.'.$field))
                    ->extraInputAttributes(['dir' => $locale === 'ar' ? 'rtl' : 'ltr', 'lang' => $locale])
                    ->columnSpanFull();
            }
            $tabs[] = Tab::make(__('catalog.'.$locale))->schema($fields);
        }

        return Tabs::make('catalog_translations')->tabs($tabs)->columnSpanFull();
    }
}
