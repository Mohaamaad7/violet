<?php

namespace App\Filament\Resources\ComboRules\Pages;

use App\Filament\Resources\ComboRules\ComboRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

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
                ->excludeAttributes(['slug'])
                ->afterReplicating(function ($original, $replica) {
                    // Generate a unique slug
                    $baseSlug = $original->slug . '-copy';
                    $slug     = $baseSlug;
                    $counter  = 2;
                    while (\App\Models\ComboRule::withTrashed()->where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $counter++;
                    }
                    $replica->slug      = $slug;
                    $replica->name      = $original->name . ' (نسخة)';
                    $replica->is_active = false; // Start inactive so admin can review
                    $replica->save();

                    // Deep-clone conditions
                    foreach ($original->conditions as $condition) {
                        $replica->conditions()->create([
                            'condition_type'    => $condition->condition_type,
                            'category_id'       => $condition->category_id,
                            'product_id'        => $condition->product_id,
                            'required_quantity' => $condition->required_quantity,
                        ]);
                    }
                })
                ->successRedirectUrl(fn ($replica) => ComboRuleResource::getUrl('edit', ['record' => $replica]))
                ->successNotificationTitle('تم نسخ العرض بنجاح — جاري فتح النسخة الجديدة للتعديل'),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
