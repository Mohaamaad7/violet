<?php

namespace App\Filament\Resources\ComboRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\ReplicateAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Model;

class ComboRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم العرض')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount_type')
                    ->label('الخصم')
                    ->formatStateUsing(function ($record) {
                        if ($record->discount_type === 'percentage') {
                            return $record->discount_percentage . '%';
                        }
                        return $record->fixed_price . ' ج.م (ثابت)';
                    })
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('مفعل')
                    ->boolean()
                    ->sortable(),
                \Filament\Tables\Columns\ToggleColumn::make('show_on_homepage')
                    ->label('الرئيسية')
                    ->sortable(),
                TextColumn::make('priority')
                    ->label('الأولوية')
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('يبدأ في')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('ينتهي في')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('viewStorefront')
                    ->label('عرض في المتجر')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('info')
                    ->tooltip('عرض صفحة العرض على المتجر في تبويب جديد')
                    ->url(fn ($record) => $record->slug ? route('combo.show', ['slug' => $record->slug]) : '#')
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record?->slug)),
                ReplicateAction::make()
                    ->label('نسخ')
                    ->icon('heroicon-m-document-duplicate')
                    ->color('gray')
                    ->tooltip('نسخ هذا العرض وتعديله')
                    ->excludeAttributes(['slug', 'name', 'is_active'])
                    ->beforeReplicaSaved(function (Model $replica, Model $record) {
                        // Set unique slug, adjusted name, and inactive status before saving
                        $baseSlug = $record->slug . '-copy';
                        $slug     = $baseSlug;
                        $counter  = 2;
                        while (\App\Models\ComboRule::withTrashed()->where('slug', $slug)->exists()) {
                            $slug = $baseSlug . '-' . $counter++;
                        }
                        $replica->slug      = $slug;
                        $replica->name      = $record->name . ' (نسخة)';
                        $replica->is_active = false;
                    })
                    ->after(function (Model $replica, Model $record) {
                        // Deep-clone conditions after replica is saved (needs its ID)
                        foreach ($record->conditions as $condition) {
                            $replica->conditions()->create([
                                'condition_type'    => $condition->condition_type,
                                'category_id'       => $condition->category_id,
                                'product_id'        => $condition->product_id,
                                'required_quantity' => $condition->required_quantity,
                            ]);
                        }
                    })
                    ->successNotificationTitle('تم نسخ العرض — افتحه وعدّل الكمية والسعر'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
