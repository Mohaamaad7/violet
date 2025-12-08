<?php

namespace App\Filament\Resources\EmailLogs\Schemas;

use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Schema;

class EmailLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('email_template_id')
                    ->relationship('emailTemplate', 'name')
                    ->default(null),
                TextInput::make('related_type')
                    ->default(null),
                TextInput::make('related_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('recipient_email')
                    ->email()
                    ->required(),
                TextInput::make('recipient_name')
                    ->default(null),
                TextInput::make('subject')
                    ->required(),
                TextInput::make('locale')
                    ->required()
                    ->default('ar'),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'queued' => 'Queued',
            'sent' => 'Sent',
            'delivered' => 'Delivered',
            'opened' => 'Opened',
            'clicked' => 'Clicked',
            'failed' => 'Failed',
            'bounced' => 'Bounced',
        ])
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('queued_at'),
                DateTimePicker::make('sent_at'),
                DateTimePicker::make('delivered_at'),
                DateTimePicker::make('opened_at'),
                DateTimePicker::make('clicked_at'),
                DateTimePicker::make('failed_at'),
                Textarea::make('error_message')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
