<?php

namespace App\Notifications;

use App\Models\Influencer;
use App\Models\InfluencerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $name;
    protected string $discountCode;

    /**
     * Create a new notification instance.
     * Accepts either InfluencerApplication or Influencer
     */
    public function __construct(
        InfluencerApplication|Influencer $record,
        string $discountCode
    ) {
        $this->discountCode = $discountCode;

        if ($record instanceof InfluencerApplication) {
            $this->name = $record->full_name;
        } else {
            $this->name = $record->user?->name ?? 'Partner';
        }
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = app()->getLocale();
        $loginUrl = url('/partners/login');

        if ($locale === 'ar') {
            return (new MailMessage)
                ->subject('🎉 مبروك! تم قبول طلبك كشريك في Flower Violet')
                ->greeting('مرحباً ' . $this->name . '!')
                ->line('يسعدنا إخبارك بأنه تم قبول طلب التقديم الخاص بك.')
                ->line('---')
                ->line('**كود الخصم الخاص بك:** ' . $this->discountCode)
                ->line('شارك هذا الكود مع متابعيك ليحصلوا على خصم، وستحصل أنت على عمولة من كل طلب!')
                ->line('---')
                ->action('دخول بوابة الشركاء', $loginUrl)
                ->salutation('فريق Flower Violet');
        }

        return (new MailMessage)
            ->subject('🎉 Congratulations! Your Partner Application has been Approved')
            ->greeting('Hello ' . $this->name . '!')
            ->line('We are delighted to inform you that your influencer application has been approved.')
            ->line('---')
            ->line('**Your discount code:** ' . $this->discountCode)
            ->line('Share this code with your followers for a discount, and earn commission on every order!')
            ->line('---')
            ->action('Access Partners Portal', $loginUrl)
            ->salutation('Flower Violet Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_approved',
            'discount_code' => $this->discountCode,
            'message' => __('admin.applications.notifications.approved'),
        ];
    }
}
