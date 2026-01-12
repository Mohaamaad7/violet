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

class ProcessEmailCampaign
{
    use Dispatchable;

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

            // Send emails immediately (synchronous)
            foreach ($subscribers as $subscriber) {
                SendCampaignEmail::dispatch($this->campaign, $subscriber);
            }

            // Mark campaign as sent
            $this->campaign->update([
                'status' => 'sent',
                'completed_at' => now(),
            ]);

            Log::info("Campaign sent successfully: {$this->campaign->title}");

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
