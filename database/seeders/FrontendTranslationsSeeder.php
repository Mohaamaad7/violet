<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class FrontendTranslationsSeeder extends Seeder
{
    /**
     * Seed frontend translations for store (header, footer, home, products, cart).
     *
     * @return void
     */
    public function run(): void
    {
        $translations = [
            // ============================================
            // HEADER TRANSLATIONS
            // ============================================

            // Top Bar
            ['key' => 'store.header.free_shipping', 'en' => 'Free shipping on orders over $50', 'ar' => 'شحن مجاني للطلبات فوق 50 دولار', 'group' => 'store'],
            ['key' => 'store.header.language', 'en' => 'EN', 'ar' => 'العربية', 'group' => 'store'],

            // Main Navigation
            ['key' => 'store.header.home', 'en' => 'Home', 'ar' => 'الرئيسية', 'group' => 'store'],
            ['key' => 'store.header.products', 'en' => 'Products', 'ar' => 'المنتجات', 'group' => 'store'],
            ['key' => 'store.header.categories', 'en' => 'Categories', 'ar' => 'الأقسام', 'group' => 'store'],
            ['key' => 'store.header.offers', 'en' => 'Offers', 'ar' => 'العروض', 'group' => 'store'],
            ['key' => 'store.header.about', 'en' => 'About Us', 'ar' => 'من نحن', 'group' => 'store'],
            ['key' => 'store.header.contact', 'en' => 'Contact Us', 'ar' => 'اتصل بنا', 'group' => 'store'],
            ['key' => 'store.header.account', 'en' => 'Account', 'ar' => 'حسابي', 'group' => 'store'],
            ['key' => 'store.header.my_account', 'en' => 'My Account', 'ar' => 'حسابي', 'group' => 'store'],

            // Search
            ['key' => 'store.header.search_placeholder', 'en' => 'Search for products...', 'ar' => 'ابحث عن المنتجات...', 'group' => 'store'],

            // Category Mega Menu
            ['key' => 'store.header.view_all', 'en' => 'View All', 'ar' => 'عرض الكل', 'group' => 'store'],
            ['key' => 'store.header.view_products', 'en' => 'View Products', 'ar' => 'عرض المنتجات', 'group' => 'store'],
            ['key' => 'store.header.view_all_products', 'en' => 'View All Products', 'ar' => 'عرض جميع المنتجات', 'group' => 'store'],

            // Cart & Actions
            ['key' => 'store.header.cart_title', 'en' => 'Open Cart', 'ar' => 'فتح السلة', 'group' => 'store'],

            // ============================================
            // FOOTER TRANSLATIONS
            // ============================================

            ['key' => 'store.footer.description', 'en' => 'Your premium destination for quality products. We bring you the best selection with unbeatable prices and excellent customer service.', 'ar' => 'وجهتك المثالية للمنتجات عالية الجودة. نقدم لك أفضل تشكيلة بأسعار لا تقبل المنافسة وخدمة عملاء ممتازة.', 'group' => 'store'],
            ['key' => 'store.footer.we_accept', 'en' => 'We Accept:', 'ar' => 'نقبل:', 'group' => 'store'],

            // Quick Links
            ['key' => 'store.footer.quick_links', 'en' => 'Quick Links', 'ar' => 'روابط سريعة', 'group' => 'store'],
            ['key' => 'store.footer.about_us', 'en' => 'About Us', 'ar' => 'من نحن', 'group' => 'store'],
            ['key' => 'store.footer.shop_now', 'en' => 'Shop Now', 'ar' => 'تسوق الآن', 'group' => 'store'],
            ['key' => 'store.footer.special_offers', 'en' => 'Special Offers', 'ar' => 'العروض الخاصة', 'group' => 'store'],
            ['key' => 'store.footer.contact_us', 'en' => 'Contact Us', 'ar' => 'اتصل بنا', 'group' => 'store'],
            ['key' => 'store.footer.blog', 'en' => 'Blog', 'ar' => 'المدونة', 'group' => 'store'],

            // Customer Service
            ['key' => 'store.footer.customer_service', 'en' => 'Customer Service', 'ar' => 'خدمة العملاء', 'group' => 'store'],
            ['key' => 'store.footer.help_center', 'en' => 'Help Center', 'ar' => 'مركز المساعدة', 'group' => 'store'],
            ['key' => 'store.footer.shipping_info', 'en' => 'Shipping Info', 'ar' => 'معلومات الشحن', 'group' => 'store'],
            ['key' => 'store.footer.returns_refunds', 'en' => 'Returns & Refunds', 'ar' => 'الإرجاع والاسترداد', 'group' => 'store'],
            ['key' => 'store.footer.track_order', 'en' => 'Track Order', 'ar' => 'تتبع الطلب', 'group' => 'store'],
            ['key' => 'store.footer.faqs', 'en' => 'FAQs', 'ar' => 'الأسئلة الشائعة', 'group' => 'store'],

            // Newsletter
            ['key' => 'store.footer.stay_connected', 'en' => 'Stay Connected', 'ar' => 'ابق على تواصل', 'group' => 'store'],
            ['key' => 'store.footer.subscribe', 'en' => 'Subscribe to Newsletter', 'ar' => 'اشترك في النشرة الإخبارية', 'group' => 'store'],
            ['key' => 'store.footer.newsletter_desc', 'en' => 'Get latest offers and updates', 'ar' => 'احصل على آخر العروض والتحديثات', 'group' => 'store'],
            ['key' => 'store.footer.your_email', 'en' => 'Your email', 'ar' => 'بريدك الإلكتروني', 'group' => 'store'],
            ['key' => 'store.footer.subscribe_button', 'en' => 'Subscribe', 'ar' => 'اشترك', 'group' => 'store'],

            // Social & Legal
            ['key' => 'store.footer.follow_us', 'en' => 'Follow Us:', 'ar' => 'تابعنا:', 'group' => 'store'],
            ['key' => 'store.footer.copyright', 'en' => 'All rights reserved.', 'ar' => 'جميع الحقوق محفوظة.', 'group' => 'store'],
            ['key' => 'store.footer.privacy_policy', 'en' => 'Privacy Policy', 'ar' => 'سياسة الخصوصية', 'group' => 'store'],
            ['key' => 'store.footer.terms', 'en' => 'Terms & Conditions', 'ar' => 'الشروط والأحكام', 'group' => 'store'],
            ['key' => 'store.footer.cookie_policy', 'en' => 'Cookie Policy', 'ar' => 'سياسة ملفات تعريف الارتباط', 'group' => 'store'],

            // ============================================
            // HOME PAGE TRANSLATIONS
            // ============================================

            // Features Section
            ['key' => 'store.home.free_shipping', 'en' => 'Free Shipping', 'ar' => 'شحن مجاني', 'group' => 'store'],
            ['key' => 'store.home.free_shipping_desc', 'en' => 'On orders over $50', 'ar' => 'للطلبات فوق 50 دولار', 'group' => 'store'],
            ['key' => 'store.home.secure_payment', 'en' => 'Secure Payment', 'ar' => 'دفع آمن', 'group' => 'store'],
            ['key' => 'store.home.secure_payment_desc', 'en' => '100% secure transactions', 'ar' => 'معاملات آمنة 100%', 'group' => 'store'],
            ['key' => 'store.home.easy_returns', 'en' => 'Easy Returns', 'ar' => 'إرجاع سهل', 'group' => 'store'],
            ['key' => 'store.home.easy_returns_desc', 'en' => '30-day return policy', 'ar' => 'سياسة إرجاع لمدة 30 يوم', 'group' => 'store'],

            // Newsletter
            ['key' => 'store.home.newsletter_title', 'en' => 'Subscribe to Our Newsletter', 'ar' => 'اشترك في نشرتنا الإخبارية', 'group' => 'store'],
            ['key' => 'store.home.newsletter_desc', 'en' => 'Get exclusive offers, updates, and special deals delivered to your inbox', 'ar' => 'احصل على عروض حصرية وتحديثات وصفقات خاصة في بريدك', 'group' => 'store'],
            ['key' => 'store.home.enter_email', 'en' => 'Enter your email', 'ar' => 'أدخل بريدك الإلكتروني', 'group' => 'store'],

            // ============================================
            // PRODUCT LIST TRANSLATIONS
            // ============================================

            ['key' => 'store.product.filters', 'en' => 'Filters', 'ar' => 'الفلاتر', 'group' => 'store'],
            ['key' => 'store.product.clear_all', 'en' => 'Clear All', 'ar' => 'مسح الكل', 'group' => 'store'],
            ['key' => 'store.product.categories', 'en' => 'Categories', 'ar' => 'الأقسام', 'group' => 'store'],
            ['key' => 'store.product.price', 'en' => 'Price', 'ar' => 'السعر', 'group' => 'store'],
            ['key' => 'store.product.rating', 'en' => 'Rating', 'ar' => 'التقييم', 'group' => 'store'],
            ['key' => 'store.product.sort_by', 'en' => 'Sort By', 'ar' => 'ترتيب حسب', 'group' => 'store'],
            ['key' => 'store.product.latest', 'en' => 'Latest', 'ar' => 'الأحدث', 'group' => 'store'],
            ['key' => 'store.product.price_low_high', 'en' => 'Price: Low to High', 'ar' => 'السعر: من الأقل للأعلى', 'group' => 'store'],
            ['key' => 'store.product.price_high_low', 'en' => 'Price: High to Low', 'ar' => 'السعر: من الأعلى للأقل', 'group' => 'store'],
            ['key' => 'store.product.name_asc', 'en' => 'Name: A-Z', 'ar' => 'الاسم: أ-ي', 'group' => 'store'],
            ['key' => 'store.product.name_desc', 'en' => 'Name: Z-A', 'ar' => 'الاسم: ي-أ', 'group' => 'store'],
            ['key' => 'store.product.showing_results', 'en' => 'Showing', 'ar' => 'عرض', 'group' => 'store'],
            ['key' => 'store.product.of', 'en' => 'of', 'ar' => 'من', 'group' => 'store'],
            ['key' => 'store.product.products', 'en' => 'products', 'ar' => 'منتجات', 'group' => 'store'],
            ['key' => 'store.product.no_products', 'en' => 'No products found', 'ar' => 'لا توجد منتجات', 'group' => 'store'],
            ['key' => 'store.product.try_different_filters', 'en' => 'Try adjusting your filters or search criteria', 'ar' => 'حاول تعديل الفلاتر أو معايير البحث', 'group' => 'store'],
            ['key' => 'store.product.add_to_cart', 'en' => 'Add to Cart', 'ar' => 'أضف للسلة', 'group' => 'store'],
            ['key' => 'store.product.out_of_stock', 'en' => 'Out of Stock', 'ar' => 'غير متوفر', 'group' => 'store'],
            ['key' => 'store.product.sale', 'en' => 'Sale', 'ar' => 'تخفيض', 'group' => 'store'],
            ['key' => 'store.product.new', 'en' => 'New', 'ar' => 'جديد', 'group' => 'store'],
            ['key' => 'store.product.view_details', 'en' => 'View Details', 'ar' => 'عرض التفاصيل', 'group' => 'store'],

            // ============================================
            // PRODUCT DETAILS TRANSLATIONS
            // ============================================

            ['key' => 'store.product_details.home', 'en' => 'Home', 'ar' => 'الرئيسية', 'group' => 'store'],
            ['key' => 'store.product_details.quantity', 'en' => 'Quantity', 'ar' => 'الكمية', 'group' => 'store'],
            ['key' => 'store.product_details.description', 'en' => 'Description', 'ar' => 'الوصف', 'group' => 'store'],
            ['key' => 'store.product_details.specifications', 'en' => 'Specifications', 'ar' => 'المواصفات', 'group' => 'store'],
            ['key' => 'store.product_details.reviews', 'en' => 'Reviews', 'ar' => 'التقييمات', 'group' => 'store'],
            ['key' => 'store.product_details.select_variant', 'en' => 'Select Option', 'ar' => 'اختر خيار', 'group' => 'store'],
            ['key' => 'store.product_details.in_stock', 'en' => 'In Stock', 'ar' => 'متوفر', 'group' => 'store'],
            ['key' => 'store.product_details.category', 'en' => 'Category', 'ar' => 'القسم', 'group' => 'store'],
            ['key' => 'store.product_details.sku', 'en' => 'SKU', 'ar' => 'رقم المنتج', 'group' => 'store'],
            ['key' => 'store.product_details.share', 'en' => 'Share', 'ar' => 'شارك', 'group' => 'store'],

            // ============================================
            // CART TRANSLATIONS
            // ============================================

            ['key' => 'store.cart.shopping_cart', 'en' => 'Shopping Cart', 'ar' => 'سلة التسوق', 'group' => 'store'],
            ['key' => 'store.cart.product', 'en' => 'product', 'ar' => 'منتج', 'group' => 'store'],
            ['key' => 'store.cart.products', 'en' => 'products', 'ar' => 'منتجات', 'group' => 'store'],
            ['key' => 'store.cart.clear_cart', 'en' => 'Clear Cart', 'ar' => 'تفريغ السلة', 'group' => 'store'],
            ['key' => 'store.cart.clear_cart_confirm', 'en' => 'Are you sure you want to clear the cart?', 'ar' => 'هل أنت متأكد من تفريغ السلة؟', 'group' => 'store'],
            ['key' => 'store.cart.option', 'en' => 'Option', 'ar' => 'الخيار', 'group' => 'store'],
            ['key' => 'store.cart.quantity', 'en' => 'Quantity', 'ar' => 'الكمية', 'group' => 'store'],
            ['key' => 'store.cart.price', 'en' => 'Price', 'ar' => 'السعر', 'group' => 'store'],
            ['key' => 'store.cart.remove', 'en' => 'Remove from Cart', 'ar' => 'إزالة من السلة', 'group' => 'store'],
            ['key' => 'store.cart.continue_shopping', 'en' => 'Continue Shopping', 'ar' => 'متابعة التسوق', 'group' => 'store'],
            ['key' => 'store.cart.order_summary', 'en' => 'Order Summary', 'ar' => 'ملخص الطلب', 'group' => 'store'],
            ['key' => 'store.cart.subtotal', 'en' => 'Subtotal', 'ar' => 'المجموع الفرعي', 'group' => 'store'],
            ['key' => 'store.cart.shipping', 'en' => 'Shipping', 'ar' => 'الشحن', 'group' => 'store'],
            ['key' => 'store.cart.free', 'en' => 'Free', 'ar' => 'مجاني', 'group' => 'store'],
            ['key' => 'store.cart.tax', 'en' => 'Tax (VAT 15%)', 'ar' => 'ضريبة القيمة المضافة (15%)', 'group' => 'store'],
            ['key' => 'store.cart.total', 'en' => 'Total', 'ar' => 'الإجمالي', 'group' => 'store'],
            ['key' => 'store.cart.checkout', 'en' => 'Proceed to Checkout', 'ar' => 'إتمام الطلب', 'group' => 'store'],
            ['key' => 'store.cart.secure_payment', 'en' => 'Secure & Protected Payment', 'ar' => 'الدفع آمن ومحمي', 'group' => 'store'],
            ['key' => 'store.cart.empty', 'en' => 'Cart is Empty', 'ar' => 'السلة فارغة', 'group' => 'store'],
            ['key' => 'store.cart.empty_desc', 'en' => 'Your cart is currently empty. Explore our products and add what you like to your cart!', 'ar' => 'يبدو أن سلتك فارغة حالياً. استكشف منتجاتنا وأضف ما يعجبك إلى سلتك!', 'group' => 'store'],
            ['key' => 'store.cart.browse_products', 'en' => 'Browse Products', 'ar' => 'تصفح المنتجات', 'group' => 'store'],
            ['key' => 'store.cart.add_more_for_free_shipping', 'en' => 'Add :amount more for free shipping', 'ar' => 'أضف :amount للحصول على شحن مجاني', 'group' => 'store'],
            ['key' => 'store.cart.close', 'en' => 'Close', 'ar' => 'إغلاق', 'group' => 'store'],
            ['key' => 'store.cart.view_full_cart', 'en' => 'View Full Cart', 'ar' => 'عرض السلة الكاملة', 'group' => 'store'],
            ['key' => 'out_of_stock', 'en' => 'out of stock', 'ar' => 'غير متوفر', 'group' => 'store'],

            // Currency
            ['key' => 'store.currency.sar', 'en' => 'SAR', 'ar' => 'ر.س', 'group' => 'store'],

            // ============================================
            // ORDER SUCCESS PAGE TRANSLATIONS
            // ============================================

            // Guest CTA Section
            ['key' => 'messages.order_success.create_account_title', 'en' => 'Track Your Orders Anytime!', 'ar' => 'تتبع طلباتك في أي وقت!', 'group' => 'messages'],
            ['key' => 'messages.order_success.create_account_desc', 'en' => 'Create an account to easily track all your orders, save addresses, and enjoy a faster checkout next time!', 'ar' => 'أنشئ حسابًا لتتبع جميع طلباتك بسهولة، وحفظ العناوين، والاستمتاع بإتمام أسرع للطلبات في المرة القادمة!', 'group' => 'messages'],
            ['key' => 'messages.order_success.create_account_btn', 'en' => 'Create Free Account', 'ar' => 'أنشئ حسابك مجانًا', 'group' => 'messages'],
            ['key' => 'messages.order_success.track_order_btn', 'en' => 'Track Order', 'ar' => 'تتبع الطلب', 'group' => 'messages'],
            ['key' => 'messages.order_success.migration_note', 'en' => 'Your current order will be automatically linked to your new account!', 'ar' => 'طلبك الحالي سيتم ربطه تلقائيًا بحسابك الجديد!', 'group' => 'messages'],

            // Success Messages
            ['key' => 'messages.order_success.thank_you', 'en' => 'Thank You for Your Order!', 'ar' => 'شكرًا لطلبك!', 'group' => 'messages'],
            ['key' => 'messages.order_success.confirmation_sent', 'en' => 'A confirmation email has been sent to your email address', 'ar' => 'تم إرسال رسالة تأكيد إلى بريدك الإلكتروني', 'group' => 'messages'],
            ['key' => 'messages.order_success.order_number', 'en' => 'Order Number', 'ar' => 'رقم الطلب', 'group' => 'messages'],
            ['key' => 'messages.order_success.order_date', 'en' => 'Order Date', 'ar' => 'تاريخ الطلب', 'group' => 'messages'],
            ['key' => 'messages.order_success.items_ordered', 'en' => 'Items Ordered', 'ar' => 'المنتجات المطلوبة', 'group' => 'messages'],
            ['key' => 'messages.order_success.qty', 'en' => 'Qty', 'ar' => 'الكمية', 'group' => 'messages'],
            ['key' => 'messages.order_success.discount', 'en' => 'Discount', 'ar' => 'الخصم', 'group' => 'messages'],
            ['key' => 'messages.order_success.shipping_to', 'en' => 'Shipping Address', 'ar' => 'عنوان الشحن', 'group' => 'messages'],
            ['key' => 'messages.order_success.cod_note', 'en' => 'Pay when you receive your order', 'ar' => 'ادفع عند استلام طلبك', 'group' => 'messages'],
            ['key' => 'messages.order_success.view_orders', 'en' => 'View My Orders', 'ar' => 'عرض طلباتي', 'group' => 'messages'],
            ['key' => 'messages.order_success.help_text', 'en' => 'Need help? Contact us at', 'ar' => 'تحتاج مساعدة؟ تواصل معنا على', 'group' => 'messages'],
        ];

        foreach ($translations as $translation) {
            // Create English translation
            Translation::updateOrCreate(
                [
                    'key' => $translation['key'],
                    'locale' => 'en',
                ],
                [
                    'value' => $translation['en'],
                    'group' => $translation['group'],
                    'is_active' => true,
                ]
            );

            // Create Arabic translation
            Translation::updateOrCreate(
                [
                    'key' => $translation['key'],
                    'locale' => 'ar',
                ],
                [
                    'value' => $translation['ar'],
                    'group' => $translation['group'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Frontend translations seeded successfully!');
        $this->command->info('📊 Total keys: ' . count($translations));
        $this->command->info('🌐 Locales: ar, en');
    }
}
