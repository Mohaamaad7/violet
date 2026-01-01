<?php

namespace App\Filament\Resources\Influencers\Schemas;

use App\Models\Influencer;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(trans_db('admin.payouts.sections.payout_info'))
                ->schema([
                    Select::make('influencer_id')
                        ->label(trans_db('admin.payouts.fields.influencer'))
                        ->relationship('influencer', 'id')
                        ->getOptionLabelFromRecordUsing(fn(Influencer $record) => $record->user?->name . ' (' . number_format($record->balance, 2) . ' ' . trans_db('admin.currency.egp_short') . ')')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->disabled(fn($context) => $context === 'view'),

                    TextInput::make('amount')
                        ->label(trans_db('admin.payouts.fields.amount'))
                        ->numeric()
                        ->minValue(1)
                        ->suffix(trans_db('admin.currency.egp_short'))
                        ->required()
                        ->disabled(fn($context) => $context === 'view'),

                    Select::make('method')
                        ->label(trans_db('admin.payouts.fields.method'))
                        ->options([
                            'bank_transfer' => trans_db('admin.payouts.methods.bank_transfer'),
                            'cash' => trans_db('admin.payouts.methods.cash'),
                            'wallet' => trans_db('admin.payouts.methods.wallet'),
                        ])
                        ->required()
                        ->native(false)
                        ->disabled(fn($context) => $context === 'view'),
                ])
                ->columns(3),

            Section::make(trans_db('admin.payouts.sections.bank_info'))
                ->schema([
                    TextInput::make('bank_details.bank_name')
                        ->label(trans_db('admin.payouts.fields.bank_name'))
                        ->disabled(fn($context) => $context === 'view'),

                    TextInput::make('bank_details.account_number')
                        ->label(trans_db('admin.payouts.fields.account_number'))
                        ->disabled(fn($context) => $context === 'view'),

                    TextInput::make('bank_details.account_name')
                        ->label(trans_db('admin.payouts.fields.account_name'))
                        ->disabled(fn($context) => $context === 'view'),

                    TextInput::make('bank_details.iban')
                        ->label(trans_db('admin.payouts.fields.iban'))
                        ->disabled(fn($context) => $context === 'view'),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make(trans_db('admin.payouts.sections.approval'))
                ->schema([
                    Select::make('status')
                        ->label(trans_db('admin.payouts.fields.status'))
                        ->options([
                            'pending' => trans_db('admin.payouts.status.pending'),
                            'approved' => trans_db('admin.payouts.status.approved'),
                            'rejected' => trans_db('admin.payouts.status.rejected'),
                            'paid' => trans_db('admin.payouts.status.paid'),
                        ])
                        ->disabled()
                        ->native(false),

                    TextInput::make('transaction_reference')
                        ->label(trans_db('admin.payouts.fields.transaction_ref'))
                        ->disabled(fn($context) => $context === 'view'),

                    Textarea::make('notes')
                        ->label(trans_db('admin.payouts.fields.notes'))
                        ->rows(2)
                        ->columnSpanFull()
                        ->disabled(fn($context) => $context === 'view'),

                    Placeholder::make('approved_by_name')
                        ->label(trans_db('admin.payouts.fields.approved_by'))
                        ->content(fn($record) => $record?->approver?->name ?? '-')
                        ->visible(fn($record) => $record?->approved_by !== null),

                    Placeholder::make('approved_at_display')
                        ->label(trans_db('admin.payouts.fields.approved_at'))
                        ->content(fn($record) => $record?->approved_at?->format('Y-m-d H:i') ?? '-')
                        ->visible(fn($record) => $record?->approved_at !== null),

                    Placeholder::make('paid_by_name')
                        ->label(trans_db('admin.payouts.fields.paid_by'))
                        ->content(fn($record) => $record?->payer?->name ?? '-')
                        ->visible(fn($record) => $record?->paid_by !== null),

                    Placeholder::make('paid_at_display')
                        ->label(trans_db('admin.payouts.fields.paid_at'))
                        ->content(fn($record) => $record?->paid_at?->format('Y-m-d H:i') ?? '-')
                        ->visible(fn($record) => $record?->paid_at !== null),

                    Textarea::make('rejection_reason')
                        ->label(trans_db('admin.payouts.fields.rejection_reason'))
                        ->rows(2)
                        ->disabled()
                        ->visible(fn($record) => $record?->status === 'rejected')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->visible(fn($record) => $record !== null),
        ]);
    }
}
