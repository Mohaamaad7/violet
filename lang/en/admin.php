<?php

return [
    // Navigation Groups
    'nav' => [
        'catalog' => 'Catalog',
        'sales' => 'Sales',
        'inventory' => 'Inventory',
        'customers' => 'Customers',
        'content' => 'Content',
        'geography' => 'Geographic Settings',
        'settings' => 'Settings',
        'system' => 'System',
        'general' => 'General',
    ],

    // Dashboard Configuration
    'dashboard_config' => [
        'widgets' => 'Widgets',
        'widget' => 'Widget',
        'resources' => 'Resources',
        'resource' => 'Resource',
        'nav_groups' => 'Navigation Groups',
        'nav_group' => 'Navigation Group',
        'widget_info' => 'Widget Information',
        'resource_info' => 'Resource Information',
        'group_info' => 'Group Information',
        'display_settings' => 'Display Settings',
        'labels' => 'Labels',
        'class' => 'Class',
        'group' => 'Group',
        'description' => 'Description',
        'active_help' => 'Inactive widgets will not appear for any user',
        'order' => 'Order',
        'column_span' => 'Column Span',
        'roles_using' => 'Roles Using',
        'roles_with_access' => 'Roles With Access',
        'nav_sort' => 'Navigation Sort',
        'icon' => 'Icon',
        'group_key' => 'Group Key',
        'label_ar' => 'Arabic Label',
        'label_en' => 'English Label',
    ],

    // Role Permissions Page
    'role_permissions' => [
        'title' => 'Role Permissions',
        'select_role' => 'Select Role',
        'filter_by_group' => 'Filter by Group',
        'all_groups' => 'All',
        'widgets' => 'Widgets',
        'nav_groups' => 'Navigation Groups',
        'resources' => 'Resources',
        'enable_all' => 'Enable All',
        'disable_all' => 'Disable All',
        'enable_group' => 'Enable Group',
        'disable_group' => 'Disable Group',
        'grant_full_access' => 'Grant Full Access',
        'grant_group_access' => 'Grant Access',
        'revoke_group_access' => 'Revoke Access',
        'resource_name' => 'Resource',
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'widget_enabled' => 'Widget enabled',
        'widget_disabled' => 'Widget disabled',
        'nav_group_enabled' => 'Navigation group enabled',
        'nav_group_disabled' => 'Navigation group disabled',
        'permission_updated' => 'Permission updated',
        'all_widgets_enabled' => 'All widgets enabled',
        'all_widgets_disabled' => 'All widgets disabled',
        'all_nav_groups_enabled' => 'All navigation groups enabled',
        'full_access_granted' => 'Full access granted',
        'group_widgets_enabled' => 'Group widgets enabled',
        'group_widgets_disabled' => 'Group widgets disabled',
        'group_access_granted' => 'Group access granted',
        'group_access_revoked' => 'Group access revoked',
        // Zero-Config specific
        'no_widgets_found' => 'No widgets found in the codebase',
        'no_resources_found' => 'No resources found in the codebase',
        'no_widgets_in_group' => 'No widgets in this group',
        'no_resources_in_group' => 'No resources in this group',
        'zero_config_info' => 'Zero-Config Mode Active',
        'default_visible_info' => 'All widgets and resources are visible by default',
        'override_info' => 'Items marked with "Override" have custom settings for this role',
        'auto_discover_info' => 'New widgets and resources are automatically discovered from code',
    ],

    // Common Fields
    'address' => 'Address',
    'phone' => 'Phone',
    'notes' => 'Notes',
    'active' => 'Active',
    'created_by' => 'Created By',
    'created_at' => 'Created At',
    'status' => 'Status',
    'error' => 'Error',
    'category' => 'Category',

    // Table Headers
    'table' => [
        'id' => 'ID',
        'name' => 'Name',
        'image' => 'Image',
        'photo' => 'Photo',
        'sku' => 'SKU',
        'stock' => 'Stock',
        'price' => 'Price',
        'category' => 'Category',
        'status' => 'Status',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'email' => 'Email',
        'phone' => 'Phone',
        'role' => 'Role',
        'no_role' => 'No Role',
        'title' => 'Title',
        'link' => 'Link',
        'order' => 'Order',
        'active' => 'Active',
    ],

    // Form Fields
    'form' => [
        'user_info' => 'User Information',
        'profile_photo' => 'Profile Photo',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'language' => 'Language',
        'language_help' => 'The language used in the admin panel',
        'role_permissions' => 'Role & Permissions',
        'role' => 'Role',
        'password_section' => 'Password',
        'password' => 'Password',
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
    'status_values' => [
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

    // Products
    'products' => [
        'title' => 'Products',
        'singular' => 'Product',
        'plural' => 'Products',
    ],

    // Categories
    'categories' => [
        'title' => 'Categories',
        'singular' => 'Category',
        'plural' => 'Categories',
    ],

    // Orders
    'orders' => [
        'title' => 'Orders',
        'singular' => 'Order',
        'plural' => 'Orders',
        'fields' => [
            'order_number' => 'Order Number',
            'total' => 'Total',
            'status' => 'Order Status',
            'payment_status' => 'Payment Status',
            'created_at' => 'Order Date',
        ],
        'payment_status' => [
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'pending' => 'Pending',
        ],
    ],

    // Payments
    'payments' => [
        'title' => 'Payments',
        'singular' => 'Payment',
        'plural' => 'Payments',
    ],

    // Returns
    'returns' => [
        'title' => 'Returns',
        'singular' => 'Return',
        'plural' => 'Returns',
    ],

    // Sliders
    'sliders' => [
        'title' => 'Sliders',
        'singular' => 'Slider',
        'plural' => 'Sliders',
    ],

    // Banners
    'banners' => [
        'title' => 'Banners',
        'singular' => 'Banner',
        'plural' => 'Banners',
    ],

    // Warehouses
    'warehouses' => [
        'title' => 'Warehouses',
        'singular' => 'Warehouse',
        'plural' => 'Warehouses',
    ],

    // Stock Movements
    'stock_movements' => [
        'title' => 'Stock Movements',
        'singular' => 'Stock Movement',
        'plural' => 'Stock Movements',
    ],

    // Stock Counts
    'stock_counts' => [
        'title' => 'Stock Counts',
        'singular' => 'Stock Count',
        'plural' => 'Stock Counts',
    ],

    // Low Stock Products
    'low_stock' => [
        'title' => 'Low Stock Products',
        'singular' => 'Low Stock Product',
        'plural' => 'Low Stock Products',
    ],

    // Out of Stock Products
    'out_of_stock' => [
        'title' => 'Out of Stock Products',
        'singular' => 'Out of Stock Product',
        'plural' => 'Out of Stock Products',
    ],

    // Users
    'users' => [
        'title' => 'Users',
        'singular' => 'User',
        'plural' => 'Users',
    ],

    // Roles
    'roles' => [
        'title' => 'Roles',
        'singular' => 'Role',
        'plural' => 'Roles',
    ],

    // Permissions
    'permissions' => [
        'title' => 'Permissions',
        'singular' => 'Permission',
        'plural' => 'Permissions',
    ],

    // Translations
    'translations' => [
        'title' => 'Translations',
        'singular' => 'Translation',
        'plural' => 'Translations',
    ],

    // Settings
    'settings' => [
        'title' => 'Settings',
        'singular' => 'Setting',
        'plural' => 'Settings',
    ],

    // Email Templates
    'email_templates' => [
        'title' => 'Email Templates',
        'singular' => 'Email Template',
        'plural' => 'Email Templates',
    ],

    // Email Logs
    'email_logs' => [
        'title' => 'Email Logs',
        'singular' => 'Email Log',
        'plural' => 'Email Logs',
    ],

    // Countries
    'countries' => [
        'title' => 'Countries',
        'singular' => 'Country',
        'plural' => 'Countries',
    ],

    // Governorates
    'governorates' => [
        'title' => 'Governorates',
        'singular' => 'Governorate',
        'plural' => 'Governorates',
    ],

    // Cities
    'cities' => [
        'title' => 'Cities',
        'singular' => 'City',
        'plural' => 'Cities',
    ],

    // Pages
    'pages' => [
        'payment_settings' => [
            'title' => 'Payment Settings',
            'active_gateway' => 'Active Gateway',
            'gateway_description' => 'Choose the payment gateway for all transactions. Only settings for selected gateway will be shown.',
            'enabled_gateway' => 'Active Payment Gateway',
            'save_success' => 'Settings saved successfully',
        ],
        'sales_report' => [
            'title' => 'Sales Report',
        ],
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

    // Widgets
    'widgets' => [
        // Stats (legacy - kept for compatibility)
        'stats' => [
            'heading' => 'Statistics Overview',
            'today_revenue' => 'Today\'s Revenue',
            'new_orders_today' => 'New Orders Today',
            'total_customers' => 'Total Customers',
            'products_in_stock' => 'Products in Stock',
            'vs_yesterday' => 'vs yesterday',
            'no_change' => 'No change',
            'new_customers_this_week' => 'new this week',
            'low_stock_products' => 'low stock products',
            'all_in_stock' => 'All in stock',
        ],

        // Today Revenue Widget
        'today_revenue' => [
            'heading' => 'Today\'s Revenue',
            'title' => 'Today\'s Revenue',
        ],

        // New Orders Today Widget
        'new_orders_today' => [
            'heading' => 'New Orders Today',
            'title' => 'New Orders Today',
        ],

        // Total Customers Widget
        'total_customers' => [
            'heading' => 'Total Customers',
            'title' => 'Total Customers',
            'new_this_week' => 'new this week',
        ],

        // Products In Stock Widget
        'products_in_stock' => [
            'heading' => 'Products in Stock',
            'title' => 'Products in Stock',
            'low_stock' => 'low stock products',
            'all_in_stock' => 'All in stock',
        ],

        // Recent Orders Widget
        'recent_orders' => [
            'heading' => 'Recent Orders',
            'order_number' => 'Order Number',
            'customer' => 'Customer',
            'status' => 'Status',
            'total' => 'Total',
            'copied' => 'Copied!',
            'view_all' => 'View All Orders',
        ],

        // Sales Chart Widget
        'sales' => [
            'heading' => 'Sales Chart',
            'dataset_label' => 'Revenue (EGP)',
            'filters' => [
                '7days' => 'Last 7 Days',
                '30days' => 'Last 30 Days',
            ],
            'desc_7days' => 'Revenue trend for the last 7 days',
            'desc_30days' => 'Revenue trend for the last 30 days',
        ],

        // Out of Stock Widget
        'out_of_stock' => [
            'heading' => 'Out of Stock',
            'title' => 'Out of Stock',
            'of_total' => 'of total products',
        ],

        // Approved Returns Widget
        'approved_returns' => [
            'heading' => 'Approved Returns',
            'title' => 'Approved Returns',
            'today' => 'approved today',
        ],

        // Monthly Returns Widget
        'monthly_returns' => [
            'heading' => 'Monthly Returns',
            'title' => 'Returns This Month',
            'vs_last_month' => 'vs last month',
        ],

        // Current Orders Widget
        'current_orders' => [
            'heading' => 'Current Orders',
            'title' => 'Orders in Progress',
            'pending' => 'pending',
            'processing' => 'processing',
            'shipped' => 'shipped',
        ],

        // Potential Profit Widget
        'potential_profit' => [
            'heading' => 'Potential Profit',
            'title' => 'Potential Profit',
            'margin' => 'profit margin',
        ],

        // Total Stock Units Widget
        'total_stock_units' => [
            'heading' => 'Total Stock Units',
            'title' => 'Total Units in Stock',
            'avg_per_product' => 'avg per product',
        ],
    ],

    // Currency
    'currency' => [
        'egp_short' => 'EGP',
        'egp_full' => 'Egyptian Pound',
    ],
];
