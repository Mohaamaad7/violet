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

    public static function getNavigationIcon(): string|\BackedEnum|\Illuminate\Contracts\Support\Htmlable|null
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
        return __('admin.content_pages');
    }

    public static function getModelLabel(): string
    {
        return __('admin.content_page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.content_pages');
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
                            ->live(onBlur: true)
                            ->helperText(__('admin.slug_helper')),

                        Forms\Components\RichEditor::make('content')
                            ->label(__('admin.content'))
                            ->required(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('slug') !== 'about')
                            ->hidden(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('slug') === 'about')
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

                // ──────────────────────────────────────────────
                // About Us Page — Structured Metadata Section
                // Visible ONLY when slug === 'about'
                // Data is stored in `pages.metadata` (JSON)
                // ──────────────────────────────────────────────
                Components\Section::make(__('admin.about_us_content', ['default' => 'About Us — Structured Content']))
                    ->description(__('admin.about_us_content_desc', ['default' => 'Vision, Values, and Achievements for the About Us page. This data is stored as structured JSON metadata.']))
                    ->icon('heroicon-o-building-office-2')
                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('slug') === 'about')
                    ->schema([
                        // ── Vision ──
                        Forms\Components\Textarea::make('metadata.vision')
                            ->label(__('admin.vision', ['default' => 'Vision']))
                            ->helperText(__('admin.vision_helper', ['default' => 'The company\'s vision statement displayed on the About Us page.']))
                            ->default(fn () => __('about.our_vision.content'))
                            ->rows(3)
                            ->columnSpanFull(),

                        // ── Values ──
                        Forms\Components\Repeater::make('metadata.values')
                            ->label(__('admin.values', ['default' => 'Values']))
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label(__('admin.value_title', ['default' => 'Value Title']))
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('description')
                                    ->label(__('admin.value_description', ['default' => 'Description']))
                                    ->required()
                                    ->rows(2)
                                    ->maxLength(500),

                                Forms\Components\TextInput::make('icon')
                                    ->label(__('admin.icon', ['default' => 'Icon']))
                                    ->helperText(__('admin.icon_helper', ['default' => 'Heroicon name or emoji (e.g. "✨" or "heroicon-o-heart")']))
                                    ->maxLength(100),
                            ])
                            ->columns(2)
                            ->default([
                                ['title' => __('about.our_values.quality.title'), 'description' => __('about.our_values.quality.description'), 'icon' => 'heroicon-o-check-badge'],
                                ['title' => __('about.our_values.transparency.title'), 'description' => __('about.our_values.transparency.description'), 'icon' => 'heroicon-o-eye'],
                                ['title' => __('about.our_values.innovation.title'), 'description' => __('about.our_values.innovation.description'), 'icon' => 'heroicon-o-light-bulb'],
                                ['title' => __('about.our_values.customer_satisfaction.title'), 'description' => __('about.our_values.customer_satisfaction.description'), 'icon' => 'heroicon-o-heart'],
                            ])
                            ->addActionLabel(__('admin.add_value', ['default' => 'Add Value']))
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->columnSpanFull(),

                        // ── Achievements ──
                        Forms\Components\Repeater::make('metadata.achievements')
                            ->label(__('admin.achievements', ['default' => 'Achievements']))
                            ->schema([
                                Forms\Components\TextInput::make('number')
                                    ->label(__('admin.achievement_number', ['default' => 'Number / Stat']))
                                    ->required()
                                    ->placeholder('e.g. 1000+')
                                    ->maxLength(50),

                                Forms\Components\TextInput::make('label')
                                    ->label(__('admin.achievement_label', ['default' => 'Label']))
                                    ->required()
                                    ->placeholder('e.g. Happy Customers')
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->default([
                                ['number' => '1000+', 'label' => __('about.our_achievements.happy_customers')],
                                ['number' => '500+', 'label' => __('about.our_achievements.diverse_products')],
                                ['number' => '5+', 'label' => __('about.our_achievements.years_experience')],
                                ['number' => '100%', 'label' => __('about.our_achievements.quality_guarantee')],
                            ])
                            ->addActionLabel(__('admin.add_achievement', ['default' => 'Add Achievement']))
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => isset($state['number'], $state['label']) ? "{$state['number']} — {$state['label']}" : null)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // ──────────────────────────────────────────────
                // Contact Us Page — Structured Metadata Section
                // Visible ONLY when slug === 'contact'
                // ──────────────────────────────────────────────
                Components\Section::make('محتوى صفحة اتصل بنا')
                    ->description('النصوص المخصصة لصفحة اتصل بنا.')
                    ->icon('heroicon-o-phone')
                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('slug') === 'contact')
                    ->schema([
                        Forms\Components\TextInput::make('metadata.hero_title')
                            ->label('عنوان رئيسي لصفحة اتصل بنا')
                            ->helperText('مثال: تواصل معنا')
                            ->default(fn () => __('contact.hero.title'))
                            ->maxLength(255),

                        Forms\Components\Textarea::make('metadata.hero_subtitle')
                            ->label('نص ترحيبي')
                            ->helperText('مثال: نحن هنا للإجابة على استفساراتك')
                            ->default(fn () => __('contact.hero.subtitle'))
                            ->rows(2)
                            ->maxLength(500),

                        Forms\Components\TextInput::make('metadata.contact_info_title')
                            ->label('عنوان معلومات الاتصال')
                            ->helperText('مثال: معلومات الاتصال')
                            ->default(fn () => __('contact.contact_info.title'))
                            ->maxLength(255),

                        Forms\Components\Textarea::make('metadata.working_hours_text')
                            ->label('ساعات العمل النصية')
                            ->helperText('النص الذي يظهر في قسم ساعات العمل. يمكنك استخدام أسطر متعددة.')
                            ->default(fn () => __('contact.contact_info.hours.weekdays') . "\n" . __('contact.contact_info.hours.friday'))
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->collapsible(),

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
