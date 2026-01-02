<?php

namespace App\Filament\Resources\Influencers\Tables;

use App\Models\InfluencerApplication;
use App\Services\InfluencerService;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label(trans_db('admin.applications.fields.full_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label(trans_db('admin.applications.fields.email'))
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('phone')
                    ->label(trans_db('admin.applications.fields.phone'))
                    ->icon('heroicon-o-phone'),

                TextColumn::make('total_followers')
                    ->label(trans_db('admin.applications.fields.total_followers'))
                    ->state(
                        fn(InfluencerApplication $record): int =>
                        ($record->instagram_followers ?? 0) +
                        ($record->facebook_followers ?? 0) +
                        ($record->tiktok_followers ?? 0) +
                        ($record->youtube_followers ?? 0) +
                        ($record->twitter_followers ?? 0)
                    )
                    ->numeric()
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderByRaw(
                            'COALESCE(instagram_followers, 0) + COALESCE(facebook_followers, 0) + COALESCE(tiktok_followers, 0) + COALESCE(youtube_followers, 0) + COALESCE(twitter_followers, 0) ' . $direction
                        );
                    }),

                TextColumn::make('status')
                    ->label(trans_db('admin.applications.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => trans_db("admin.applications.status.{$state}"))
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label(trans_db('admin.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans_db('admin.applications.fields.status'))
                    ->options([
                        'pending' => trans_db('admin.applications.status.pending'),
                        'approved' => trans_db('admin.applications.status.approved'),
                        'rejected' => trans_db('admin.applications.status.rejected'),
                    ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label(trans_db('admin.action.export_excel')),
            ])
            ->actions([
                ViewAction::make(),

                // ========================================
                // Approve Action (Enhanced with Coupon)
                // ========================================
                Action::make('approve')
                    ->label(trans_db('admin.applications.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(InfluencerApplication $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading(trans_db('admin.applications.modals.approve_heading'))
                    ->modalDescription(trans_db('admin.applications.modals.approve_description'))
                    ->form([
                        // نسبة عمولة المؤثر
                        TextInput::make('commission_rate')
                            ->label(trans_db('admin.applications.fields.commission_rate'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(10)
                            ->suffix('%')
                            ->required(),

                        // كود الخصم
                        TextInput::make('coupon_code')
                            ->label(trans_db('admin.influencers.fields.coupon_code'))
                            ->required()
                            ->maxLength(20)
                            ->alphaDash()
                            ->default(fn(InfluencerApplication $record) => self::generateCouponCode($record->full_name))
                            ->suffixAction(
                                FormAction::make('generate_code')
                                    ->icon('heroicon-o-arrow-path')
                                    ->tooltip(trans_db('admin.influencers.fields.generate_code'))
                                    ->action(function ($get, $set, InfluencerApplication $record) {
                                        $code = self::generateCouponCode($record->full_name);
                                        $set('coupon_code', $code);
                                    })
                            ),

                        // نوع الخصم للعملاء
                        Radio::make('discount_type')
                            ->label(trans_db('admin.influencers.fields.discount_type'))
                            ->options([
                                'percentage' => trans_db('admin.influencers.discount_types.percentage'),
                                'fixed' => trans_db('admin.influencers.discount_types.fixed'),
                            ])
                            ->default('percentage')
                            ->inline()
                            ->required(),

                        // قيمة الخصم
                        TextInput::make('discount_value')
                            ->label(trans_db('admin.influencers.fields.discount_value'))
                            ->numeric()
                            ->minValue(0)
                            ->default(15)
                            ->suffix(fn($get) => $get('discount_type') === 'percentage' ? '%' : trans_db('admin.currency.egp_short'))
                            ->required(),

                        // إرسال إيميل ترحيب
                        Toggle::make('send_welcome_email')
                            ->label(trans_db('admin.applications.fields.send_welcome_email'))
                            ->helperText(trans_db('admin.applications.fields.send_welcome_email_help'))
                            ->default(true),
                    ])
                    ->action(function (InfluencerApplication $record, array $data): void {
                        try {
                            DB::transaction(function () use ($record, $data) {
                                app(InfluencerService::class)->approveApplicationWithCoupon(
                                    applicationId: $record->id,
                                    commissionRate: (float) $data['commission_rate'],
                                    couponCode: $data['coupon_code'],
                                    discountType: $data['discount_type'],
                                    discountValue: (float) $data['discount_value'],
                                    sendWelcomeEmail: $data['send_welcome_email'] ?? true,
                                    reviewedBy: auth()->id()
                                );
                            });

                            Notification::make()
                                ->title(trans_db('admin.applications.notifications.approved'))
                                ->body(trans_db('admin.influencers.fields.coupon_code') . ': ' . $data['coupon_code'])
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(trans_db('admin.error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // ========================================
                // Reject Action
                // ========================================
                Action::make('reject')
                    ->label(trans_db('admin.applications.actions.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(InfluencerApplication $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading(trans_db('admin.applications.modals.reject_heading'))
                    ->modalDescription(trans_db('admin.applications.modals.reject_description'))
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label(trans_db('admin.applications.fields.rejection_reason'))
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (InfluencerApplication $record, array $data): void {
                        try {
                            app(InfluencerService::class)->rejectApplication(
                                $record->id,
                                $data['rejection_reason'],
                                auth()->id()
                            );

                            Notification::make()
                                ->title(trans_db('admin.applications.notifications.rejected'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(trans_db('admin.error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Generate coupon code from name
     */
    private static function generateCouponCode(string $name): string
    {
        $firstName = strtoupper(Str::before($name, ' '));
        $firstName = preg_replace('/[^A-Z0-9]/', '', $firstName);
        $firstName = substr($firstName, 0, 8); // Max 8 chars from name
        return $firstName . date('Y');
    }
}
