<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Model;

/**
 * Email Template Service
 * 
 * Renders pre-compiled HTML email templates with variable replacement.
 * No external dependencies (MJML API/npm) required - templates are pre-compiled.
 */
class EmailTemplateService
{
    /**
     * Render HTML template with variable replacement.
     */
    public function render(
        EmailTemplate $template,
        array $variables = [],
        string $locale = 'ar'
    ): string {
        // Get pre-compiled HTML content
        $html = $template->content_html;

        if (empty($html)) {
            throw new \RuntimeException(
                "Template '{$template->slug}' has no HTML content. Please run the seeder."
            );
        }

        // Add global variables
        $variables = array_merge($this->getGlobalVariables($template), $variables);

        // Replace variables in HTML
        return $this->replaceVariables($html, $variables);
    }

    /**
     * Get subject line with variable replacement.
     */
    public function getSubject(
        EmailTemplate $template,
        array $variables = [],
        string $locale = 'ar'
    ): string {
        $subject = $template->getSubject($locale);

        // Add global variables for subject too
        $variables = array_merge($this->getGlobalVariables($template), $variables);

        return $this->replaceVariables($subject, $variables);
    }

    /**
     * Preview template with sample data.
     */
    public function preview(EmailTemplate $template, string $locale = 'ar'): string
    {
        $sampleVariables = $this->getSampleVariables($template);

        return $this->render($template, $sampleVariables, $locale);
    }

    /**
     * Get sample data for template (used in live preview).
     * 
     * @param EmailTemplate $template
     * @return array
     */
    public function getSampleData(EmailTemplate $template): array
    {
        return array_merge(
            $this->getGlobalVariables($template),
            $this->getSampleVariables($template)
        );
    }

    /**
     * Replace variables in content.
     */
    protected function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            // Support both {{ variable }} and {{variable}} syntax
            $content = str_replace(
                ['{{ ' . $key . ' }}', '{{' . $key . '}}', '{{ ' . $key . '}}', '{{' . $key . ' }}'],
                (string) $value,
                $content
            );
        }

        return $content;
    }

    /**
     * Get global variables available to all templates.
     */
    protected function getGlobalVariables(EmailTemplate $template): array
    {
        return [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'primary_color' => $template->primary_color ?? '#4F46E5',
            'secondary_color' => $template->secondary_color ?? '#F59E0B',
            'logo_url' => $template->logo_path 
                ? asset('storage/' . $template->logo_path)
                : asset('images/logo.png'),
            'current_year' => date('Y'),
            'support_email' => config('mail.from.address'),
        ];
    }

    /**
     * Get sample variables for preview.
     */
    protected function getSampleVariables(EmailTemplate $template): array
    {
        $availableVars = $template->available_variables ?? [];
        $samples = [];

        foreach ($availableVars as $var) {
            $varName = is_array($var) ? ($var['name'] ?? $var) : $var;
            $samples[$varName] = $this->getSampleValueFor($varName);
        }

        return $samples;
    }

    /**
     * Get sample value for a variable name.
     */
    protected function getSampleValueFor(string $varName): string
    {
        $sampleValues = [
            // User
            'user_name' => 'أحمد محمد',
            'user_email' => 'ahmed@example.com',
            'user_phone' => '01012345678',
            
            // Order
            'order_number' => 'VIO-2024-001234',
            'order_total' => '1,250.00 ج.م',
            'order_subtotal' => '1,150.00 ج.م',
            'order_shipping' => '50.00 ج.م',
            'order_discount' => '50.00 ج.م',
            'order_status' => 'قيد التجهيز',
            'order_date' => date('Y/m/d'),
            'order_items_count' => '3',
            
            // Shipping
            'shipping_name' => 'أحمد محمد',
            'shipping_address' => 'شارع التحرير، وسط البلد',
            'shipping_city' => 'القاهرة',
            'shipping_governorate' => 'القاهرة',
            'shipping_phone' => '01012345678',
            
            // Product
            'product_name' => 'كريم العناية بالبشرة',
            'product_price' => '350.00 ج.م',
            
            // Links
            'action_url' => config('app.url') . '/orders/12345',
            'reset_url' => config('app.url') . '/reset-password/token123',
            'verify_url' => config('app.url') . '/verify-email/token123',
            'track_url' => config('app.url') . '/track-order',
            
            // Auth
            'verification_code' => '123456',
            'reset_code' => '654321',
        ];

        return $sampleValues[$varName] ?? '[' . $varName . ']';
    }

    /**
     * Create email log entry.
     */
    public function createLog(
        EmailTemplate $template,
        string $recipientEmail,
        string $subject,
        ?string $recipientName = null,
        ?Model $related = null,
        string $locale = 'ar',
        array $metadata = []
    ): EmailLog {
        return EmailLog::create([
            'email_template_id' => $template->id,
            'related_type' => $related ? get_class($related) : null,
            'related_id' => $related?->id,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'subject' => $subject,
            'locale' => $locale,
            'status' => 'pending',
            'metadata' => $metadata,
        ]);
    }
}
