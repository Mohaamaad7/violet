<?php

namespace App\Services;

use App\Mail\TemplateMail;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function __construct(
        protected EmailTemplateService $templateService
    ) {}

    /**
     * Send email using a template.
     */
    public function send(
        string $templateSlug,
        string $recipientEmail,
        array $variables = [],
        ?string $recipientName = null,
        ?Model $related = null,
        ?string $locale = null
    ): ?EmailLog {
        // Find template
        $template = EmailTemplate::findBySlug($templateSlug);

        if (!$template) {
            Log::error('Email template not found', ['slug' => $templateSlug]);
            return null;
        }

        if (!$template->is_active) {
            Log::warning('Email template is inactive', ['slug' => $templateSlug]);
            return null;
        }

        // Determine locale
        $locale = $locale ?? app()->getLocale();

        // Get subject
        $subject = $this->templateService->getSubject($template, $variables, $locale);

        // Create log entry
        $log = $this->templateService->createLog(
            template: $template,
            recipientEmail: $recipientEmail,
            subject: $subject,
            recipientName: $recipientName,
            related: $related,
            locale: $locale,
            metadata: ['variables' => $variables]
        );

        try {
            // Create mailable
            $mailable = new TemplateMail(
                template: $template,
                variables: $variables,
                locale: $locale,
                recipientName: $recipientName,
                related: $related,
            );

            $mailable->withLog($log);

            // Send immediately
            Mail::to($recipientEmail, $recipientName)
                ->send($mailable);

            // Mark as sent
            $log->markAsSent();

            return $log;
        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'template' => $templateSlug,
                'recipient' => $recipientEmail,
                'error' => $e->getMessage(),
            ]);

            $log->markAsFailed($e->getMessage());

            return $log;
        }
    }

    /**
     * Send order confirmation email.
     */
    public function sendOrderConfirmation(
        \App\Models\Order $order,
        ?string $locale = null
    ): ?EmailLog {
        $recipientEmail = $order->user?->email ?? $order->guest_email;
        $recipientName = $order->user?->name ?? $order->guest_name;

        if (!$recipientEmail) {
            Log::warning('No email address for order confirmation', ['order_id' => $order->id]);
            return null;
        }

        $variables = $this->getOrderVariables($order);

        return $this->send(
            templateSlug: 'order-confirmation',
            recipientEmail: $recipientEmail,
            variables: $variables,
            recipientName: $recipientName,
            related: $order,
            locale: $locale ?? $order->user?->locale ?? 'ar'
        );
    }

    /**
     * Send order status update email.
     */
    public function sendOrderStatusUpdate(
        \App\Models\Order $order,
        ?string $locale = null
    ): ?EmailLog {
        $recipientEmail = $order->user?->email ?? $order->guest_email;
        $recipientName = $order->user?->name ?? $order->guest_name;

        if (!$recipientEmail) {
            return null;
        }

        $variables = $this->getOrderVariables($order);

        return $this->send(
            templateSlug: 'order-status-update',
            recipientEmail: $recipientEmail,
            variables: $variables,
            recipientName: $recipientName,
            related: $order,
            locale: $locale ?? $order->user?->locale ?? 'ar'
        );
    }

    /**
     * Send welcome email.
     */
    public function sendWelcome(
        \App\Models\User $user,
        ?string $locale = null
    ): ?EmailLog {
        $variables = [
            'user_name' => $user->name,
            'user_email' => $user->email,
        ];

        return $this->send(
            templateSlug: 'welcome',
            recipientEmail: $user->email,
            variables: $variables,
            recipientName: $user->name,
            related: $user,
            locale: $locale ?? $user->locale ?? 'ar'
        );
    }

    /**
     * Send password reset email.
     */
    public function sendPasswordReset(
        \App\Models\User $user,
        string $resetUrl,
        ?string $locale = null
    ): ?EmailLog {
        $variables = [
            'user_name' => $user->name,
            'reset_url' => $resetUrl,
        ];

        return $this->send(
            templateSlug: 'password-reset',
            recipientEmail: $user->email,
            variables: $variables,
            recipientName: $user->name,
            related: $user,
            locale: $locale ?? $user->locale ?? 'ar'
        );
    }

    /**
     * Get order variables for email templates.
     */
    protected function getOrderVariables(\App\Models\Order $order): array
    {
        $statusLabels = [
            'pending' => 'قيد الانتظار',
            'processing' => 'قيد التجهيز',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغي',
        ];

        return [
            'order_number' => $order->order_number,
            'order_status' => $statusLabels[$order->status] ?? $order->status,
            'order_total' => number_format($order->total, 2) . ' ج.م',
            'order_subtotal' => number_format($order->subtotal, 2) . ' ج.م',
            'order_shipping' => number_format($order->shipping_cost, 2) . ' ج.م',
            'order_discount' => number_format($order->discount_amount, 2) . ' ج.م',
            'order_date' => $order->created_at->format('Y/m/d'),
            'order_items_count' => (string) $order->items->count(),
            'user_name' => $order->user?->name ?? $order->guest_name,
            'user_email' => $order->user?->email ?? $order->guest_email,
            'user_phone' => $order->user?->phone ?? $order->guest_phone,
            'shipping_name' => $order->shippingAddress?->full_name ?? $order->guest_name,
            'shipping_address' => $order->shippingAddress?->address ?? $order->guest_address,
            'shipping_city' => $order->shippingAddress?->city ?? $order->guest_city,
            'shipping_governorate' => $order->shippingAddress?->governorate ?? $order->guest_governorate,
            'shipping_phone' => $order->shippingAddress?->phone ?? $order->guest_phone,
            'track_url' => config('app.url') . '/track/' . $order->order_number,
        ];
    }
}
