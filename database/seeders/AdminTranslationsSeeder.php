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
            'admin.table.price' => ['ar' => 'السعر', 'en' => 'Price'],
            'admin.table.stock' => ['ar' => 'المخزون', 'en' => 'Stock'],
            'admin.table.category' => ['ar' => 'الفئة', 'en' => 'Category'],
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
            'admin.table.email' => ['ar' => 'البريد الإلكتروني', 'en' => 'Email'],
            'admin.table.phone' => ['ar' => 'رقم الهاتف', 'en' => 'Phone'],
            'admin.table.role' => ['ar' => 'الدور', 'en' => 'Role'],
            'admin.table.no_role' => ['ar' => 'لا يوجد', 'en' => 'No Role'],

            // Actions
            'admin.action.create' => ['ar' => 'إنشاء', 'en' => 'Create'],
            'admin.action.edit' => ['ar' => 'تعديل', 'en' => 'Edit'],
            'admin.action.delete' => ['ar' => 'حذف', 'en' => 'Delete'],
            'admin.action.view' => ['ar' => 'عرض', 'en' => 'View'],
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
