<?php

return [
    // Navigation Groups
    'nav' => [
        'catalog' => 'الكتالوج',
        'sales' => 'المبيعات',
        'inventory' => 'المخزون',
        'customers' => 'العملاء',
        'content' => 'المحتوى',
        'geography' => 'الإعدادات الجغرافية',
        'settings' => 'الإعدادات',
        'system' => 'النظام',
    ],

    // Dashboard Configuration
    'dashboard_config' => [
        'widgets' => 'الويدجات',
        'widget' => 'ويدجت',
        'resources' => 'الموارد',
        'resource' => 'مورد',
        'nav_groups' => 'مجموعات القوائم',
        'nav_group' => 'مجموعة قوائم',
        'widget_info' => 'معلومات الويدجت',
        'resource_info' => 'معلومات المورد',
        'group_info' => 'معلومات المجموعة',
        'display_settings' => 'إعدادات العرض',
        'labels' => 'العناوين',
        'class' => 'الفئة',
        'group' => 'المجموعة',
        'description' => 'الوصف',
        'active_help' => 'الويدجات غير النشطة لن تظهر لأي مستخدم',
        'order' => 'الترتيب',
        'column_span' => 'عرض العمود',
        'roles_using' => 'الأدوار المستخدمة',
        'roles_with_access' => 'الأدوار ذات الصلاحية',
        'nav_sort' => 'ترتيب القائمة',
        'icon' => 'الأيقونة',
        'group_key' => 'مفتاح المجموعة',
        'label_ar' => 'العنوان بالعربية',
        'label_en' => 'العنوان بالإنجليزية',
    ],

    // Role Permissions Page
    'role_permissions' => [
        'title' => 'صلاحيات الأدوار',
        'select_role' => 'اختر الدور',
        'widgets' => 'الويدجات',
        'nav_groups' => 'مجموعات القوائم',
        'resources' => 'الموارد',
        'enable_all' => 'تفعيل الكل',
        'disable_all' => 'تعطيل الكل',
        'grant_full_access' => 'منح كل الصلاحيات',
        'resource_name' => 'المورد',
        'view' => 'عرض',
        'create' => 'إنشاء',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'widget_enabled' => 'تم تفعيل الويدجت',
        'widget_disabled' => 'تم تعطيل الويدجت',
        'nav_group_enabled' => 'تم تفعيل مجموعة القوائم',
        'nav_group_disabled' => 'تم تعطيل مجموعة القوائم',
        'permission_updated' => 'تم تحديث الصلاحية',
        'all_widgets_enabled' => 'تم تفعيل كل الويدجات',
        'all_widgets_disabled' => 'تم تعطيل كل الويدجات',
        'all_nav_groups_enabled' => 'تم تفعيل كل مجموعات القوائم',
        'full_access_granted' => 'تم منح كل الصلاحيات',
        // Zero-Config specific
        'no_widgets_found' => 'لا توجد ويدجات في الكود',
        'no_resources_found' => 'لا توجد موارد في الكود',
        'zero_config_info' => 'وضع الإعداد التلقائي مفعّل',
        'default_visible_info' => 'كل الويدجات والموارد مرئية تلقائياً',
        'override_info' => 'العناصر المميزة بـ "Override" لها إعدادات مخصصة لهذا الدور',
        'auto_discover_info' => 'الويدجات والموارد الجديدة تُكتشف تلقائياً من الكود',
    ],

    // Common Fields
    'address' => 'العنوان',
    'phone' => 'الهاتف',
    'notes' => 'ملاحظات',
    'active' => 'نشط',
    'created_by' => 'أنشأه',
    'created_at' => 'تاريخ الإنشاء',
    'status' => 'الحالة',
    'error' => 'خطأ',
    'category' => 'الفئة',

    // Table Headers
    'table' => [
        'id' => 'المعرف',
        'name' => 'الاسم',
        'image' => 'الصورة',
        'photo' => 'الصورة',
        'sku' => 'رمز المنتج',
        'stock' => 'المخزون',
        'price' => 'السعر',
        'category' => 'الفئة',
        'status' => 'الحالة',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'role' => 'الدور',
        'no_role' => 'بدون دور',
        'title' => 'العنوان',
        'link' => 'الرابط',
        'order' => 'الترتيب',
        'active' => 'نشط',
    ],

    // Form Fields
    'form' => [
        'user_info' => 'معلومات المستخدم',
        'profile_photo' => 'الصورة الشخصية',
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'language' => 'اللغة',
        'language_help' => 'اللغة المستخدمة في لوحة الإدارة',
        'role_permissions' => 'الدور والصلاحيات',
        'role' => 'الدور',
        'password_section' => 'كلمة المرور',
        'password' => 'كلمة المرور',
    ],

    // Actions
    'action' => [
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'duplicate' => 'نسخ',
        'view' => 'عرض',
        'create' => 'إنشاء',
        'export_excel' => 'تصدير Excel',
    ],

    // Status Values
    'status_values' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'draft' => 'مسودة',
    ],

    // Filters
    'filters' => [
        'stock_status' => 'حالة المخزون',
        'low_stock_only' => 'مخزون منخفض فقط',
        'featured_only' => 'مميز فقط',
        'min' => 'الحد الأدنى',
        'max' => 'الحد الأقصى',
        'date_from' => 'من تاريخ',
        'date_to' => 'إلى تاريخ',
    ],

    // Products
    'products' => [
        'title' => 'المنتجات',
        'singular' => 'منتج',
        'plural' => 'المنتجات',
        'fields' => [
            'sku' => 'كود المنتج',
        ],
    ],

    // Categories
    'categories' => [
        'title' => 'الفئات',
        'singular' => 'فئة',
        'plural' => 'الفئات',
    ],

    // Orders
    'orders' => [
        'title' => 'الطلبات',
        'singular' => 'طلب',
        'plural' => 'الطلبات',
        'fields' => [
            'order_number' => 'رقم الطلب',
            'total' => 'الإجمالي',
            'status' => 'حالة الطلب',
            'payment_status' => 'حالة الدفع',
            'created_at' => 'تاريخ الطلب',
        ],
        'payment_status' => [
            'paid' => 'مدفوع',
            'unpaid' => 'غير مدفوع',
            'pending' => 'معلق',
        ],
    ],

    // Payments
    'payments' => [
        'title' => 'المدفوعات',
        'singular' => 'دفعة',
        'plural' => 'المدفوعات',
    ],

    // Returns
    'returns' => [
        'title' => 'المرتجعات',
        'singular' => 'مرتجع',
        'plural' => 'المرتجعات',
    ],

    // Sliders
    'sliders' => [
        'title' => 'السلايدرز',
        'singular' => 'سلايدر',
        'plural' => 'السلايدرز',
    ],

    // Banners
    'banners' => [
        'title' => 'البانرات',
        'singular' => 'بانر',
        'plural' => 'البانرات',
    ],

    // Warehouses
    'warehouses' => [
        'title' => 'المخازن',
        'singular' => 'مخزن',
        'plural' => 'المخازن',
    ],

    // Stock Movements
    'stock_movements' => [
        'title' => 'حركات المخزون',
        'singular' => 'حركة مخزون',
        'plural' => 'حركات المخزون',
    ],

    // Stock Counts
    'stock_counts' => [
        'title' => 'جرد المخزون',
        'singular' => 'جرد',
        'plural' => 'جرد المخزون',
    ],

    // Low Stock Products
    'low_stock' => [
        'title' => 'منتجات مخزون منخفض',
        'singular' => 'منتج مخزون منخفض',
        'plural' => 'منتجات مخزون منخفض',
    ],

    // Out of Stock Products
    'out_of_stock' => [
        'title' => 'منتجات نفذت',
        'singular' => 'منتج نفذ',
        'plural' => 'منتجات نفذت',
    ],

    // Users
    'users' => [
        'title' => 'المستخدمين',
        'singular' => 'مستخدم',
        'plural' => 'المستخدمين',
    ],

    // Roles
    'roles' => [
        'title' => 'الأدوار',
        'singular' => 'دور',
        'plural' => 'الأدوار',
    ],

    // Permissions
    'permissions' => [
        'title' => 'الصلاحيات',
        'singular' => 'صلاحية',
        'plural' => 'الصلاحيات',
    ],

    // Translations
    'translations' => [
        'title' => 'الترجمات',
        'singular' => 'ترجمة',
        'plural' => 'الترجمات',
    ],

    // Settings
    'settings' => [
        'title' => 'الإعدادات',
        'singular' => 'إعداد',
        'plural' => 'الإعدادات',
    ],

    // Email Templates
    'email_templates' => [
        'title' => 'قوالب البريد',
        'singular' => 'قالب بريد',
        'plural' => 'قوالب البريد',
    ],

    // Email Logs
    'email_logs' => [
        'title' => 'سجلات البريد',
        'singular' => 'سجل بريد',
        'plural' => 'سجلات البريد',
    ],

    // Countries
    'countries' => [
        'title' => 'الدول',
        'singular' => 'دولة',
        'plural' => 'الدول',
    ],

    // Governorates
    'governorates' => [
        'title' => 'المحافظات',
        'singular' => 'محافظة',
        'plural' => 'المحافظات',
    ],

    // Cities
    'cities' => [
        'title' => 'المدن',
        'singular' => 'مدينة',
        'plural' => 'المدن',
    ],

    // Pages
    'pages' => [
        'payment_settings' => [
            'title' => 'إعدادات الدفع',
            'active_gateway' => 'البوابة النشطة',
            'gateway_description' => 'اختر بوابة الدفع التي سيتم استخدامها لجميع عمليات الدفع. سيتم عرض إعدادات البوابة المختارة فقط.',
            'enabled_gateway' => 'بوابة الدفع المفعّلة',
            'save_success' => 'تم حفظ الإعدادات',
        ],
        'sales_report' => [
            'title' => 'تقرير المبيعات',
        ],
    ],


    // Coupons
    'coupons' => [
        'title' => 'أكواد الخصم',
        'singular' => 'كود خصم',
        'plural' => 'أكواد الخصم',

        'form' => [
            'basic' => [
                'title' => 'المعلومات الأساسية',
                'desc' => 'الكود والنوع والملاحظات',
            ],
            'code' => 'كود الخصم',
            'code_help' => 'اختر كود سهل التذكر أو اضغط زر التوليد',
            'generate_code' => 'توليد كود عشوائي',
            'type' => 'نوع الكوبون',
            'internal_notes' => 'ملاحظات داخلية',
            'internal_notes_help' => 'ملاحظات للإدارة فقط (لن تظهر للعملاء)',

            'discount' => [
                'title' => 'إعدادات الخصم',
                'desc' => 'نوع وقيمة الخصم',
            ],
            'discount_type' => 'نوع الخصم',
            'discount_value' => 'قيمة الخصم',
            'max_discount' => 'الحد الأقصى للخصم',
            'max_discount_help' => 'اتركه فارغاً لعدم وجود حد أقصى',

            'conditions' => [
                'title' => 'شروط الاستخدام',
                'desc' => 'الحد الأدنى والتواريخ',
            ],
            'min_order' => 'الحد الأدنى للطلب',
            'starts_at' => 'تاريخ البداية',
            'expires_at' => 'تاريخ الانتهاء',

            'limits' => [
                'title' => 'حدود الاستخدام',
                'desc' => 'عدد مرات الاستخدام',
            ],
            'usage_limit' => 'إجمالي الاستخدام',
            'usage_limit_help' => 'اتركه فارغاً لاستخدام غير محدود',
            'usage_per_user' => 'لكل عميل',
            'usage_per_user_help' => 'عدد مرات الاستخدام لكل عميل',

            'targeting' => [
                'title' => 'التخصيص',
                'desc' => 'تحديد المنتجات والأقسام المشمولة أو المستثناة',
            ],
            'applies_categories' => 'أقسام مشمولة',
            'applies_categories_help' => 'اتركه فارغاً للتطبيق على الكل',
            'applies_products' => 'منتجات مشمولة',
            'applies_products_help' => 'اتركه فارغاً للتطبيق على الكل',
            'exclude_categories' => 'أقسام مستثناة',
            'exclude_categories_help' => 'هذه الأقسام لن يُطبق عليها الخصم',
            'exclude_products' => 'منتجات مستثناة',
            'exclude_products_help' => 'المنتجات التي عليها عرض حالياً',

            'settings' => [
                'title' => 'الإعدادات',
            ],
            'is_active' => 'مفعّل',
            'is_active_help' => 'يمكنك تعطيله مؤقتاً دون حذفه',
            'influencer' => 'المؤثر',
        ],

        'table' => [
            'code' => 'الكود',
            'code_copied' => 'تم نسخ الكود!',
            'discount_type' => 'نوع الخصم',
            'value' => 'القيمة',
            'usage' => 'الاستخدام',
            'expires' => 'ينتهي في',
            'no_expiry' => 'بدون انتهاء',
            'status' => 'الحالة',
            'active' => 'مفعّل',
        ],

        'types' => [
            'general' => 'عام',
            'influencer' => 'مؤثر',
            'campaign' => 'حملة',
        ],

        'discount_types' => [
            'percentage' => 'نسبة مئوية',
            'fixed' => 'مبلغ ثابت',
            'free_shipping' => 'شحن مجاني',
        ],

        'filters' => [
            'active_only' => 'المفعّلة فقط',
            'expired' => 'المنتهية',
            'valid' => 'الصالحة حالياً',
        ],

        'actions' => [
            'activate' => 'تفعيل المحدد',
            'deactivate' => 'تعطيل المحدد',
        ],
    ],

    // Customers
    'customers' => [
        'title' => 'العملاء',
        'singular' => 'عميل',
        'plural' => 'العملاء',

        'sections' => [
            'customer_info' => 'معلومات العميل',
            'basic_info' => 'المعلومات الأساسية',
            'statistics' => 'الإحصائيات',
            'recent_orders' => 'آخر الطلبات',
            'addresses' => 'العناوين المحفوظة',
            'timestamps' => 'التواريخ',
            'security_note' => 'ملاحظة أمنية',
        ],

        'fields' => [
            'profile_photo' => 'الصورة الشخصية',
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الموبايل',
            'status' => 'الحالة',
            'locale' => 'اللغة المفضلة',
            'total_orders' => 'عدد الطلبات',
            'total_spent' => 'إجمالي المشتريات',
            'last_order_at' => 'آخر طلب',
            'created_at' => 'تاريخ التسجيل',
            'updated_at' => 'آخر تحديث',
            'email_verified_at' => 'تاريخ تفعيل البريد',
        ],

        'status' => [
            'active' => 'نشط',
            'blocked' => 'محظور',
            'inactive' => 'غير نشط',
        ],

        'filters' => [
            'min_orders' => 'الحد الأدنى للطلبات',
            'max_orders' => 'الحد الأقصى للطلبات',
            'min_spent' => 'الحد الأدنى للمشتريات',
            'max_spent' => 'الحد الأقصى للمشتريات',
        ],

        'actions' => [
            'activate' => 'تفعيل',
            'block' => 'حظر',
            'activate_selected' => 'تفعيل المحدد',
            'block_selected' => 'حظر المحدد',
            'send_email' => 'إرسال بريد إلكتروني',
            'reset_password' => 'إعادة تعيين كلمة المرور',
            'view_wishlist' => 'عرض قائمة الأمنيات',
        ],

        'email' => [
            'subject' => 'الموضوع',
            'message' => 'الرسالة',
            'sent_success' => 'تم إرسال البريد بنجاح',
            'sent_failed' => 'فشل إرسال البريد',
            'sent_to' => 'تم إرسال البريد إلى: :email',
        ],

        'password' => [
            'reset_heading' => 'إعادة تعيين كلمة المرور',
            'reset_description' => 'سيتم إرسال رابط إعادة تعيين كلمة المرور إلى بريد العميل الإلكتروني.',
            'send_reset_link' => 'إرسال رابط إعادة التعيين',
            'sent_success' => 'تم إرسال رابط إعادة التعيين بنجاح',
            'sent_failed' => 'فشل إرسال رابط إعادة التعيين',
            'sent_to' => 'تم إرسال الرابط إلى: :email',
            'error' => 'حدث خطأ',
        ],

        'wishlist' => [
            'heading' => 'قائمة أمنيات :name',
            'empty' => 'لا توجد منتجات في قائمة الأمنيات',
            'total_items' => 'إجمالي المنتجات: :count',
        ],

        'messages' => [
            'password_security_note' => 'ملاحظة: لأسباب أمنية، لا يمكن تعديل كلمة المرور من لوحة الإدارة. استخدم خيار "إعادة تعيين كلمة المرور" لإرسال رابط إعادة التعيين للعميل.',
        ],
    ],

    // الويدجات
    'widgets' => [
        // الإحصائيات (قديم - للتوافقية)
        'stats' => [
            'heading' => 'ملخص الإحصائيات',
            'today_revenue' => 'إيرادات اليوم',
            'new_orders_today' => 'طلبات جديدة اليوم',
            'total_customers' => 'إجمالي العملاء',
            'products_in_stock' => 'منتجات متاحة',
            'vs_yesterday' => 'عن أمس',
            'no_change' => 'لا تغيير',
            'new_customers_this_week' => 'عميل جديد هذا الأسبوع',
            'low_stock_products' => 'منتج بمخزون منخفض',
            'all_in_stock' => 'الكل متوفر',
        ],

        // ويدجت إيرادات اليوم
        'today_revenue' => [
            'heading' => 'إيرادات اليوم',
            'title' => 'إيرادات اليوم',
        ],

        // ويدجت طلبات جديدة اليوم
        'new_orders_today' => [
            'heading' => 'طلبات جديدة اليوم',
            'title' => 'طلبات جديدة اليوم',
        ],

        // ويدجت إجمالي العملاء
        'total_customers' => [
            'heading' => 'إجمالي العملاء',
            'title' => 'إجمالي العملاء',
            'new_this_week' => 'عميل جديد هذا الأسبوع',
        ],

        // ويدجت منتجات متاحة
        'products_in_stock' => [
            'heading' => 'منتجات متاحة',
            'title' => 'منتجات متاحة',
            'low_stock' => 'منتج بمخزون منخفض',
            'all_in_stock' => 'الكل متوفر',
        ],

        // ويدجت آخر الطلبات
        'recent_orders' => [
            'heading' => 'آخر الطلبات',
            'order_number' => 'رقم الطلب',
            'customer' => 'العميل',
            'status' => 'الحالة',
            'total' => 'الإجمالي',
            'copied' => 'تم النسخ!',
            'view_all' => 'عرض كل الطلبات',
        ],

        // ويدجت مخطط المبيعات
        'sales' => [
            'heading' => 'مخطط المبيعات',
            'dataset_label' => 'الإيرادات (ج.م)',
            'filters' => [
                '7days' => 'آخر 7 أيام',
                '30days' => 'آخر 30 يوم',
            ],
            'desc_7days' => 'اتجاه الإيرادات خلال آخر 7 أيام',
            'desc_30days' => 'اتجاه الإيرادات خلال آخر 30 يوم',
        ],

        // ويدجت نفذ من المخزون
        'out_of_stock' => [
            'heading' => 'نفذ من المخزون',
            'title' => 'نفذ من المخزون',
            'of_total' => 'من إجمالي المنتجات',
        ],

        // ويدجت المرتجعات الموافق عليها
        'approved_returns' => [
            'heading' => 'المرتجعات الموافق عليها',
            'title' => 'المرتجعات الموافق عليها',
            'today' => 'تمت الموافقة اليوم',
        ],

        // ويدجت مرتجعات الشهر
        'monthly_returns' => [
            'heading' => 'مرتجعات هذا الشهر',
            'title' => 'مرتجعات هذا الشهر',
            'vs_last_month' => 'مقارنة بالشهر الماضي',
        ],

        // ويدجت الطلبات الحالية
        'current_orders' => [
            'heading' => 'الطلبات الحالية',
            'title' => 'طلبات قيد التنفيذ',
            'pending' => 'معلق',
            'processing' => 'قيد التجهيز',
            'shipped' => 'تم الشحن',
        ],

        // ويدجت الربح المحتمل
        'potential_profit' => [
            'heading' => 'الربح المحتمل',
            'title' => 'الربح المحتمل',
            'margin' => 'هامش الربح',
        ],

        // ويدجت إجمالي وحدات المخزون
        'total_stock_units' => [
            'heading' => 'إجمالي وحدات المخزون',
            'title' => 'إجمالي الوحدات في المخزون',
            'avg_per_product' => 'متوسط لكل منتج',
        ],
    ],

    // العملة
    'currency' => [
        'egp_short' => 'ج.م',
        'egp_full' => 'جنيه مصري',
    ],
];
