<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.content');
    }

    public static function getNavigationSort(): ?int
    {
        return 50;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.pages');
    }

    public static function getModelLabel(): string
    {
        return __('admin.page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.pages');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make(__('admin.page_content'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.title'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label(__('admin.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('admin.slug_helper')),

                        Forms\Components\RichEditor::make('content')
                            ->label(__('admin.content'))
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'h2',
                                'h3',
                                'bulletList',
                                'orderedList',
                                'link',
                                'undo',
                                'redo',
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('admin.is_active'))
                            ->default(true),
                    ])
                    ->columns(2),

                Components\Section::make(__('admin.seo_settings'))
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label(__('admin.meta_title'))
                            ->maxLength(60)
                            ->helperText(__('admin.meta_title_helper')),

                        Forms\Components\Textarea::make('meta_description')
                            ->label(__('admin.meta_description'))
                            ->maxLength(160)
                            ->rows(2)
                            ->helperText(__('admin.meta_description_helper')),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('admin.slug'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('admin.copied')),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.is_active')),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('view_page')
                    ->label(__('admin.view_page'))
                    ->icon('heroicon-o-eye')
                    ->url(fn(Page $record): string => route('page.show', $record->slug))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
