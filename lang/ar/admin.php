<?php

return [
    // Navigation Groups
    'nav' => [
        'inventory' => 'المخزون',
        'products' => 'المنتجات',
        'orders' => 'الطلبات',
        'sales' => 'المبيعات',
        'customers' => 'العملاء',
        'settings' => 'الإعدادات',
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
];

