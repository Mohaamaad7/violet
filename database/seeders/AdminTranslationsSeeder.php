<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class AdminTranslationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $translations = [
                        // Currency short label
                        'admin.currency.egp_short' => ['ar' => 'ج.م', 'en' => 'EGP'],

                        // Widgets: Stats Overview
                        'admin.widgets.stats.today_revenue' => ['ar' => 'إيرادات اليوم', 'en' => "Today's Revenue"],
                        'admin.widgets.stats.heading' => ['ar' => 'ملخص الإحصائيات', 'en' => 'Stats Overview'],
                        'admin.widgets.stats.vs_yesterday' => ['ar' => 'عن أمس', 'en' => 'vs yesterday'],
                        'admin.widgets.stats.new_orders_today' => ['ar' => 'طلبات جديدة اليوم', 'en' => 'New Orders Today'],
                        'admin.widgets.stats.total_customers' => ['ar' => 'إجمالي العملاء', 'en' => 'Total Customers'],
                        'admin.widgets.stats.new_customers_this_week' => ['ar' => 'عميل جديد هذا الأسبوع', 'en' => 'new this week'],
                        'admin.widgets.stats.products_in_stock' => ['ar' => 'منتجات متاحة', 'en' => 'Products In Stock'],
                        'admin.widgets.stats.low_stock_products' => ['ar' => 'منتج بمخزون منخفض', 'en' => 'low stock products'],
                        'admin.widgets.stats.all_in_stock' => ['ar' => 'جميع المنتجات متوفرة', 'en' => 'All products in stock'],
                        'admin.widgets.stats.no_change' => ['ar' => 'لا تغيير', 'en' => 'No change'],

                        // Widgets: Sales Chart
                        'admin.widgets.sales.filters.7days' => ['ar' => 'آخر 7 أيام', 'en' => 'Last 7 days'],
                        'admin.widgets.sales.filters.30days' => ['ar' => 'آخر 30 يوم', 'en' => 'Last 30 days'],
                        'admin.widgets.sales.heading' => ['ar' => 'مبيعات', 'en' => 'Sales'],
                        'admin.widgets.sales.dataset_label' => ['ar' => 'الإيرادات', 'en' => 'Revenue'],
                        'admin.widgets.sales.desc_7days' => ['ar' => 'إجمالي الإيرادات خلال آخر 7 أيام', 'en' => 'Total revenue over the last 7 days'],
                        'admin.widgets.sales.desc_30days' => ['ar' => 'إجمالي الإيرادات خلال آخر 30 يوم', 'en' => 'Total revenue over the last 30 days'],

                        // Widgets: Recent Orders
                        'admin.widgets.recent_orders.heading' => ['ar' => 'آخر الطلبات', 'en' => 'Recent Orders'],
                        'admin.widgets.recent_orders.order_number' => ['ar' => 'رقم الطلب', 'en' => 'Order Number'],
                        'admin.widgets.recent_orders.copied' => ['ar' => 'تم نسخ رقم الطلب', 'en' => 'Order number copied'],
                        'admin.widgets.recent_orders.customer' => ['ar' => 'العميل', 'en' => 'Customer'],
                        'admin.widgets.recent_orders.status' => ['ar' => 'الحالة', 'en' => 'Status'],
                        'admin.widgets.recent_orders.total' => ['ar' => 'الإجمالي', 'en' => 'Total'],
                        'admin.widgets.recent_orders.view_all' => ['ar' => 'عرض جميع الطلبات', 'en' => 'View all orders'],

                        // Orders: Status labels
                        'admin.orders.status.pending' => ['ar' => 'قيد الانتظار', 'en' => 'Pending'],
                        'admin.orders.status.processing' => ['ar' => 'قيد التجهيز', 'en' => 'Processing'],
                        'admin.orders.status.shipped' => ['ar' => 'تم الشحن', 'en' => 'Shipped'],
                        'admin.orders.status.delivered' => ['ar' => 'تم التسليم', 'en' => 'Delivered'],
                        'admin.orders.status.cancelled' => ['ar' => 'ملغي', 'en' => 'Cancelled'],

                        // Orders: Payment status labels
                        'admin.orders.payment.unpaid' => ['ar' => 'غير مدفوع', 'en' => 'Unpaid'],
                        'admin.orders.payment.paid' => ['ar' => 'مدفوع', 'en' => 'Paid'],
                        'admin.orders.payment.failed' => ['ar' => 'فشل', 'en' => 'Failed'],
                        'admin.orders.payment.refunded' => ['ar' => 'مسترد', 'en' => 'Refunded'],

                        // Orders: Payment method labels
                        'admin.orders.method.cod' => ['ar' => 'الدفع عند الاستلام', 'en' => 'Cash on Delivery'],
                        'admin.orders.method.card' => ['ar' => 'بطاقة', 'en' => 'Card'],
                        'admin.orders.method.instapay' => ['ar' => 'إنستاباي', 'en' => 'InstaPay'],
            // Navigation Groups
            'admin.nav.catalog' => ['ar' => 'الكتالوج', 'en' => 'Catalog'],
            'admin.nav.sales' => ['ar' => 'إدارة المبيعات', 'en' => 'Sales Management'],
            'admin.nav.system' => ['ar' => 'إدارة النظام', 'en' => 'System Management'],
            'admin.nav.content' => ['ar' => 'إدارة المحتوى', 'en' => 'Content Management'],

            // Categories Resource
            'admin.categories.title' => ['ar' => 'الفئات', 'en' => 'Categories'],
            'admin.categories.singular' => ['ar' => 'فئة', 'en' => 'Category'],
            'admin.categories.plural' => ['ar' => 'الفئات', 'en' => 'Categories'],

            // Products Resource
            'admin.products.title' => ['ar' => 'المنتجات', 'en' => 'Products'],
            'admin.products.singular' => ['ar' => 'منتج', 'en' => 'Product'],
            'admin.products.plural' => ['ar' => 'المنتجات', 'en' => 'Products'],

            // Orders Resource
            'admin.orders.title' => ['ar' => 'الطلبات', 'en' => 'Orders'],
            'admin.orders.singular' => ['ar' => 'طلب', 'en' => 'Order'],
            'admin.orders.plural' => ['ar' => 'الطلبات', 'en' => 'Orders'],

            // Users Resource
            'admin.users.title' => ['ar' => 'الموظفين', 'en' => 'Employees'],
            'admin.users.singular' => ['ar' => 'موظف', 'en' => 'Employee'],
            'admin.users.plural' => ['ar' => 'الموظفين', 'en' => 'Employees'],

            // Roles Resource
            'admin.roles.title' => ['ar' => 'الأدوار', 'en' => 'Roles'],
            'admin.roles.singular' => ['ar' => 'دور', 'en' => 'Role'],
            'admin.roles.plural' => ['ar' => 'الأدوار', 'en' => 'Roles'],

            // Permissions Resource
            'admin.permissions.title' => ['ar' => 'الصلاحيات', 'en' => 'Permissions'],
            'admin.permissions.singular' => ['ar' => 'صلاحية', 'en' => 'Permission'],
            'admin.permissions.plural' => ['ar' => 'الصلاحيات', 'en' => 'Permissions'],

            // Sliders Resource
            'admin.sliders.title' => ['ar' => 'السلايدرز', 'en' => 'Sliders'],
            'admin.sliders.singular' => ['ar' => 'سلايدر', 'en' => 'Slider'],
            'admin.sliders.plural' => ['ar' => 'السلايدرز', 'en' => 'Sliders'],

            // Banners Resource
            'admin.banners.title' => ['ar' => 'البنرات', 'en' => 'Banners'],
            'admin.banners.singular' => ['ar' => 'بنر', 'en' => 'Banner'],
            'admin.banners.plural' => ['ar' => 'البنرات', 'en' => 'Banners'],

            // Translations Resource
            'admin.translations.title' => ['ar' => 'الترجمات', 'en' => 'Translations'],
            'admin.translations.singular' => ['ar' => 'ترجمة', 'en' => 'Translation'],
            'admin.translations.plural' => ['ar' => 'الترجمات', 'en' => 'Translations'],

            // Common Form Fields
            'admin.form.name' => ['ar' => 'الاسم', 'en' => 'Name'],
            'admin.form.title' => ['ar' => 'العنوان', 'en' => 'Title'],
            'admin.form.description' => ['ar' => 'الوصف', 'en' => 'Description'],
            'admin.form.price' => ['ar' => 'السعر', 'en' => 'Price'],
            'admin.form.stock' => ['ar' => 'المخزون', 'en' => 'Stock'],
            'admin.form.category' => ['ar' => 'الفئة', 'en' => 'Category'],
            'admin.form.parent_category' => ['ar' => 'الفئة الأساسية', 'en' => 'Parent Category'],
            'admin.form.icon' => ['ar' => 'الأيقونة', 'en' => 'Icon'],
            'admin.form.order' => ['ar' => 'الترتيب', 'en' => 'Order'],
            'admin.form.image' => ['ar' => 'الصورة', 'en' => 'Image'],
            'admin.form.images' => ['ar' => 'الصور', 'en' => 'Images'],
            'admin.form.status' => ['ar' => 'الحالة', 'en' => 'Status'],
            'admin.form.active' => ['ar' => 'نشط', 'en' => 'Active'],
            'admin.form.inactive' => ['ar' => 'غير نشط', 'en' => 'Inactive'],
            'admin.form.is_active' => ['ar' => 'مفعّل', 'en' => 'Is Active'],
            'admin.form.is_featured' => ['ar' => 'مميز', 'en' => 'Featured'],
            'admin.form.email' => ['ar' => 'البريد الإلكتروني', 'en' => 'Email'],
            'admin.form.password' => ['ar' => 'كلمة المرور', 'en' => 'Password'],
            'admin.form.role' => ['ar' => 'الدور', 'en' => 'Role'],
            'admin.form.roles' => ['ar' => 'الأدوار', 'en' => 'Roles'],
            'admin.form.permissions' => ['ar' => 'الصلاحيات', 'en' => 'Permissions'],
            'admin.form.created_at' => ['ar' => 'تاريخ الإنشاء', 'en' => 'Created At'],
            'admin.form.updated_at' => ['ar' => 'تاريخ التحديث', 'en' => 'Updated At'],
            'admin.form.phone' => ['ar' => 'رقم الهاتف', 'en' => 'Phone'],
            'admin.form.profile_photo' => ['ar' => 'الصورة الشخصية', 'en' => 'Profile Photo'],
            'admin.form.user_info' => ['ar' => 'معلومات المستخدم', 'en' => 'User Information'],
            'admin.form.role_permissions' => ['ar' => 'الدور والصلاحيات', 'en' => 'Role & Permissions'],
            'admin.form.password_section' => ['ar' => 'كلمة المرور', 'en' => 'Password'],

            // Table Columns
            'admin.table.id' => ['ar' => 'المعرّف', 'en' => 'ID'],
            'admin.table.name' => ['ar' => 'الاسم', 'en' => 'Name'],
            'admin.table.title' => ['ar' => 'العنوان', 'en' => 'Title'],
            'admin.table.link' => ['ar' => 'الرابط', 'en' => 'Link'],
            'admin.table.no_link' => ['ar' => 'لا يوجد رابط', 'en' => 'No link'],
            'admin.table.no_title' => ['ar' => 'لا يوجد عنوان', 'en' => 'No title'],
            'admin.table.price' => ['ar' => 'السعر', 'en' => 'Price'],
            'admin.table.stock' => ['ar' => 'المخزون', 'en' => 'Stock'],
            'admin.table.category' => ['ar' => 'الفئة', 'en' => 'Category'],
            'admin.table.customer' => ['ar' => 'العميل', 'en' => 'Customer'],
            'admin.table.parent_category' => ['ar' => 'الفئة الأساسية', 'en' => 'Parent Category'],
            'admin.table.subcategories' => ['ar' => 'الفئات الفرعية', 'en' => 'Subcategories'],
            'admin.table.products' => ['ar' => 'المنتجات', 'en' => 'Products'],
            'admin.table.status' => ['ar' => 'الحالة', 'en' => 'Status'],
            'admin.table.active' => ['ar' => 'نشط', 'en' => 'Active'],
            'admin.table.featured' => ['ar' => 'مميز', 'en' => 'Featured'],
            'admin.table.created_at' => ['ar' => 'تاريخ الإنشاء', 'en' => 'Created At'],
            'admin.table.updated_at' => ['ar' => 'تاريخ التحديث', 'en' => 'Updated At'],
            'admin.table.actions' => ['ar' => 'الإجراءات', 'en' => 'Actions'],
            'admin.table.photo' => ['ar' => 'الصورة', 'en' => 'Photo'],
            'admin.table.image' => ['ar' => 'الصورة', 'en' => 'Image'],
            'admin.table.email' => ['ar' => 'البريد الإلكتروني', 'en' => 'Email'],
            'admin.table.phone' => ['ar' => 'رقم الهاتف', 'en' => 'Phone'],
            'admin.table.role' => ['ar' => 'الدور', 'en' => 'Role'],
            'admin.table.no_role' => ['ar' => 'لا يوجد', 'en' => 'No Role'],
            'admin.table.order' => ['ar' => 'الترتيب', 'en' => 'Order'],
            'admin.table.position' => ['ar' => 'الموضع', 'en' => 'Position'],
            'admin.table.order_number' => ['ar' => 'رقم الطلب', 'en' => 'Order Number'],
            'admin.table.order_status' => ['ar' => 'حالة الطلب', 'en' => 'Order Status'],
            'admin.table.payment_status' => ['ar' => 'حالة الدفع', 'en' => 'Payment Status'],
            'admin.table.payment_method' => ['ar' => 'طريقة الدفع', 'en' => 'Payment Method'],
            'admin.table.order_date' => ['ar' => 'تاريخ الطلب', 'en' => 'Order Date'],
            'admin.table.guard' => ['ar' => 'حارس', 'en' => 'Guard'],
            'admin.table.permissions_count' => ['ar' => 'عدد الصلاحيات', 'en' => 'Permissions Count'],

            // Actions
            'admin.action.create' => ['ar' => 'إنشاء', 'en' => 'Create'],
            'admin.action.edit' => ['ar' => 'تعديل', 'en' => 'Edit'],
            'admin.action.delete' => ['ar' => 'حذف', 'en' => 'Delete'],
            'admin.action.view' => ['ar' => 'عرض', 'en' => 'View'],
            'admin.action.view_details' => ['ar' => 'عرض التفاصيل', 'en' => 'View Details'],
            'admin.action.delete_selected' => ['ar' => 'حذف المحدد', 'en' => 'Delete Selected'],
            'admin.action.save' => ['ar' => 'حفظ', 'en' => 'Save'],
            'admin.action.cancel' => ['ar' => 'إلغاء', 'en' => 'Cancel'],
            'admin.action.back' => ['ar' => 'رجوع', 'en' => 'Back'],
            'admin.action.export' => ['ar' => 'تصدير', 'en' => 'Export'],
            'admin.action.import' => ['ar' => 'استيراد', 'en' => 'Import'],
            'admin.action.filter' => ['ar' => 'تصفية', 'en' => 'Filter'],
            'admin.action.search' => ['ar' => 'بحث', 'en' => 'Search'],

            // Filters
            'admin.filter.all' => ['ar' => 'الكل', 'en' => 'All'],
            'admin.filter.active' => ['ar' => 'النشط', 'en' => 'Active'],
            'admin.filter.inactive' => ['ar' => 'غير النشط', 'en' => 'Inactive'],
            'admin.filter.category' => ['ar' => 'حسب الفئة', 'en' => 'By Category'],
            'admin.filters.date_from' => ['ar' => 'من تاريخ', 'en' => 'From Date'],
            'admin.filters.date_to' => ['ar' => 'إلى تاريخ', 'en' => 'To Date'],

            // Banners positions
            'admin.banners.position.homepage_top' => ['ar' => 'الصفحة الرئيسية - أعلى', 'en' => 'Homepage - Top'],
            'admin.banners.position.homepage_middle' => ['ar' => 'الصفحة الرئيسية - منتصف', 'en' => 'Homepage - Middle'],
            'admin.banners.position.homepage_bottom' => ['ar' => 'الصفحة الرئيسية - أسفل', 'en' => 'Homepage - Bottom'],
            'admin.banners.position.sidebar_top' => ['ar' => 'الشريط الجانبي - أعلى', 'en' => 'Sidebar - Top'],
            'admin.banners.position.sidebar_middle' => ['ar' => 'الشريط الجانبي - منتصف', 'en' => 'Sidebar - Middle'],
            'admin.banners.position.sidebar_bottom' => ['ar' => 'الشريط الجانبي - أسفل', 'en' => 'Sidebar - Bottom'],
            'admin.banners.position.category_page' => ['ar' => 'صفحة الفئة', 'en' => 'Category Page'],
            'admin.banners.position.product_page' => ['ar' => 'صفحة المنتج', 'en' => 'Product Page'],

            // Messages
            'admin.message.created' => ['ar' => 'تم الإنشاء بنجاح', 'en' => 'Created successfully'],
            'admin.message.updated' => ['ar' => 'تم التحديث بنجاح', 'en' => 'Updated successfully'],
            'admin.message.deleted' => ['ar' => 'تم الحذف بنجاح', 'en' => 'Deleted successfully'],
            'admin.message.error' => ['ar' => 'حدث خطأ', 'en' => 'An error occurred'],

            // System Labels
            'admin.system.dashboard' => ['ar' => 'لوحة التحكم', 'en' => 'Dashboard'],
            'admin.system.logout' => ['ar' => 'تسجيل الخروج', 'en' => 'Logout'],
            'admin.system.profile' => ['ar' => 'الملف الشخصي', 'en' => 'Profile'],
            'admin.system.settings' => ['ar' => 'الإعدادات', 'en' => 'Settings'],
        ];

        $seededCount = 0;
        $locales = ['ar', 'en'];

        foreach ($translations as $key => $values) {
            foreach ($locales as $locale) {
                Translation::updateOrCreate(
                    [
                        'key' => $key,
                        'locale' => $locale,
                    ],
                    [
                        'group' => 'admin',
                        'value' => $values[$locale],
                        'is_active' => true,
                    ]
                );
                $seededCount++;
            }
        }

        $this->command->info("✅ Admin panel translations seeded successfully!");
        $this->command->info("📊 Total keys: " . count($translations));
        $this->command->info("🌐 Locales: " . implode(', ', $locales));
        $this->command->info("✨ Total records: {$seededCount}");
    }
}
