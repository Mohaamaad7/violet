<?php

namespace App\Filament\Resources\EmailTemplates\Tables;

use App\Models\EmailTemplate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم القالب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('المعرف')
                    ->searchable()
                    ->copyable()
                    ->color('gray')
                    ->size('sm'),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => EmailTemplate::TYPES[$state] ?? $state)
                    ->color(fn(string $state): string => match ($state) {
                        'customer' => 'success',
                        'admin' => 'warning',
                        'system' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('category')
                    ->label('التصنيف')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => EmailTemplate::CATEGORIES[$state] ?? $state)
                    ->color(fn(string $state): string => match ($state) {
                        'order' => 'primary',
                        'auth' => 'warning',
                        'notification' => 'info',
                        'marketing' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('subject_ar')
                    ->label('العنوان')
                    ->limit(40)
                    ->tooltip(fn($state) => $state)
                    ->toggleable(),

                ColorColumn::make('primary_color')
                    ->label('اللون')
                    ->copyable(),

                IconColumn::make('is_active')
                    ->label('مفعّل')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('logs_count')
                    ->label('الرسائل')
                    ->counts('logs')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options(EmailTemplate::TYPES),

                SelectFilter::make('category')
                    ->label('التصنيف')
                    ->options(EmailTemplate::CATEGORIES),

                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('مفعّل')
                    ->falseLabel('غير مفعّل'),

                TrashedFilter::make()
                    ->label('المحذوفة'),
            ])
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->label('تصدير Excel')
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                            ->fromTable()
                            ->withFilename('email-templates-' . now()->format('Y-m-d'))
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                    ForceDeleteBulkAction::make()
                        ->label('حذف نهائي'),
                    RestoreBulkAction::make()
                        ->label('استعادة'),
                ]),
            ])
            ->emptyStateHeading('لا توجد قوالب بريد')
            ->emptyStateDescription('ابدأ بإنشاء قالب بريد جديد')
            ->emptyStateIcon('heroicon-o-envelope');
    }
}
