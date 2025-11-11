<?php

namespace App\Filament\Resources\TranslationResource\Pages;

use App\Filament\Resources\TranslationResource;
use App\Models\Translation;
use App\Services\TranslationService;
use Filament\Resources\Pages\ManageRecords;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ManageTranslations extends ManageRecords
{

    protected static string $resource = TranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importJson')
                ->label('Import JSON')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->acceptedFileTypes(['application/json'])
                        ->required(),
                ])
                ->action(function (array $data, TranslationService $service) {
                    $path = $data['file']?->getRealPath();
                    if (!$path) return;
                    $json = json_decode(file_get_contents($path), true);
                    if (!is_array($json)) return;
                    $locale = app()->getLocale();
                    $items = [];
                    foreach ($json as $key => $value) {
                        $items[] = ['key' => $key, 'locale' => $locale, 'value' => $value, 'group' => null, 'is_active' => true];
                    }
                    $service->bulkImport($items, override: false, updatedBy: optional(auth()->user())->id);
                    Notification::make()->title('Imported translations')->success()->send();
                }),
            Action::make('exportJson')
                ->label('Export JSON')
                ->action(function () {
                    $locale = app()->getLocale();
                    $data = Translation::query()->where('locale', $locale)->pluck('value', 'key')->toArray();
                    $filename = "translations_$locale.json";
                    return response()->streamDownload(function () use ($data) {
                        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }, $filename, ['Content-Type' => 'application/json']);
                }),
        ];
    }

    protected function afterCreate(): void
    {
        $this->notifyUpdate();
    }

    protected function afterEdit(): void
    {
        $this->notifyUpdate();
    }

    protected function afterDelete(): void
    {
        $this->notifyUpdate();
    }

    protected function notifyUpdate(): void
    {
        // Invalidate cache for affected locale(s)
        // Best-effort: clear current locale cache; precise invalidation handled in service on set()
        app(TranslationService::class)->invalidateCache(app()->getLocale());

        // Broadcast to Livewire/Alpine to refresh UI strings
        $this->dispatch('translations-updated', locale: app()->getLocale());

    Notification::make()->title('Translations updated')->success()->send();
    }
}
