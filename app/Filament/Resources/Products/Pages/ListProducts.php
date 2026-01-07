<?php

namespace App\Filament\Resources\Products\Pages;

use App\Exports\ProductTemplateExport;
use App\Filament\Resources\Products\ProductResource;
use App\Imports\ProductImport;
use App\Imports\ProductImportValidator;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Import/Export Action Group
            ActionGroup::make([
                // Download Template for New Products
                Action::make('downloadCreateTemplate')
                    ->label(__('admin.import.download_create_template'))
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->action(function () {
                        return Excel::download(
                            new ProductTemplateExport('create'),
                            'products-new-template-' . now()->format('Y-m-d') . '.xlsx'
                        );
                    }),

                // Download Template for Update (All Products)
                Action::make('downloadUpdateTemplate')
                    ->label(__('admin.import.download_update_template'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function () {
                        return Excel::download(
                            new ProductTemplateExport('update'),
                            'products-update-all-' . now()->format('Y-m-d') . '.xlsx'
                        );
                    }),

                // Import New Products
                Action::make('importCreate')
                    ->label(__('admin.import.import_new'))
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Placeholder::make('info')
                            ->label('')
                            ->content(new HtmlString('
                                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 mb-4">
                                    <p class="text-green-700 dark:text-green-300">🆕 سيتم إنشاء منتجات جديدة من الملف المرفوع</p>
                                </div>
                            ')),
                        FileUpload::make('file')
                            ->label(__('admin.import.upload_file'))
                            ->helperText(__('admin.import.upload_create_help'))
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->required()
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private'),
                    ])
                    ->action(function (array $data) {
                        $this->executeImport('create', $data['file']);
                    }),

                // Import Updates
                Action::make('importUpdate')
                    ->label(__('admin.import.import_update'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->form([
                        Placeholder::make('info')
                            ->label('')
                            ->content(new HtmlString('
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 mb-4">
                                    <p class="text-blue-700 dark:text-blue-300">🔄 سيتم تحديث المنتجات الموجودة بناءً على المعرف (ID)</p>
                                    <p class="text-amber-600 dark:text-amber-400 text-sm mt-2">⚠️ لن يتم تعديل المخزون - استخدم حركات المخزون</p>
                                </div>
                            ')),
                        FileUpload::make('file')
                            ->label(__('admin.import.upload_file'))
                            ->helperText(__('admin.import.upload_update_help'))
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->required()
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private'),
                    ])
                    ->action(function (array $data) {
                        $this->executeImport('update', $data['file']);
                    }),
            ])
                ->label(__('admin.import.wizard_title'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->button(),

            CreateAction::make(),
        ];
    }

    /**
     * Execute import with validation
     */
    protected function executeImport(string $mode, string $file): void
    {
        $filePath = storage_path('app/' . $file);

        if (!file_exists($filePath)) {
            Notification::make()
                ->title(__('admin.import.file_not_found'))
                ->danger()
                ->send();
            return;
        }

        try {
            // Step 1: Validate
            $validator = new ProductImportValidator($mode);
            $result = $validator->validate($filePath);

            if (!$result['valid']) {
                // Show validation errors
                $errorMessages = [];
                foreach (array_slice($result['errors'], 0, 5) as $error) {
                    $errorMessages[] = __('admin.import.row') . ' ' . $error['row'] . ': ' . $error['field'] . ' - ' . $error['message'];
                }

                $body = implode("\n", $errorMessages);
                if (count($result['errors']) > 5) {
                    $body .= "\n... " . __('admin.import.more_errors', ['count' => count($result['errors']) - 5]);
                }

                Notification::make()
                    ->title(__('admin.import.validation_failed', ['count' => count($result['errors'])]))
                    ->body($body)
                    ->danger()
                    ->persistent()
                    ->send();

                // Clean up
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                return;
            }

            // Step 2: Execute import
            $importer = new ProductImport($mode, $result['validRows']);
            $importResult = $importer->execute();

            // Clean up
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            if ($importResult['success']) {
                $message = $mode === 'create'
                    ? __('admin.import.created_success', ['count' => $importResult['processed']])
                    : __('admin.import.updated_success', ['count' => $importResult['processed']]);

                Notification::make()
                    ->title(__('admin.import.success'))
                    ->body($message)
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title(__('admin.import.partial_success'))
                    ->body(__('admin.import.some_errors', [
                        'processed' => $importResult['processed'],
                        'errors' => $importResult['errors']
                    ]))
                    ->warning()
                    ->send();
            }

        } catch (\Exception $e) {
            // Clean up on error
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            Notification::make()
                ->title(__('admin.import.error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
