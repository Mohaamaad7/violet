<?php

return [
    // Navigation Groups
    'nav' => [
        'catalog' => 'Catalog',                      // Products and Categories
        'sales' => 'Sales',                          // Orders, Payments, Coupons, Returns
        'inventory' => 'Inventory',                  // Warehouses, Stock Movements, Stock Counts
        'customers' => 'Customers',                  // Customer Management
        'content' => 'Content',                      // Sliders and Banners
        'geography' => 'Geographic Settings',        // Countries, Governorates, Cities
        'settings' => 'Settings',                    // System Settings and Email Templates
        'system' => 'System',                        // Users, Roles, Permissions, Translations
    ],

    // Table Headers
    'table' => [
        'id' => 'ID',
        'name' => 'Name',
        'image' => 'Image',
        'sku' => 'SKU',
        'stock' => 'Stock',
        'price' => 'Price',
        'category' => 'Category',
        'status' => 'Status',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    // Actions
    'action' => [
        'edit' => 'Edit',
        'delete' => 'Delete',
        'duplicate' => 'Duplicate',
        'view' => 'View',
        'create' => 'Create',
        'export_excel' => 'Export Excel',
    ],

    // Status
    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'draft' => 'Draft',
    ],

    // Filters
    'filters' => [
        'stock_status' => 'Stock Status',
        'low_stock_only' => 'Low Stock Only',
        'featured_only' => 'Featured Only',
        'min' => 'Min',
        'max' => 'Max',
        'date_from' => 'Date From',
        'date_to' => 'Date To',
    ],

    // Coupons
    'coupons' => [
        'title' => 'Discount Codes',
        'singular' => 'Discount Code',
        'plural' => 'Discount Codes',

        'form' => [
            'basic' => ['title' => 'Basic Information', 'desc' => 'Code, type, and notes'],
            'code' => 'Discount Code',
            'code_help' => 'Enter a memorable code or use the generate button',
            'generate_code' => 'Generate Random Code',
            'type' => 'Coupon Type',
            'internal_notes' => 'Internal Notes',
            'internal_notes_help' => 'Admin only (not visible to customers)',

            'discount' => ['title' => 'Discount Settings', 'desc' => 'Type and value'],
            'discount_type' => 'Discount Type',
            'discount_value' => 'Discount Value',
            'max_discount' => 'Max Discount',
            'max_discount_help' => 'Leave empty for no cap',

            'conditions' => ['title' => 'Conditions', 'desc' => 'Minimum order and dates'],
            'min_order' => 'Min Order Amount',
            'starts_at' => 'Start Date',
            'expires_at' => 'Expiry Date',

            'limits' => ['title' => 'Usage Limits', 'desc' => 'Limit usage'],
            'usage_limit' => 'Total Usage',
            'usage_limit_help' => 'Leave empty for unlimited',
            'usage_per_user' => 'Per Customer',
            'usage_per_user_help' => 'Uses per customer',

            'targeting' => ['title' => 'Targeting', 'desc' => 'Include/exclude products and categories'],
            'applies_categories' => 'Include Categories',
            'applies_categories_help' => 'Leave empty for all',
            'applies_products' => 'Include Products',
            'applies_products_help' => 'Leave empty for all',
            'exclude_categories' => 'Exclude Categories',
            'exclude_categories_help' => 'These categories will not get the discount',
            'exclude_products' => 'Exclude Products',
            'exclude_products_help' => 'Products currently on sale',

            'settings' => ['title' => 'Settings'],
            'is_active' => 'Active',
            'is_active_help' => 'Disable temporarily without deleting',
            'influencer' => 'Influencer',
        ],

        'table' => [
            'code' => 'Code',
            'code_copied' => 'Code copied!',
            'discount_type' => 'Type',
            'value' => 'Value',
            'usage' => 'Usage',
            'expires' => 'Expires',
            'no_expiry' => 'No expiry',
            'status' => 'Status',
            'active' => 'Active',
        ],

        'types' => ['general' => 'General', 'influencer' => 'Influencer', 'campaign' => 'Campaign'],
        'discount_types' => ['percentage' => 'Percentage', 'fixed' => 'Fixed Amount', 'free_shipping' => 'Free Shipping'],
        'filters' => ['active_only' => 'Active Only', 'expired' => 'Expired', 'valid' => 'Currently Valid'],
        'actions' => ['activate' => 'Activate Selected', 'deactivate' => 'Deactivate Selected'],
    ],

    // Customers
    'customers' => [
        'title' => 'Customers',
        'singular' => 'Customer',
        'plural' => 'Customers',

        'sections' => [
            'customer_info' => 'Customer Information',
            'basic_info' => 'Basic Information',
            'statistics' => 'Statistics',
            'recent_orders' => 'Recent Orders',
            'addresses' => 'Saved Addresses',
            'timestamps' => 'Timestamps',
            'security_note' => 'Security Note',
        ],

        'fields' => [
            'profile_photo' => 'Profile Photo',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone Number',
            'status' => 'Status',
            'locale' => 'Preferred Language',
            'total_orders' => 'Total Orders',
            'total_spent' => 'Total Spent',
            'last_order_at' => 'Last Order',
            'created_at' => 'Registration Date',
            'updated_at' => 'Last Updated',
            'email_verified_at' => 'Email Verified At',
        ],

        'status' => [
            'active' => 'Active',
            'blocked' => 'Blocked',
            'inactive' => 'Inactive',
        ],

        'filters' => [
            'min_orders' => 'Min Orders',
            'max_orders' => 'Max Orders',
            'min_spent' => 'Min Spent',
            'max_spent' => 'Max Spent',
        ],

        'actions' => [
            'activate' => 'Activate',
            'block' => 'Block',
            'activate_selected' => 'Activate Selected',
            'block_selected' => 'Block Selected',
            'send_email' => 'Send Email',
            'reset_password' => 'Reset Password',
            'view_wishlist' => 'View Wishlist',
        ],

        'email' => [
            'subject' => 'Subject',
            'message' => 'Message',
            'sent_success' => 'Email sent successfully',
            'sent_failed' => 'Failed to send email',
            'sent_to' => 'Email sent to: :email',
        ],

        'password' => [
            'reset_heading' => 'Reset Password',
            'reset_description' => 'A password reset link will be sent to the customer\'s email address.',
            'send_reset_link' => 'Send Reset Link',
            'sent_success' => 'Reset link sent successfully',
            'sent_failed' => 'Failed to send reset link',
            'sent_to' => 'Reset link sent to: :email',
            'error' => 'An error occurred',
        ],

        'wishlist' => [
            'heading' => ':name\'s Wishlist',
            'empty' => 'No products in wishlist',
            'total_items' => 'Total items: :count',
        ],

        'messages' => [
            'password_security_note' => 'Note: For security reasons, passwords cannot be edited from the admin panel. Use the "Reset Password" option to send a reset link to the customer.',
        ],
    ],
];
