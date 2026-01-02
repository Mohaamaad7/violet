<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class InfluencerTranslationsSeeder extends Seeder
{
    /**
     * Seed influencer-related translations
     */
    public function run(): void
    {
        $translations = [
            // === Sections ===
            'admin.influencers.sections.credentials' => [
                'ar' => 'بيانات الدخول',
                'en' => 'Login Credentials',
            ],
            'admin.influencers.sections.credentials_desc' => [
                'ar' => 'بيانات المستخدم للدخول على بوابة الشركاء',
                'en' => 'User credentials for Partners Portal access',
            ],
            'admin.influencers.sections.profile' => [
                'ar' => 'ملف المؤثر',
                'en' => 'Influencer Profile',
            ],
            'admin.influencers.sections.profile_desc' => [
                'ar' => 'معلومات حسابات السوشيال ميديا',
                'en' => 'Social media accounts information',
            ],
            'admin.influencers.sections.financial' => [
                'ar' => 'الاتفاق المالي',
                'en' => 'Financial Agreement',
            ],
            'admin.influencers.sections.financial_desc' => [
                'ar' => 'نسبة العمولة وكود الخصم',
                'en' => 'Commission rate and discount code',
            ],
            'admin.influencers.sections.statistics' => [
                'ar' => 'الإحصائيات',
                'en' => 'Statistics',
            ],

            // === Fields ===
            'admin.influencers.fields.email' => [
                'ar' => 'البريد الإلكتروني',
                'en' => 'Email',
            ],
            'admin.influencers.fields.phone' => [
                'ar' => 'رقم الهاتف',
                'en' => 'Phone Number',
            ],
            'admin.influencers.fields.send_invitation' => [
                'ar' => 'إرسال دعوة بالبريد',
                'en' => 'Send Email Invitation',
            ],
            'admin.influencers.fields.send_invitation_help' => [
                'ar' => 'سيتم إنشاء كلمة مرور عشوائية وإرسالها للمؤثر',
                'en' => 'A random password will be generated and sent to the influencer',
            ],
            'admin.influencers.fields.primary_platform' => [
                'ar' => 'المنصة الرئيسية',
                'en' => 'Primary Platform',
            ],
            'admin.influencers.fields.handle' => [
                'ar' => 'اسم الحساب',
                'en' => 'Handle/Username',
            ],
            'admin.influencers.fields.commission_type' => [
                'ar' => 'نوع العمولة',
                'en' => 'Commission Type',
            ],
            'admin.influencers.fields.commission_value' => [
                'ar' => 'قيمة العمولة',
                'en' => 'Commission Value',
            ],
            'admin.influencers.fields.coupon_code' => [
                'ar' => 'كود الخصم',
                'en' => 'Discount Code',
            ],
            'admin.influencers.fields.coupon_code_help' => [
                'ar' => 'أدخل كود فريد مثل: AHMED2026',
                'en' => 'Enter a unique code like: AHMED2026',
            ],
            'admin.influencers.fields.generate_code' => [
                'ar' => 'توليد كود',
                'en' => 'Generate Code',
            ],
            'admin.influencers.fields.discount_type' => [
                'ar' => 'نوع الخصم للعملاء',
                'en' => 'Customer Discount Type',
            ],
            'admin.influencers.fields.discount_value' => [
                'ar' => 'قيمة الخصم',
                'en' => 'Discount Value',
            ],
            'admin.influencers.fields.total_earned' => [
                'ar' => 'إجمالي المكتسب',
                'en' => 'Total Earned',
            ],
            'admin.influencers.fields.total_paid' => [
                'ar' => 'إجمالي المدفوع',
                'en' => 'Total Paid',
            ],

            // === Commission Types ===
            'admin.influencers.commission_types.percentage' => [
                'ar' => 'نسبة مئوية',
                'en' => 'Percentage',
            ],
            'admin.influencers.commission_types.fixed' => [
                'ar' => 'مبلغ ثابت',
                'en' => 'Fixed Amount',
            ],

            // === Discount Types ===
            'admin.influencers.discount_types.percentage' => [
                'ar' => 'نسبة مئوية',
                'en' => 'Percentage',
            ],
            'admin.influencers.discount_types.fixed' => [
                'ar' => 'مبلغ ثابت',
                'en' => 'Fixed Amount',
            ],

            // === Notifications ===
            'admin.influencers.notifications.created' => [
                'ar' => 'تم إنشاء المؤثر بنجاح',
                'en' => 'Influencer created successfully',
            ],

            // === Applications ===
            'admin.applications.fields.send_welcome_email' => [
                'ar' => 'إرسال بريد ترحيبي',
                'en' => 'Send Welcome Email',
            ],
            'admin.applications.fields.send_welcome_email_help' => [
                'ar' => 'سيتم إرسال بريد يحتوي على بيانات الدخول وكود الخصم',
                'en' => 'An email with login details and discount code will be sent',
            ],

            // === Currency ===
            'admin.currency.egp_short' => [
                'ar' => 'ج.م',
                'en' => 'EGP',
            ],
        ];

        foreach ($translations as $key => $values) {
            foreach ($values as $locale => $value) {
                Translation::updateOrCreate(
                    ['key' => $key, 'locale' => $locale],
                    [
                        'value' => $value,
                        'group' => 'admin',
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('✅ Influencer translations seeded successfully!');
    }
}
