<?php

namespace App\Livewire;

use App\Models\InfluencerApplication;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class InfluencerApplicationForm extends Component
{
    public string $full_name = '';
    public string $email = '';
    public string $phone = '';
    public ?string $instagram_url = null;
    public ?int $instagram_followers = null;
    public ?string $facebook_url = null;
    public ?int $facebook_followers = null;
    public ?string $tiktok_url = null;
    public ?int $tiktok_followers = null;
    public ?string $youtube_url = null;
    public ?int $youtube_followers = null;
    public ?string $twitter_url = null;
    public ?int $twitter_followers = null;
    public ?string $content_type = null;
    public ?string $portfolio = null;

    public bool $submitted = false;
    public bool $alreadyApplied = false;

    protected $rules = [
        'full_name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:20',
        'instagram_url' => 'nullable|url',
        'instagram_followers' => 'nullable|integer|min:0',
        'facebook_url' => 'nullable|url',
        'facebook_followers' => 'nullable|integer|min:0',
        'tiktok_url' => 'nullable|url',
        'tiktok_followers' => 'nullable|integer|min:0',
        'youtube_url' => 'nullable|url',
        'youtube_followers' => 'nullable|integer|min:0',
        'twitter_url' => 'nullable|url',
        'twitter_followers' => 'nullable|integer|min:0',
        'content_type' => 'nullable|string|max:100',
        'portfolio' => 'nullable|string|max:1000',
    ];

    public function mount()
    {
        // Pre-fill if customer is logged in
        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();
            $this->full_name = $customer->name ?? '';
            $this->email = $customer->email ?? '';
            $this->phone = $customer->phone ?? '';

            // Check if already applied
            $this->alreadyApplied = InfluencerApplication::where('email', $this->email)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();
        }
    }

    public function submit()
    {
        $this->validate();

        // Check for existing application
        $existing = InfluencerApplication::where('email', $this->email)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            $this->alreadyApplied = true;
            return;
        }

        // At least one social media account required
        if (!$this->instagram_url && !$this->facebook_url && !$this->tiktok_url && !$this->youtube_url && !$this->twitter_url) {
            $this->addError('instagram_url', __('messages.influencer.at_least_one_social'));
            return;
        }

        InfluencerApplication::create([
            'user_id' => Auth::guard('customer')->id(),
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'instagram_url' => $this->instagram_url,
            'instagram_followers' => $this->instagram_followers,
            'facebook_url' => $this->facebook_url,
            'facebook_followers' => $this->facebook_followers,
            'tiktok_url' => $this->tiktok_url,
            'tiktok_followers' => $this->tiktok_followers,
            'youtube_url' => $this->youtube_url,
            'youtube_followers' => $this->youtube_followers,
            'twitter_url' => $this->twitter_url,
            'twitter_followers' => $this->twitter_followers,
            'content_type' => $this->content_type,
            'portfolio' => $this->portfolio,
            'status' => 'pending',
        ]);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.influencer-application-form')
            ->layout('layouts.store');
    }
}
