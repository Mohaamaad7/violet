<?php

namespace App\Notifications;

use App\Models\InfluencerCommission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommissionEarnedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected InfluencerCommission $commission
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = app()->getLocale();
        $amount = number_format($this->commission->commission_amount, 2);
        $order = $this->commission->order;

        if ($locale === 'ar') {
            return (new MailMessage)
                ->subject('لقد ربحت عمولة جديدة! 💰')
                ->greeting('مرحباً!')
                ->line('لديك عمولة جديدة من طلب تم استخدام كود الخصم الخاص بك فيه.')
                ->line('**رقم الطلب:** ' . ($order->order_number ?? 'غير متاح'))
                ->line('**قيمة الطلب:** ' . number_format($this->commission->order_amount, 2) . ' ج.م')
                ->line('**نسبة العمولة:** ' . $this->commission->commission_rate . '%')
                ->line('**مبلغ العمولة:** ' . $amount . ' ج.م')
                ->line('تمت إضافة المبلغ إلى رصيدك.')
                ->line('شكراً لتعاونك معنا!');
        }

        return (new MailMessage)
            ->subject('You Earned a New Commission! 💰')
            ->greeting('Hello!')
            ->line('You have earned a new commission from an order where your discount code was used.')
            ->line('**Order Number:** ' . ($order->order_number ?? 'N/A'))
            ->line('**Order Amount:** ' . number_format($this->commission->order_amount, 2) . ' EGP')
            ->line('**Commission Rate:** ' . $this->commission->commission_rate . '%')
            ->line('**Commission Amount:** ' . $amount . ' EGP')
            ->line('The amount has been added to your balance.')
            ->line('Thank you for your partnership!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'commission_earned',
            'commission_id' => $this->commission->id,
            'order_id' => $this->commission->order_id,
            'amount' => $this->commission->commission_amount,
            'message' => 'Commission earned: ' . number_format($this->commission->commission_amount, 2) . ' EGP',
        ];
    }
}
