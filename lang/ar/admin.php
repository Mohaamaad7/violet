<?php

return [
    // Navigation Groups
    'nav' => [
        'catalog' => 'الكتالوج',                    // المنتجات والفئات
        'sales' => 'المبيعات',                      // الطلبات والمدفوعات والكوبونات والمرتجعات
        'inventory' => 'المخزون',                   // المخازن وحركات المخزون والجرد
        'customers' => 'العملاء',                   // إدارة العملاء
        'content' => 'المحتوى',                     // السلايدرز والبانرز
        'geography' => 'الإعدادات الجغرافية',       // البلاد والمحافظات والمدن
        'settings' => 'الإعدادات',                  // إعدادات النظام والإيميلات
        'system' => 'النظام',                       // المستخدمين والأدوار والصلاحيات والترجمات
    ],

    // Table Headers
    'table' => [
        'id' => 'المعرف',
        'name' => 'الاسم',
        'image' => 'الصورة',
        'sku' => 'رمز المنتج',
        'stock' => 'المخزون',
        'price' => 'السعر',
        'category' => 'الفئة',
        'status' => 'الحالة',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
    ],

    // Actions
    'action' => [
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'duplicate' => 'نسخ',
        'view' => 'عرض',
        'create' => 'إنشاء',
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

    // Orders
    'orders' => [
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

    // Shipping Addresses
    'shipping_addresses' => [
        'fields' => [
            'full_name' => 'الاسم الكامل',
            'phone' => 'رقم الموبايل',
            'address' => 'العنوان',
            'is_default' => 'العنوان الافتراضي',
        ],
    ],

    // Products
    'products' => [
        'fields' => [
            'sku' => 'كود المنتج',
        ],
    ],
];

