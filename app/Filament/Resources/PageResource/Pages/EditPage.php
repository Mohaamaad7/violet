<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('view_page')
                ->label(__('admin.view_page'))
                ->icon('heroicon-o-eye')
                ->url(fn(): string => route('page.show', $this->record->slug))
                ->openUrlInNewTab(),
        ];
    }
}
