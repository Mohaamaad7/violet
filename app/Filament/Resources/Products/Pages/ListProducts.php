<?php

namespace App\Filament\Resources\Products\Pages;

use App\Exports\ProductTemplateExport;
use App\Filament\Resources\Products\ProductResource;
use App\Imports\ProductImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Import from Excel Action
            Action::make('import')
                ->label(__('admin.import.title'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label(__('admin.import.upload_file'))
                        ->helperText(__('admin.import.upload_help'))
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            '.xlsx',
                            '.xls',
                        ])
                        ->required()
                        ->disk('local')
                        ->directory('imports')
                        ->visibility('private'),
                ])
                ->action(function (array $data) {
                    if (empty($data['file'])) {
                        Notification::make()
                            ->title(__('admin.import.file_required'))
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        $import = new ProductImport();
                        $filePath = storage_path('app/' . $data['file']);

                        Excel::import($import, $filePath);

                        $updatedCount = $import->getUpdatedCount();
                        $errors = $import->getErrors();

                        // Clean up the uploaded file
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }

                        if ($updatedCount > 0) {
                            Notification::make()
                                ->title(__('admin.import.success'))
                                ->body(__('admin.import.success_count', ['count' => $updatedCount]))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('admin.import.no_updates'))
                                ->warning()
                                ->send();
                        }

                        if (!empty($errors)) {
                            Notification::make()
                                ->title(__('admin.import.validation_errors'))
                                ->body(implode("\n", array_slice($errors, 0, 5)))
                                ->warning()
                                ->persistent()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title(__('admin.import.error'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // Download Empty Template Action
            Action::make('downloadTemplate')
                ->label(__('admin.import.download_empty_template'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    return Excel::download(
                        new ProductTemplateExport(null),
                        'products-template-' . now()->format('Y-m-d') . '.xlsx'
                    );
                }),

            CreateAction::make(),
        ];
    }
}
