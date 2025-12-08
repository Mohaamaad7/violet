<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\Mjml\Mjml;

class EmailTemplateService
{
    /**
     * Render MJML template to HTML with variable replacement.
     */
    public function render(
        EmailTemplate $template,
        array $variables = [],
        string $locale = 'ar'
    ): string {
        // Get MJML content
        $mjml = $template->content_mjml;

        // Add global variables
        $variables = array_merge($this->getGlobalVariables($template), $variables);

        // Replace variables in MJML
        $mjml = $this->replaceVariables($mjml, $variables);

        // Convert MJML to HTML
        return $this->convertToHtml($mjml);
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

        return $this->replaceVariables($subject, $variables);
    }

    /**
     * Convert MJML to HTML.
     */
    public function convertToHtml(string $mjml): string
    {
        try {
            $result = Mjml::new()->convert($mjml);

            if ($result->hasErrors()) {
                foreach ($result->errors() as $error) {
                    Log::warning('MJML conversion warning', [
                        'line' => $error->line(),
                        'message' => $error->message(),
                        'tag' => $error->tagName(),
                    ]);
                }
            }

            return $result->html();
        } catch (\Exception $e) {
            Log::error('MJML conversion failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Validate MJML syntax.
     */
    public function validateMjml(string $mjml): array
    {
        try {
            $canConvert = Mjml::new()->canConvert($mjml);
            $result = Mjml::new()->convert($mjml);

            return [
                'valid' => $canConvert,
                'has_errors' => $result->hasErrors(),
                'errors' => array_map(fn($e) => [
                    'line' => $e->line(),
                    'message' => $e->message(),
                    'tag' => $e->tagName(),
                ], $result->errors()),
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'has_errors' => true,
                'errors' => [['message' => $e->getMessage()]],
            ];
        }
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
     * Replace variables in content.
     */
    protected function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            // Support both {{ variable }} and {{variable}} syntax
            $content = str_replace(
                ['{{ ' . $key . ' }}', '{{' . $key . '}}', '{{ ' . $key . '}}', '{{' . $key . ' }}'],
                $value,
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
            'track_url' => config('app.url') . '/track/VIO-2024-001234',
            
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
