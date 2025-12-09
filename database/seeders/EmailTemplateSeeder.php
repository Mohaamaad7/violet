<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Path to pre-compiled HTML templates.
     */
    private string $templatesPath;

    public function __construct()
    {
        $this->templatesPath = resource_path('views/emails/templates');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Order Confirmation
            [
                'name' => 'تأكيد الطلب',
                'slug' => 'order-confirmation',
                'type' => 'customer',
                'category' => 'order',
                'description' => 'يُرسل للعميل عند إنشاء طلب جديد',
                'subject_ar' => 'تم استلام طلبك #{{ order_number }}',
                'subject_en' => 'Your Order #{{ order_number }} Confirmed',
                'content_html' => $this->loadTemplate('order-confirmation.html'),
                'available_variables' => [
                    'order_number', 'order_total', 'order_subtotal', 'order_shipping',
                    'order_discount', 'order_date', 'order_items_count', 'order_status',
                    'user_name', 'user_email', 'user_phone',
                    'product_name', 'product_price',
                    'shipping_name', 'shipping_address', 'shipping_city', 'shipping_governorate',
                    'track_url', 'app_name', 'app_url', 'support_email', 'current_year',
                ],
                'is_active' => true,
            ],

            // Order Status Update
            [
                'name' => 'تحديث حالة الطلب',
                'slug' => 'order-status-update',
                'type' => 'customer',
                'category' => 'order',
                'description' => 'يُرسل للعميل عند تغيير حالة الطلب',
                'subject_ar' => 'تحديث على طلبك #{{ order_number }} - {{ order_status }}',
                'subject_en' => 'Order #{{ order_number }} Update - {{ order_status }}',
                'content_html' => $this->loadTemplate('order-status-update.html'),
                'available_variables' => [
                    'order_number', 'order_status', 'order_total', 'order_date',
                    'user_name', 'product_name',
                    'track_url', 'app_name', 'app_url', 'support_email', 'current_year',
                ],
                'is_active' => true,
            ],

            // Welcome Email
            [
                'name' => 'رسالة ترحيب',
                'slug' => 'welcome',
                'type' => 'customer',
                'category' => 'auth',
                'description' => 'يُرسل للعميل بعد التسجيل',
                'subject_ar' => 'مرحباً بك في {{ app_name }}!',
                'subject_en' => 'Welcome to {{ app_name }}!',
                'content_html' => $this->loadTemplate('welcome.html'),
                'available_variables' => [
                    'user_name', 'user_email', 'app_name', 'app_url', 'support_email', 'current_year',
                ],
                'is_active' => true,
            ],

            // Password Reset
            [
                'name' => 'استعادة كلمة المرور',
                'slug' => 'password-reset',
                'type' => 'customer',
                'category' => 'auth',
                'description' => 'يُرسل عند طلب استعادة كلمة المرور',
                'subject_ar' => 'طلب استعادة كلمة المرور - {{ app_name }}',
                'subject_en' => 'Password Reset Request - {{ app_name }}',
                'content_html' => $this->loadTemplate('password-reset.html'),
                'available_variables' => [
                    'user_name', 'reset_url', 'app_name', 'app_url', 'support_email', 'current_year',
                ],
                'is_active' => true,
            ],

            // Admin: New Order Notification
            [
                'name' => 'إشعار طلب جديد (للإدارة)',
                'slug' => 'admin-new-order',
                'type' => 'admin',
                'category' => 'order',
                'description' => 'يُرسل للإدارة عند وجود طلب جديد',
                'subject_ar' => '🛒 طلب جديد #{{ order_number }} - {{ order_total }}',
                'subject_en' => '🛒 New Order #{{ order_number }} - {{ order_total }}',
                'content_html' => $this->loadTemplate('admin-new-order.html'),
                'available_variables' => [
                    'order_number', 'order_total', 'order_date', 'order_items_count',
                    'user_name', 'user_email', 'user_phone',
                    'product_name',
                    'shipping_address', 'shipping_city', 'shipping_governorate',
                    'app_name', 'app_url', 'current_year',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }

        $this->command->info('✓ Email templates seeded successfully! (Pre-compiled HTML)');
    }

    /**
     * Load pre-compiled HTML template from file.
     */
    private function loadTemplate(string $filename): string
    {
        $path = $this->templatesPath . '/' . $filename;

        if (!file_exists($path)) {
            throw new \RuntimeException("Email template not found: {$path}");
        }

        return file_get_contents($path);
    }
}
