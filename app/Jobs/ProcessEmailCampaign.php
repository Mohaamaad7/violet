<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEmailCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public EmailCampaign $campaign
    ) {}

    public function handle(): void
    {
        try {
            // Update campaign status to sending
            $this->campaign->update([
                'status' => 'sending',
                'started_at' => now(),
            ]);

            // Get target subscribers based on campaign settings
            $subscribers = $this->getTargetSubscribers();
            
            // Update recipients count
            $this->campaign->update([
                'recipients_count' => $subscribers->count(),
            ]);

            Log::info("Starting campaign: {$this->campaign->title} for {$subscribers->count()} subscribers");

            // Dispatch individual email jobs with rate limiting
            $delay = 0;
            $rateLimit = $this->campaign->send_rate_limit ?? 50; // emails per minute
            $delayPerEmail = 60 / $rateLimit; // seconds between emails

            foreach ($subscribers as $subscriber) {
                SendCampaignEmail::dispatch($this->campaign, $subscriber)
                    ->delay(now()->addSeconds($delay));
                
                $delay += $delayPerEmail;
            }

            // Mark campaign as sent (will be completed when all jobs finish)
            $this->campaign->update([
                'status' => 'sent',
                'completed_at' => now()->addSeconds($delay),
            ]);

            Log::info("Campaign queued successfully: {$this->campaign->title}");

        } catch (\Exception $e) {
            Log::error("Campaign processing failed: {$this->campaign->title}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->campaign->update([
                'status' => 'cancelled',
            ]);

            throw $e;
        }
    }

    protected function getTargetSubscribers()
    {
        $query = NewsletterSubscription::query()->where('status', 'active');

        return match($this->campaign->send_to) {
            'all' => $query->get(),
            'active_only' => $query->get(),
            'recent' => $query->where('subscribed_at', '>=', now()->subDays(30))->get(),
            'custom' => $this->applyCustomFilters($query)->get(),
            default => $query->get(),
        };
    }

    protected function applyCustomFilters($query)
    {
        if ($this->campaign->custom_filters) {
            $filters = $this->campaign->custom_filters;
            
            // Apply custom filters based on campaign settings
            // Can be extended with more filter options
            if (isset($filters['days'])) {
                $query->where('subscribed_at', '>=', now()->subDays($filters['days']));
            }
        }

        return $query;
    }
}
