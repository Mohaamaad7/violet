<?php

namespace App\Filament\Resources\HelpEntries\Schemas;

use App\Models\HelpEntry;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Utilities\Set;

class HelpEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('admin.help_entries.form.content_section'))
                    ->schema([
                        TextInput::make('question')
                            ->label(__('admin.help_entries.form.question'))
                            ->required()
                            ->maxLength(500)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->columnSpanFull(),

                        RichEditor::make('answer')
                            ->label(__('admin.help_entries.form.answer'))
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'orderedList',
                                'bulletList',
                                'h2',
                                'h3',
                                'blockquote',
                                'redo',
                                'undo',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make(__('admin.help_entries.form.settings_section'))
                    ->schema([
                        Select::make('category')
                            ->label(__('admin.help_entries.form.category'))
                            ->options(HelpEntry::CATEGORIES)
                            ->required()
                            ->searchable(),

                        TextInput::make('slug')
                            ->label(__('admin.help_entries.form.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(HelpEntry::class, 'slug', ignoreRecord: true)
                            ->helperText(__('admin.help_entries.form.slug_help')),

                        TextInput::make('sort_order')
                            ->label(__('admin.help_entries.form.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Toggle::make('is_active')
                            ->label(__('admin.help_entries.form.is_active'))
                            ->default(true)
                            ->helperText(__('admin.help_entries.form.is_active_help')),
                    ])
                    ->columns(2),
            ]);
    }
}
