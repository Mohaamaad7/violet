<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailCampaign $campaign,
        public NewsletterSubscription $subscriber
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
        );
    }

    public function content(): Content
    {
        // Choose view based on campaign type
        $view = match($this->campaign->type) {
            'offers' => 'emails.campaign-offers',
            'custom' => 'emails.campaign-custom',
            default => 'emails.campaign-custom',
        };

        return new Content(
            view: $view,
            with: [
                'campaign' => $this->campaign,
                'subscriber' => $this->subscriber,
                'unsubscribeUrl' => route('newsletter.unsubscribe', [
                    'token' => $this->subscriber->unsubscribe_token
                ]),
            ],
        );
    }
}
