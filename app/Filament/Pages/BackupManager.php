<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ChecksPageAccess;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Backup Manager Page
 * 
 * Allows Super Admin to create, download, and manage backups.
 */
class BackupManager extends Page
{
    use ChecksPageAccess;

    protected static ?int $navigationSort = 98;

    public bool $includeDatabase = true;
    public bool $includeFiles = true;

    protected string $view = 'filament.pages.backup-manager';

    public static function getNavigationIcon(): string|null
    {
        return 'heroicon-o-cloud-arrow-up';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.backup.title');
    }

    public function getTitle(): string
    {
        return __('admin.backup.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.settings');
    }

    /**
     * Only Super Admin can access this page
     */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    /**
     * Get list of existing backups
     */
    public function getBackups(): array
    {
        $backupPath = config('backup.backup.name', 'Laravel');
        $disk = Storage::disk('local');
        $path = "{$backupPath}";

        if (!$disk->exists($path)) {
            return [];
        }

        $files = $disk->files($path);
        $backups = [];

        foreach ($files as $file) {
            if (str_ends_with($file, '.zip')) {
                $sizeBytes = $disk->size($file);

                $backups[] = [
                    'filename' => basename($file),
                    'path' => $file,
                    'size' => $this->formatBytes($sizeBytes),
                    'size_bytes' => $sizeBytes,
                    'created_at' => date('Y-m-d H:i:s', $disk->lastModified($file)),
                    'type' => $this->detectBackupType($disk->path($file), $sizeBytes),
                ];
            }
        }

        // Sort by date descending
        usort($backups, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        return $backups;
    }

    /**
     * Detect backup type by examining the zip contents
     */
    protected function detectBackupType(string $zipPath, int $sizeBytes): array
    {
        $type = [
            'has_db' => false,
            'has_files' => false,
            'label' => __('admin.backup.type_unknown'),
            'color' => 'gray',
        ];

        try {
            $zip = new \ZipArchive();
            if ($zip->open($zipPath) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);

                    // Check for database dump
                    if (str_ends_with($filename, '.sql') || str_contains($filename, 'db-dumps')) {
                        $type['has_db'] = true;
                    }

                    // Check for files (images, etc)
                    if (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|pdf|mp4|mp3)$/i', $filename)) {
                        $type['has_files'] = true;
                    }
                }
                $zip->close();
            }
        } catch (\Exception $e) {
            // If we can't read the zip, use size heuristics
            // DB-only backups are usually small (< 1MB)
            // File backups are usually larger
            if ($sizeBytes < 500 * 1024) { // < 500KB
                $type['has_db'] = true;
            } else {
                $type['has_db'] = true;
                $type['has_files'] = true;
            }
        }

        // Set label and color based on type
        if ($type['has_db'] && $type['has_files']) {
            $type['label'] = __('admin.backup.type_full');
            $type['color'] = 'success';
        } elseif ($type['has_db']) {
            $type['label'] = __('admin.backup.type_db_only');
            $type['color'] = 'info';
        } elseif ($type['has_files']) {
            $type['label'] = __('admin.backup.type_files_only');
            $type['color'] = 'warning';
        }

        return $type;
    }

    /**
     * Create a new backup
     */
    public function createBackup(): void
    {
        // Validate at least one option is selected
        if (!$this->includeDatabase && !$this->includeFiles) {
            Notification::make()
                ->title(__('admin.backup.select_at_least_one'))
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title(__('admin.backup.creating'))
            ->info()
            ->send();

        try {
            $options = [];
            $backupType = 'full';

            if ($this->includeDatabase && !$this->includeFiles) {
                $options['--only-db'] = true;
                $backupType = 'db_only';
            } elseif ($this->includeFiles && !$this->includeDatabase) {
                $options['--only-files'] = true;
                $backupType = 'files_only';
            }
            // If both are selected, no options = full backup (db + files)

            Artisan::call('backup:run', $options);

            Notification::make()
                ->title(__('admin.backup.success'))
                ->body(__('admin.backup.type_' . $backupType))
                ->success()
                ->send();

            // Log activity with details
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'include_database' => $this->includeDatabase,
                    'include_files' => $this->includeFiles,
                    'backup_type' => $backupType,
                    'artisan_options' => $options,
                ])
                ->log('تم إنشاء نسخة احتياطية: ' . __('admin.backup.type_' . $backupType));

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('admin.backup.failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Download a backup file
     */
    public function downloadBackup(string $path): StreamedResponse
    {
        $disk = Storage::disk('local');

        if (!$disk->exists($path)) {
            Notification::make()
                ->title(__('admin.backup.not_found'))
                ->danger()
                ->send();

            return response()->streamDownload(fn() => null, 'error.txt');
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['file' => basename($path)])
            ->log('تم تحميل نسخة احتياطية');

        return response()->streamDownload(
            fn() => print ($disk->get($path)),
            basename($path),
            ['Content-Type' => 'application/zip']
        );
    }

    /**
     * Delete a backup file
     */
    public function deleteBackup(string $path): void
    {
        $disk = Storage::disk('local');

        if (!$disk->exists($path)) {
            Notification::make()
                ->title(__('admin.backup.not_found'))
                ->danger()
                ->send();
            return;
        }

        $filename = basename($path);
        $disk->delete($path);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['file' => $filename])
            ->log('تم حذف نسخة احتياطية');

        Notification::make()
            ->title(__('admin.backup.deleted'))
            ->body($filename)
            ->success()
            ->send();
    }

    /**
     * Run cleanup to remove old backups
     */
    public function runCleanup(): void
    {
        try {
            Artisan::call('backup:clean');

            Notification::make()
                ->title(__('admin.backup.cleanup_success'))
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('admin.backup.cleanup_failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get total backup size
     */
    public function getTotalBackupSize(): string
    {
        $backups = $this->getBackups();
        $totalBytes = array_sum(array_column($backups, 'size_bytes'));
        return $this->formatBytes($totalBytes);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
