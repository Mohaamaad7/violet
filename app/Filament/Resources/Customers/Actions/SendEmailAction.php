<?php

namespace App\Filament\Resources\Customers\Actions;

use App\Models\Customer;
use App\Services\EmailService;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class SendEmailAction
{
    public static function make(): Action
    {
        return Action::make('send_email')
            ->label(trans_db('admin.customers.actions.send_email'))
            ->icon('heroicon-o-envelope')
            ->color('primary')
            ->form([
                TextInput::make('subject')
                    ->label(trans_db('admin.customers.email.subject'))
                    ->required()
                    ->maxLength(255),

                RichEditor::make('message')
                    ->label(trans_db('admin.customers.email.message'))
                    ->required()
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'link',
                        'bulletList',
                        'orderedList',
                    ]),
            ])
            ->action(function (Customer $record, array $data) {
                try {
                    $emailService = app(EmailService::class);
                    
                    // إرسال البريد الإلكتروني
                    $emailService->sendCustomEmail(
                        to: $record->email,
                        subject: $data['subject'],
                        content: $data['message'],
                        customerName: $record->name
                    );

                    Notification::make()
                        ->success()
                        ->title(trans_db('admin.customers.email.sent_success'))
                        ->body(__('admin.customers.email.sent_to', ['email' => $record->email]))
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title(trans_db('admin.customers.email.sent_failed'))
                        ->body($e->getMessage())
                        ->send();
                }
            })
            ->modalWidth('xl');
    }
}
