<?php

namespace App\Notifications;

use App\Models\CommissionPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutProcessedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected CommissionPayout $payout
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = app()->getLocale();
        $amount = number_format($this->payout->amount, 2);

        if ($locale === 'ar') {
            return (new MailMessage)
                ->subject('تم تحويل أرباحك! 🎉')
                ->greeting('مرحباً!')
                ->line('يسعدنا إخبارك بأنه تم تحويل أرباحك بنجاح.')
                ->line('**المبلغ:** ' . $amount . ' ج.م')
                ->line('**طريقة الدفع:** ' . __('admin.payouts.methods.' . $this->payout->method))
                ->line('**رقم المرجع:** ' . ($this->payout->transaction_reference ?? 'غير متاح'))
                ->line('**تاريخ التحويل:** ' . $this->payout->paid_at?->format('Y-m-d H:i'))
                ->line('شكراً لتعاونك المستمر معنا!');
        }

        return (new MailMessage)
            ->subject('Your Payout Has Been Processed! 🎉')
            ->greeting('Hello!')
            ->line('We are pleased to inform you that your payout has been successfully processed.')
            ->line('**Amount:** ' . $amount . ' EGP')
            ->line('**Payment Method:** ' . __('admin.payouts.methods.' . $this->payout->method))
            ->line('**Transaction Reference:** ' . ($this->payout->transaction_reference ?? 'N/A'))
            ->line('**Payment Date:** ' . $this->payout->paid_at?->format('Y-m-d H:i'))
            ->line('Thank you for your continued partnership!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payout_processed',
            'payout_id' => $this->payout->id,
            'amount' => $this->payout->amount,
            'transaction_reference' => $this->payout->transaction_reference,
            'message' => 'Payout processed: ' . number_format($this->payout->amount, 2) . ' EGP',
        ];
    }
}
