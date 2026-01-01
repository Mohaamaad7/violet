<?php

namespace App\Notifications;

use App\Models\InfluencerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected InfluencerApplication $application
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            return (new MailMessage)
                ->subject('تحديث بخصوص طلب التقديم')
                ->greeting('مرحباً ' . $this->application->full_name . '!')
                ->line('نشكرك على اهتمامك بالتعاون معنا كمؤثر.')
                ->line('بعد مراجعة طلبك بعناية، نأسف لإبلاغك بأننا لم نتمكن من قبوله في الوقت الحالي.')
                ->line('**السبب:** ' . ($this->application->rejection_reason ?? 'لم يتم تحديد سبب'))
                ->line('يمكنك التقديم مرة أخرى في المستقبل.')
                ->line('شكراً لتفهمك.');
        }

        return (new MailMessage)
            ->subject('Update on Your Influencer Application')
            ->greeting('Hello ' . $this->application->full_name . '!')
            ->line('Thank you for your interest in collaborating with us as an influencer.')
            ->line('After carefully reviewing your application, we regret to inform you that we were unable to accept it at this time.')
            ->line('**Reason:** ' . ($this->application->rejection_reason ?? 'No specific reason provided'))
            ->line('You are welcome to apply again in the future.')
            ->line('Thank you for your understanding.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_rejected',
            'application_id' => $this->application->id,
            'reason' => $this->application->rejection_reason,
            'message' => __('admin.applications.notifications.rejected'),
        ];
    }
}
