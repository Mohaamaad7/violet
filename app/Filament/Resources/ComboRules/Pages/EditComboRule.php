<?php

namespace App\Filament\Resources\ComboRules\Pages;

use App\Filament\Resources\ComboRules\ComboRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditComboRule extends EditRecord
{
    protected static string $resource = ComboRuleResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            ReplicateAction::make()
                ->label('نسخ هذا العرض')
                ->icon('heroicon-m-document-duplicate')
                ->color('gray')
                ->tooltip('إنشاء نسخة من هذا العرض لتعديلها')
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
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
