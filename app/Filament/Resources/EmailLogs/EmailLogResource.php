<?php

namespace App\Filament\Resources\EmailLogs;

use App\Filament\Resources\Concerns\ChecksResourceAccess;
use App\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use App\Filament\Resources\EmailLogs\Tables\EmailLogsTable;
use App\Models\EmailLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmailLogResource extends Resource
{
    use ChecksResourceAccess;

    protected static ?string $model = EmailLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static ?string $recordTitleAttribute = 'recipient_email';

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.email_logs.title');
    }

    public static function getModelLabel(): string
    {
        return __('admin.email_logs.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.email_logs.plural');
    }

    public static function canCreate(): bool
    {
        return false; // Logs are created automatically
    }

    public static function table(Table $table): Table
    {
        return EmailLogsTable::configure($table);
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
            'index' => ListEmailLogs::route('/'),
        ];
    }
}
