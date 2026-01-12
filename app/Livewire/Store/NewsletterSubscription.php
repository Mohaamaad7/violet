<?php

namespace App\Livewire\Store;

use App\Models\NewsletterSubscription as Subscription;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;

class NewsletterSubscription extends Component
{
    #[Validate('required|email|unique:newsletter_subscriptions,email')]
    public string $email = '';
    
    public bool $loading = false;
    public ?string $message = null;
    public ?string $messageType = null; // 'success' or 'error'
    
    public function subscribe()
    {
        $this->loading = true;
        $this->message = null;
        $this->messageType = null;
        
        try {
            // Validate
            $this->validate();
            
            // Create subscription
            Subscription::create([
                'email' => $this->email,
                'status' => 'active',
                'source' => $this->getSource(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'subscribed_at' => now(),
            ]);
            
            // Success message
            $this->message = __('تم الاشتراك بنجاح! سنرسل لك آخر العروض والتحديثات.');
            $this->messageType = 'success';
            $this->email = '';
            
            // Reset validation
            $this->resetValidation();
            
            // Optional: Send welcome email (implement later)
            // Mail::to($this->email)->queue(new WelcomeSubscriber());
            
            // Dispatch browser event for analytics
            $this->dispatch('newsletter-subscribed');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->messageType = 'error';
            
            // Check if it's duplicate email
            if (isset($e->validator->failed()['email']['Unique'])) {
                $this->message = __('هذا البريد الإلكتروني مشترك بالفعل في نشرتنا.');
            }
            
            throw $e;
            
        } catch (\Exception $e) {
            Log::error('Newsletter subscription error', [
                'email' => $this->email,
                'error' => $e->getMessage(),
            ]);
            
            $this->message = __('عذراً، حدث خطأ. يرجى المحاولة مرة أخرى.');
            $this->messageType = 'error';
        } finally {
            $this->loading = false;
        }
    }
    
    /**
     * Determine the source of subscription
     */
    protected function getSource(): string
    {
        $currentUrl = request()->url();
        
        if (str_contains($currentUrl, '/contact')) {
            return 'contact';
        } elseif (str_contains($currentUrl, '/checkout')) {
            return 'checkout';
        } else {
            return 'footer';
        }
    }
    
    public function render()
    {
        return view('livewire.store.newsletter-subscription');
    }
}
