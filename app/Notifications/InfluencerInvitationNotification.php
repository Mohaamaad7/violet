<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InfluencerInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $password;
    protected string $couponCode;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password, string $couponCode)
    {
        $this->password = $password;
        $this->couponCode = $couponCode;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = url('/partners/login');

        return (new MailMessage)
            ->subject('🎉 ' . __('notifications.influencer_invitation.subject'))
            ->greeting(__('notifications.influencer_invitation.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.influencer_invitation.intro'))
            ->line('---')
            ->line('**' . __('notifications.influencer_invitation.login_details') . '**')
            ->line(__('notifications.influencer_invitation.email_label') . ': ' . $notifiable->email)
            ->line(__('notifications.influencer_invitation.password_label') . ': ' . $this->password)
            ->line('---')
            ->line('**' . __('notifications.influencer_invitation.coupon_section') . '**')
            ->line(__('notifications.influencer_invitation.your_code') . ': **' . $this->couponCode . '**')
            ->line('---')
            ->action(__('notifications.influencer_invitation.login_button'), $loginUrl)
            ->line(__('notifications.influencer_invitation.change_password_note'))
            ->salutation(__('notifications.influencer_invitation.salutation'));
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'influencer_invitation',
            'coupon_code' => $this->couponCode,
            'message' => __('notifications.influencer_invitation.db_message', [
                'code' => $this->couponCode,
            ]),
        ];
    }
}
