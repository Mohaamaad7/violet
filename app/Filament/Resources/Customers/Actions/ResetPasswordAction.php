<?php

namespace App\Filament\Resources\Customers\Actions;

use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordAction
{
    public static function make(): Action
    {
        return Action::make('reset_password')
            ->label(trans_db('admin.customers.actions.reset_password'))
            ->icon('heroicon-o-key')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading(trans_db('admin.customers.password.reset_heading'))
            ->modalDescription(trans_db('admin.customers.password.reset_description'))
            ->modalSubmitActionLabel(trans_db('admin.customers.password.send_reset_link'))
            ->action(function (Customer $record) {
                try {
                    // إرسال رابط إعادة تعيين الباسورد
                    $status = Password::broker('customers')->sendResetLink(
                        ['email' => $record->email]
                    );

                    if ($status === Password::RESET_LINK_SENT) {
                        Notification::make()
                            ->success()
                            ->title(trans_db('admin.customers.password.sent_success'))
                            ->body(__('admin.customers.password.sent_to', ['email' => $record->email]))
                            ->send();
                    } else {
                        throw new \Exception(trans_db('admin.customers.password.sent_failed'));
                    }

                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title(trans_db('admin.customers.password.error'))
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }
}
