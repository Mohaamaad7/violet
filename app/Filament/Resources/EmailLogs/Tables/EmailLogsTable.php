<?php

namespace App\Filament\Resources\EmailLogs\Tables;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmailLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                
                TextColumn::make('emailTemplate.name')
                    ->label('القالب')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                
                TextColumn::make('recipient_email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),
                
                TextColumn::make('recipient_name')
                    ->label('الاسم')
                    ->searchable()
                    ->toggleable(),
                
                TextColumn::make('subject')
                    ->label('العنوان')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state)
                    ->searchable(),
                
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => EmailLog::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'queued' => 'info',
                        'sent' => 'primary',
                        'delivered' => 'success',
                        'opened' => 'success',
                        'clicked' => 'success',
                        'failed' => 'danger',
                        'bounced' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'queued' => 'heroicon-o-queue-list',
                        'sent' => 'heroicon-o-paper-airplane',
                        'delivered' => 'heroicon-o-check',
                        'opened' => 'heroicon-o-eye',
                        'clicked' => 'heroicon-o-cursor-arrow-rays',
                        'failed' => 'heroicon-o-x-circle',
                        'bounced' => 'heroicon-o-arrow-uturn-left',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                
                TextColumn::make('related_type')
                    ->label('النوع المرتبط')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('sent_at')
                    ->label('تاريخ الإرسال')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->placeholder('لم يُرسل'),
                
                TextColumn::make('error_message')
                    ->label('الخطأ')
                    ->limit(20)
                    ->tooltip(fn ($state) => $state)
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(EmailLog::STATUSES),
                
                SelectFilter::make('email_template_id')
                    ->label('القالب')
                    ->relationship('emailTemplate', 'name')
                    ->preload()
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->emptyStateHeading('لا توجد سجلات بريد')
            ->emptyStateDescription('ستظهر هنا سجلات الرسائل المرسلة')
            ->emptyStateIcon('heroicon-o-inbox');
    }
}
