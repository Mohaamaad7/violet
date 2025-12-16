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
    ) {
    }

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
        $recipientEmail = $order->customer?->email ?? $order->guest_email;
        $recipientName = $order->customer?->name ?? $order->guest_name;

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
            locale: $locale ?? $order->customer?->locale ?? 'ar'
        );
    }

    /**
     * Send order status update email.
     */
    public function sendOrderStatusUpdate(
        \App\Models\Order $order,
        ?string $locale = null
    ): ?EmailLog {
        $recipientEmail = $order->customer?->email ?? $order->guest_email;
        $recipientName = $order->customer?->name ?? $order->guest_name;

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
            locale: $locale ?? $order->customer?->locale ?? 'ar'
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
     * Send admin notification for new order.
     */
    public function sendAdminNewOrderNotification(
        \App\Models\Order $order
    ): ?EmailLog {
        // Get admin email from config or use a default
        $adminEmail = config('mail.admin_email', config('mail.from.address'));

        if (!$adminEmail) {
            Log::warning('No admin email configured for order notifications');
            return null;
        }

        $variables = $this->getOrderVariables($order);

        return $this->send(
            templateSlug: 'admin-new-order',
            recipientEmail: $adminEmail,
            variables: $variables,
            recipientName: 'Admin',
            related: $order,
            locale: 'ar'
        );
    }

    /**
     * Get order variables for email templates.
     */
    protected function getOrderVariables(\App\Models\Order $order): array
    {
        return [
            'order_number' => $order->order_number,
            'order_status' => $order->status?->label() ?? 'غير محدد',
            'order_total' => number_format($order->total, 2) . ' ج.م',
            'order_subtotal' => number_format($order->subtotal, 2) . ' ج.م',
            'order_shipping' => number_format($order->shipping_cost, 2) . ' ج.م',
            'order_discount' => number_format($order->discount_amount, 2) . ' ج.م',
            'order_date' => $order->created_at->format('Y/m/d'),
            'order_items_count' => (string) $order->items->count(),
            'user_name' => $order->customer?->name ?? $order->guest_name,
            'user_email' => $order->customer?->email ?? $order->guest_email,
            'user_phone' => $order->customer?->phone ?? $order->guest_phone,
            'shipping_name' => $order->shippingAddress?->full_name ?? $order->guest_name,
            'shipping_address' => $order->shippingAddress?->address ?? $order->guest_address,
            'shipping_city' => $order->shippingAddress?->city ?? $order->guest_city,
            'shipping_governorate' => $order->shippingAddress?->governorate ?? $order->guest_governorate,
            'shipping_phone' => $order->shippingAddress?->phone ?? $order->guest_phone,
            'track_url' => config('app.url') . '/track/' . $order->order_number,
        ];
    }

    /**
     * Send return request received email (to customer).
     */
    public function sendReturnRequestReceived(
        \App\Models\OrderReturn $return,
        ?string $locale = null
    ): ?EmailLog {
        $order = $return->order;
        $recipientEmail = $order->customer?->email ?? $order->guest_email;
        $recipientName = $order->customer?->name ?? $order->guest_name;

        if (!$recipientEmail) {
            Log::warning('No email address for return request', ['return_id' => $return->id]);
            return null;
        }

        $variables = $this->getReturnVariables($return);

        return $this->send(
            templateSlug: 'return-request-received',
            recipientEmail: $recipientEmail,
            variables: $variables,
            recipientName: $recipientName,
            related: $return,
            locale: $locale ?? $order->customer?->locale ?? 'ar'
        );
    }

    /**
     * Send return approved email (to customer).
     */
    public function sendReturnApproved(
        \App\Models\OrderReturn $return,
        ?string $locale = null
    ): ?EmailLog {
        $order = $return->order;
        $recipientEmail = $order->customer?->email ?? $order->guest_email;
        $recipientName = $order->customer?->name ?? $order->guest_name;

        if (!$recipientEmail) {
            Log::warning('No email address for return approval', ['return_id' => $return->id]);
            return null;
        }

        $variables = $this->getReturnVariables($return);

        return $this->send(
            templateSlug: 'return-approved',
            recipientEmail: $recipientEmail,
            variables: $variables,
            recipientName: $recipientName,
            related: $return,
            locale: $locale ?? $order->customer?->locale ?? 'ar'
        );
    }

    /**
     * Send return rejected email (to customer).
     */
    public function sendReturnRejected(
        \App\Models\OrderReturn $return,
        ?string $locale = null
    ): ?EmailLog {
        $order = $return->order;
        $recipientEmail = $order->customer?->email ?? $order->guest_email;
        $recipientName = $order->customer?->name ?? $order->guest_name;

        if (!$recipientEmail) {
            Log::warning('No email address for return rejection', ['return_id' => $return->id]);
            return null;
        }

        $variables = $this->getReturnVariables($return);

        return $this->send(
            templateSlug: 'return-rejected',
            recipientEmail: $recipientEmail,
            variables: $variables,
            recipientName: $recipientName,
            related: $return,
            locale: $locale ?? $order->customer?->locale ?? 'ar'
        );
    }

    /**
     * Send return completed email (to customer).
     */
    public function sendReturnCompleted(
        \App\Models\OrderReturn $return,
        ?string $locale = null
    ): ?EmailLog {
        $order = $return->order;
        $recipientEmail = $order->customer?->email ?? $order->guest_email;
        $recipientName = $order->customer?->name ?? $order->guest_name;

        if (!$recipientEmail) {
            Log::warning('No email address for return completion', ['return_id' => $return->id]);
            return null;
        }

        $variables = $this->getReturnVariables($return);

        return $this->send(
            templateSlug: 'return-completed',
            recipientEmail: $recipientEmail,
            variables: $variables,
            recipientName: $recipientName,
            related: $return,
            locale: $locale ?? $order->customer?->locale ?? 'ar'
        );
    }

    /**
     * Send admin notification for new return request.
     */
    public function sendAdminNewReturnNotification(
        \App\Models\OrderReturn $return
    ): ?EmailLog {
        // Get admin email from config or use a default
        $adminEmail = config('mail.admin_email', config('mail.from.address'));

        if (!$adminEmail) {
            Log::warning('No admin email configured for return notifications');
            return null;
        }

        $variables = $this->getReturnVariables($return);

        return $this->send(
            templateSlug: 'admin-new-return',
            recipientEmail: $adminEmail,
            variables: $variables,
            recipientName: 'Admin',
            related: $return,
            locale: 'ar'
        );
    }

    /**
     * Get return variables for email templates.
     */
    protected function getReturnVariables(\App\Models\OrderReturn $return): array
    {
        $order = $return->order;

        return [
            'return_number' => $return->return_number,
            'order_number' => $order->order_number,
            'return_type' => $return->type?->label() ?? 'غير محدد',
            'return_reason' => $return->reason ?? '',
            'customer_notes' => $return->customer_notes ?? '',
            'admin_notes' => $return->admin_notes ?? '',
            'rejection_reason' => $return->admin_notes ?? '',
            'items_count' => (string) $return->items->count(),
            'total_amount' => number_format($return->refund_amount ?? 0, 2) . ' ج.م',
            'refund_amount' => number_format($return->refund_amount ?? 0, 2) . ' ج.م',
            'refund_status' => $return->refund_status ?? 'pending',
            'refund_method' => 'نفس طريقة الدفع الأصلية',
            'approved_at' => $return->approved_at?->format('Y/m/d h:i A') ?? '',
            'rejected_at' => $return->rejected_at?->format('Y/m/d h:i A') ?? '',
            'completed_at' => $return->completed_at?->format('Y/m/d h:i A') ?? '',
            'next_steps' => 'سيتم التواصل معك لتحديد موعد استلام المنتجات.',
            'customer_name' => $order->customer?->name ?? $order->guest_name,
            'customer_email' => $order->customer?->email ?? $order->guest_email,
            'customer_phone' => $order->customer?->phone ?? $order->guest_phone,
            'user_name' => $order->customer?->name ?? $order->guest_name,
            'track_url' => config('app.url') . '/account/returns/' . $return->id,
            'admin_panel_url' => route('filament.admin.resources.order-returns.view', $return),
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'support_email' => config('mail.from.address'),
            'current_year' => date('Y'),
        ];
    }
}
