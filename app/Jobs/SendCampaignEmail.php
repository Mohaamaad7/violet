<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\CampaignLog;
use App\Models\EmailCampaign;
use App\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmail
{
    use Dispatchable;

    public function __construct(
        public EmailCampaign $campaign,
        public NewsletterSubscription $subscriber
    ) {}

    public function handle(): void
    {
        // Create log entry
        $log = CampaignLog::create([
            'campaign_id' => $this->campaign->id,
            'subscriber_id' => $this->subscriber->id,
            'status' => 'sending',
        ]);

        try {
            // Send email
            Mail::to($this->subscriber->email)->send(
                new CampaignMail($this->campaign, $this->subscriber)
            );

            // Update log as sent
            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Increment campaign sent count
            $this->campaign->increment('emails_sent');

            Log::info("Email sent successfully", [
                'campaign' => $this->campaign->title,
                'email' => $this->subscriber->email,
            ]);

        } catch (\Exception $e) {
            // Update log as failed
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Increment campaign failed count
            $this->campaign->increment('emails_failed');

            Log::error("Email send failed", [
                'campaign' => $this->campaign->title,
                'email' => $this->subscriber->email,
                'error' => $e->getMessage(),
            ]);

            // Don't retry if it's a bounce
            if (str_contains($e->getMessage(), 'bounce') || str_contains($e->getMessage(), 'invalid')) {
                $this->subscriber->markAsBounced();
                $this->campaign->increment('emails_bounced');
                return;
            }

            throw $e;
        }
    }
}
