<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Services\EmailTemplateService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('معاينة القالب')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->modalHeading('معاينة القالب')
                ->modalDescription('عرض القالب مع بيانات تجريبية')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('إغلاق')
                ->modalContent(function () {
                    $template = $this->record;
                    $service = app(EmailTemplateService::class);
                    
                    try {
                        $html = $service->preview($template, 'ar');
                        
                        return view('filament.email-preview', [
                            'html' => $html,
                            'template' => $template,
                        ]);
                    } catch (\Exception $e) {
                        return view('filament.email-preview-error', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }),
            
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
