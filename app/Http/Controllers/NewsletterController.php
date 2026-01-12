<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe(string $token)
    {
        $subscriber = NewsletterSubscription::where('unsubscribe_token', $token)->firstOrFail();

        return view('newsletter.unsubscribe', compact('subscriber'));
    }

    /**
     * Process unsubscribe request
     */
    public function processUnsubscribe(Request $request, string $token)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $subscriber = NewsletterSubscription::where('unsubscribe_token', $token)->firstOrFail();
        
        $subscriber->unsubscribe($request->input('reason'));

        return view('newsletter.unsubscribed');
    }
}
