<?php

return [
    // Navigation Groups
    'nav' => [
        'inventory' => 'المخزون',
        'products' => 'المنتجات',
        'orders' => 'الطلبات',
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

    // Status
    'status' => [
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
];
