<?php

namespace App\Notifications;

use App\Models\InfluencerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected InfluencerApplication $application,
        protected string $discountCode
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
                ->subject('تم قبول طلب التقديم كمؤثر 🎉')
                ->greeting('مرحباً ' . $this->application->full_name . '!')
                ->line('يسعدنا إخبارك بأنه تم قبول طلب التقديم الخاص بك.')
                ->line('يمكنك الآن البدء في مشاركة كود الخصم الخاص بك مع متابعيك.')
                ->line('**كود الخصم الخاص بك:** ' . $this->discountCode)
                ->action('تسجيل الدخول', url('/login'))
                ->line('شكراً لانضمامك إلينا!');
        }

        return (new MailMessage)
            ->subject('Your Influencer Application has been Approved 🎉')
            ->greeting('Hello ' . $this->application->full_name . '!')
            ->line('We are delighted to inform you that your influencer application has been approved.')
            ->line('You can now start sharing your discount code with your followers.')
            ->line('**Your discount code:** ' . $this->discountCode)
            ->action('Login Now', url('/login'))
            ->line('Thank you for joining us!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_approved',
            'application_id' => $this->application->id,
            'discount_code' => $this->discountCode,
            'message' => __('admin.applications.notifications.approved'),
        ];
    }
}
