<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\Concerns\ChecksResourceAccess;
use App\Models\Category;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class CategoryResource extends Resource
{
    use ChecksResourceAccess;

    protected static ?string $model = Category::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-rectangle-stack';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.catalog');
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.categories.title');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.categories.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.categories.singular');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('image')
                            ->label('صورة القسم')
                            ->collection('category-images')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.form.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Category::class, 'slug', ignoreRecord: true),

                        Forms\Components\Select::make('parent_id')
                            ->label(__('admin.form.parent_category'))
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('بدون فئة أب (قسم رئيسي)'),

                        Forms\Components\Textarea::make('description')
                            ->label(__('admin.form.description'))
                            ->rows(3),

                        Forms\Components\Select::make('icon')
                            ->label(__('admin.form.icon'))
                            ->options([
                                'heroicon-o-shopping-bag' => '🛍️ تسوق',
                                'heroicon-o-gift' => '🎁 هدايا',
                                'heroicon-o-heart' => '❤️ مفضلات',
                                'heroicon-o-star' => '⭐ مميز',
                                'heroicon-o-sparkles' => '✨ جديد',
                                'heroicon-o-fire' => '🔥 عروض',
                                'heroicon-o-tag' => '🏷️ تصنيف',
                                'heroicon-o-cube' => '📦 منتجات',
                                'heroicon-o-home' => '🏠 المنزل',
                                'heroicon-o-device-phone-mobile' => '📱 إلكترونيات',
                                'heroicon-o-computer-desktop' => '🖥️ كمبيوتر',
                                'heroicon-o-truck' => '🚚 توصيل',
                                'heroicon-o-beaker' => '🧪 منتجات العناية',
                                'heroicon-o-face-smile' => '😊 جمال',
                                'heroicon-o-sun' => '☀️ صيفي',
                                'heroicon-o-moon' => '🌙 ليلي',
                                'heroicon-o-puzzle-piece' => '🧩 ألعاب',
                                'heroicon-o-book-open' => '📖 كتب',
                                'heroicon-o-musical-note' => '🎵 موسيقى',
                                'heroicon-o-camera' => '📷 تصوير',
                                'heroicon-o-paint-brush' => '🎨 فن',
                                'heroicon-o-scissors' => '✂️ أدوات',
                                'heroicon-o-wrench' => '🔧 صيانة',
                                'heroicon-o-light-bulb' => '💡 إضاءة',
                                'heroicon-o-cake' => '🎂 مناسبات',
                                'heroicon-o-academic-cap' => '🎓 تعليم',
                                'heroicon-o-briefcase' => '💼 أعمال',
                                'heroicon-o-clock' => '⏰ ساعات',
                                'heroicon-o-globe-alt' => '🌍 عالمي',
                                'heroicon-o-users' => '👥 عائلة',
                            ])
                            ->searchable()
                            ->placeholder('اختر أيقونة للقسم'),

                        Forms\Components\TextInput::make('order')
                            ->label(__('admin.form.order'))
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('admin.form.is_active'))
                            ->default(true),

                        Forms\Components\Toggle::make('show_in_header')
                            ->label(__('storefront.categories.show_in_header'))
                            ->helperText(__('storefront.categories.visibility_help'))
                            ->default(false),

                        Forms\Components\Toggle::make('show_on_home')
                            ->label(__('storefront.categories.show_on_home'))
                            ->helperText(__('storefront.categories.visibility_help'))
                            ->default(false),

                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('storefront.categories.sort_order'))
                            ->integer()->minValue(0)->required()->default(0),

                        Forms\Components\TextInput::make('home_sort_order')
                            ->label(__('storefront.categories.home_sort_order'))
                            ->integer()->minValue(0)->required()->default(0),
                    ])
                    ->columns(2),

                Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('عنوان SEO')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('وصف SEO')
                            ->rows(2),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label('الصورة')
                    ->collection('category-images')
                    ->conversion('thumb')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('admin.table.parent_category'))
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('children_count')
                    ->label(__('admin.table.subcategories'))
                    ->counts('children')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('admin.table.products'))
                    ->counts('products')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('order')
                    ->label('الترتيب')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('نشط')
                    ->disabled(fn($record) => !auth()->user()->can('update', $record)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),

                Tables\Filters\Filter::make('parent')
                    ->label('فئة رئيسية فقط')
                    ->query(fn(Builder $query): Builder => $query->whereNull('parent_id')),
            ])
            ->actions([
                Actions\EditAction::make()
                    ->visible(fn($record) => auth()->user()->can('update', $record)),
                Actions\DeleteAction::make()
                    ->visible(fn($record) => auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->can('delete categories')),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
