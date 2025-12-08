<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
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
                'content_mjml' => $this->getOrderConfirmationTemplate(),
                'available_variables' => [
                    'order_number', 'order_total', 'order_subtotal', 'order_shipping',
                    'order_discount', 'order_date', 'order_items_count', 'order_status',
                    'user_name', 'user_email', 'user_phone',
                    'shipping_name', 'shipping_address', 'shipping_city', 'shipping_governorate',
                    'track_url', 'app_name', 'app_url', 'support_email',
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
                'content_mjml' => $this->getOrderStatusUpdateTemplate(),
                'available_variables' => [
                    'order_number', 'order_status', 'order_total', 'order_date',
                    'user_name', 'track_url', 'app_name', 'app_url', 'support_email',
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
                'content_mjml' => $this->getWelcomeTemplate(),
                'available_variables' => [
                    'user_name', 'user_email', 'app_name', 'app_url', 'support_email',
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
                'content_mjml' => $this->getPasswordResetTemplate(),
                'available_variables' => [
                    'user_name', 'reset_url', 'app_name', 'app_url', 'support_email',
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
                'content_mjml' => $this->getAdminNewOrderTemplate(),
                'available_variables' => [
                    'order_number', 'order_total', 'order_date', 'order_items_count',
                    'user_name', 'user_email', 'user_phone',
                    'shipping_address', 'shipping_city', 'shipping_governorate',
                    'app_name', 'app_url',
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

        $this->command->info('Email templates seeded successfully!');
    }

    /**
     * Order Confirmation MJML Template
     */
    private function getOrderConfirmationTemplate(): string
    {
        return <<<'MJML'
<mjml>
  <mj-head>
    <mj-title>تأكيد الطلب</mj-title>
    <mj-attributes>
      <mj-all font-family="Tahoma, Arial, sans-serif" />
      <mj-text align="right" />
      <mj-section direction="rtl" />
    </mj-attributes>
    <mj-style>
      .order-details td { padding: 8px 12px; border-bottom: 1px solid #eee; direction: rtl; text-align: right; }
      * { direction: rtl; }
    </mj-style>
  </mj-head>
  <mj-body background-color="#f4f4f4" css-class="rtl">
    <!-- Header -->
    <mj-section background-color="#4F46E5" padding="20px">
      <mj-column>
        <mj-text align="center" color="#ffffff" font-size="24px" font-weight="bold">
          {{ app_name }}
        </mj-text>
      </mj-column>
    </mj-section>

    <!-- Main Content -->
    <mj-section background-color="#ffffff" padding="30px">
      <mj-column>
        <mj-text font-size="22px" color="#333" font-weight="bold">
          شكراً لطلبك، {{ user_name }}! 🎉
        </mj-text>
        <mj-text font-size="16px" color="#666" padding-top="10px">
          تم استلام طلبك بنجاح وسيتم معالجته قريباً.
        </mj-text>
        <mj-divider border-color="#eee" padding="20px 0" />
        
        <!-- Order Info -->
        <mj-text font-size="18px" color="#333" font-weight="bold">
          تفاصيل الطلب
        </mj-text>
        <mj-table>
          <tr class="order-details">
            <td style="font-weight: bold;">رقم الطلب:</td>
            <td>{{ order_number }}</td>
          </tr>
          <tr class="order-details">
            <td style="font-weight: bold;">تاريخ الطلب:</td>
            <td>{{ order_date }}</td>
          </tr>
          <tr class="order-details">
            <td style="font-weight: bold;">عدد المنتجات:</td>
            <td>{{ order_items_count }}</td>
          </tr>
          <tr class="order-details">
            <td style="font-weight: bold;">المجموع الفرعي:</td>
            <td>{{ order_subtotal }}</td>
          </tr>
          <tr class="order-details">
            <td style="font-weight: bold;">الشحن:</td>
            <td>{{ order_shipping }}</td>
          </tr>
          <tr class="order-details">
            <td style="font-weight: bold;">الخصم:</td>
            <td>{{ order_discount }}</td>
          </tr>
          <tr class="order-details" style="background-color: #f9f9f9;">
            <td style="font-weight: bold; font-size: 18px;">الإجمالي:</td>
            <td style="font-size: 18px; color: #4F46E5; font-weight: bold;">{{ order_total }}</td>
          </tr>
        </mj-table>

        <mj-divider border-color="#eee" padding="20px 0" />

        <!-- Shipping Address -->
        <mj-text font-size="18px" color="#333" font-weight="bold">
          عنوان الشحن
        </mj-text>
        <mj-text font-size="14px" color="#666">
          {{ shipping_name }}<br/>
          {{ shipping_address }}<br/>
          {{ shipping_city }}، {{ shipping_governorate }}
        </mj-text>

        <!-- CTA Button -->
        <mj-button background-color="#4F46E5" href="{{ track_url }}" padding-top="30px">
          تتبع طلبك
        </mj-button>
      </mj-column>
    </mj-section>

    <!-- Footer -->
    <mj-section background-color="#f4f4f4" padding="20px">
      <mj-column>
        <mj-text align="center" font-size="12px" color="#999">
          إذا كان لديك أي استفسار، تواصل معنا على {{ support_email }}
        </mj-text>
        <mj-text align="center" font-size="12px" color="#999">
          © {{ current_year }} {{ app_name }}. جميع الحقوق محفوظة.
        </mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;
    }

    /**
     * Order Status Update MJML Template
     */
    private function getOrderStatusUpdateTemplate(): string
    {
        return <<<'MJML'
<mjml>
  <mj-head>
    <mj-title>تحديث حالة الطلب</mj-title>
    <mj-attributes>
      <mj-all font-family="Tahoma, Arial, sans-serif" />
      <mj-text align="right" />
      <mj-section direction="rtl" />
    </mj-attributes>
    <mj-style>
      * { direction: rtl; }
    </mj-style>
  </mj-head>
  <mj-body background-color="#f4f4f4">
    <!-- Header -->
    <mj-section background-color="#4F46E5" padding="20px">
      <mj-column>
        <mj-text align="center" color="#ffffff" font-size="24px" font-weight="bold">
          {{ app_name }}
        </mj-text>
      </mj-column>
    </mj-section>

    <!-- Main Content -->
    <mj-section background-color="#ffffff" padding="30px">
      <mj-column>
        <mj-text font-size="22px" color="#333" font-weight="bold">
          مرحباً {{ user_name }}
        </mj-text>
        <mj-text font-size="16px" color="#666" padding-top="10px">
          تم تحديث حالة طلبك رقم <strong>{{ order_number }}</strong>
        </mj-text>
        
        <!-- Status Badge -->
        <mj-text align="center" padding="30px 0">
          <span style="background-color: #F59E0B; color: #fff; padding: 12px 24px; border-radius: 25px; font-size: 18px; font-weight: bold;">
            {{ order_status }}
          </span>
        </mj-text>

        <mj-text font-size="14px" color="#666" align="center">
          تاريخ الطلب: {{ order_date }} | الإجمالي: {{ order_total }}
        </mj-text>

        <!-- CTA Button -->
        <mj-button background-color="#4F46E5" href="{{ track_url }}" padding-top="30px">
          تتبع طلبك
        </mj-button>
      </mj-column>
    </mj-section>

    <!-- Footer -->
    <mj-section background-color="#f4f4f4" padding="20px">
      <mj-column>
        <mj-text align="center" font-size="12px" color="#999">
          إذا كان لديك أي استفسار، تواصل معنا على {{ support_email }}
        </mj-text>
        <mj-text align="center" font-size="12px" color="#999">
          © {{ current_year }} {{ app_name }}. جميع الحقوق محفوظة.
        </mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;
    }

    /**
     * Welcome Email MJML Template
     */
    private function getWelcomeTemplate(): string
    {
        return <<<'MJML'
<mjml>
  <mj-head>
    <mj-title>مرحباً بك</mj-title>
    <mj-attributes>
      <mj-all font-family="Tahoma, Arial, sans-serif" />
      <mj-text align="right" />
      <mj-section direction="rtl" />
    </mj-attributes>
    <mj-style>
      * { direction: rtl; }
    </mj-style>
  </mj-head>
  <mj-body background-color="#f4f4f4">
    <!-- Header -->
    <mj-section background-color="#4F46E5" padding="20px">
      <mj-column>
        <mj-text align="center" color="#ffffff" font-size="24px" font-weight="bold">
          {{ app_name }}
        </mj-text>
      </mj-column>
    </mj-section>

    <!-- Main Content -->
    <mj-section background-color="#ffffff" padding="30px">
      <mj-column>
        <mj-text align="center" font-size="48px" padding-bottom="20px">
          🎉
        </mj-text>
        <mj-text font-size="24px" color="#333" font-weight="bold" align="center">
          مرحباً بك {{ user_name }}!
        </mj-text>
        <mj-text font-size="16px" color="#666" padding-top="20px" align="center">
          شكراً لانضمامك إلى {{ app_name }}. نحن سعداء بوجودك معنا!
        </mj-text>
        <mj-text font-size="14px" color="#666" padding-top="10px" align="center">
          يمكنك الآن استكشاف منتجاتنا والاستمتاع بتجربة تسوق فريدة.
        </mj-text>

        <!-- CTA Button -->
        <mj-button background-color="#4F46E5" href="{{ app_url }}" padding-top="30px">
          ابدأ التسوق
        </mj-button>
      </mj-column>
    </mj-section>

    <!-- Footer -->
    <mj-section background-color="#f4f4f4" padding="20px">
      <mj-column>
        <mj-text align="center" font-size="12px" color="#999">
          © {{ current_year }} {{ app_name }}. جميع الحقوق محفوظة.
        </mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;
    }

    /**
     * Password Reset MJML Template
     */
    private function getPasswordResetTemplate(): string
    {
        return <<<'MJML'
<mjml>
  <mj-head>
    <mj-title>استعادة كلمة المرور</mj-title>
    <mj-attributes>
      <mj-all font-family="Tahoma, Arial, sans-serif" />
      <mj-text align="right" />
      <mj-section direction="rtl" />
    </mj-attributes>
    <mj-style>
      * { direction: rtl; }
    </mj-style>
  </mj-head>
  <mj-body background-color="#f4f4f4">
    <!-- Header -->
    <mj-section background-color="#4F46E5" padding="20px">
      <mj-column>
        <mj-text align="center" color="#ffffff" font-size="24px" font-weight="bold">
          {{ app_name }}
        </mj-text>
      </mj-column>
    </mj-section>

    <!-- Main Content -->
    <mj-section background-color="#ffffff" padding="30px">
      <mj-column>
        <mj-text align="center" font-size="48px" padding-bottom="20px">
          🔐
        </mj-text>
        <mj-text font-size="22px" color="#333" font-weight="bold" align="center">
          استعادة كلمة المرور
        </mj-text>
        <mj-text font-size="16px" color="#666" padding-top="20px">
          مرحباً {{ user_name }}،
        </mj-text>
        <mj-text font-size="14px" color="#666" padding-top="10px">
          تلقينا طلباً لاستعادة كلمة المرور الخاصة بحسابك. اضغط على الزر أدناه لإعادة تعيين كلمة المرور.
        </mj-text>

        <!-- CTA Button -->
        <mj-button background-color="#4F46E5" href="{{ reset_url }}" padding="30px 0">
          إعادة تعيين كلمة المرور
        </mj-button>

        <mj-text font-size="12px" color="#999">
          ⚠️ هذا الرابط صالح لمدة 60 دقيقة فقط.
        </mj-text>
        <mj-text font-size="12px" color="#999" padding-top="10px">
          إذا لم تطلب استعادة كلمة المرور، يمكنك تجاهل هذا البريد.
        </mj-text>
      </mj-column>
    </mj-section>

    <!-- Footer -->
    <mj-section background-color="#f4f4f4" padding="20px">
      <mj-column>
        <mj-text align="center" font-size="12px" color="#999">
          © {{ current_year }} {{ app_name }}. جميع الحقوق محفوظة.
        </mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;
    }

    /**
     * Admin New Order Notification MJML Template
     */
    private function getAdminNewOrderTemplate(): string
    {
        return <<<'MJML'
<mjml>
  <mj-head>
    <mj-title>طلب جديد</mj-title>
    <mj-attributes>
      <mj-all font-family="Tahoma, Arial, sans-serif" />
      <mj-text align="right" />
      <mj-section direction="rtl" />
    </mj-attributes>
    <mj-style>
      .info-row td { padding: 8px 12px; border-bottom: 1px solid #eee; direction: rtl; text-align: right; }
      * { direction: rtl; }
    </mj-style>
  </mj-head>
  <mj-body background-color="#f4f4f4">
    <!-- Header -->
    <mj-section background-color="#1a1a2e" padding="20px">
      <mj-column>
        <mj-text align="center" color="#ffffff" font-size="24px" font-weight="bold">
          {{ app_name }} - لوحة التحكم
        </mj-text>
      </mj-column>
    </mj-section>

    <!-- Alert Banner -->
    <mj-section background-color="#22c55e" padding="15px">
      <mj-column>
        <mj-text align="center" color="#ffffff" font-size="18px" font-weight="bold">
          🛒 طلب جديد!
        </mj-text>
      </mj-column>
    </mj-section>

    <!-- Main Content -->
    <mj-section background-color="#ffffff" padding="30px">
      <mj-column>
        <mj-text font-size="20px" color="#333" font-weight="bold">
          تفاصيل الطلب
        </mj-text>
        <mj-table>
          <tr class="info-row">
            <td style="font-weight: bold; width: 40%;">رقم الطلب:</td>
            <td>{{ order_number }}</td>
          </tr>
          <tr class="info-row">
            <td style="font-weight: bold;">التاريخ:</td>
            <td>{{ order_date }}</td>
          </tr>
          <tr class="info-row">
            <td style="font-weight: bold;">الإجمالي:</td>
            <td style="color: #22c55e; font-weight: bold; font-size: 18px;">{{ order_total }}</td>
          </tr>
          <tr class="info-row">
            <td style="font-weight: bold;">عدد المنتجات:</td>
            <td>{{ order_items_count }}</td>
          </tr>
        </mj-table>

        <mj-divider border-color="#eee" padding="20px 0" />

        <mj-text font-size="18px" color="#333" font-weight="bold">
          بيانات العميل
        </mj-text>
        <mj-table>
          <tr class="info-row">
            <td style="font-weight: bold; width: 40%;">الاسم:</td>
            <td>{{ user_name }}</td>
          </tr>
          <tr class="info-row">
            <td style="font-weight: bold;">البريد:</td>
            <td>{{ user_email }}</td>
          </tr>
          <tr class="info-row">
            <td style="font-weight: bold;">الهاتف:</td>
            <td>{{ user_phone }}</td>
          </tr>
        </mj-table>

        <mj-divider border-color="#eee" padding="20px 0" />

        <mj-text font-size="18px" color="#333" font-weight="bold">
          عنوان الشحن
        </mj-text>
        <mj-text font-size="14px" color="#666">
          {{ shipping_address }}<br/>
          {{ shipping_city }}، {{ shipping_governorate }}
        </mj-text>

        <!-- CTA Button -->
        <mj-button background-color="#1a1a2e" href="{{ app_url }}/admin/orders" padding-top="30px">
          عرض الطلب في لوحة التحكم
        </mj-button>
      </mj-column>
    </mj-section>

    <!-- Footer -->
    <mj-section background-color="#f4f4f4" padding="20px">
      <mj-column>
        <mj-text align="center" font-size="12px" color="#999">
          هذه رسالة آلية من نظام {{ app_name }}
        </mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;
    }
}
