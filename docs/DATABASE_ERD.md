# 📊 Entity Relationship Diagram - Violet E-Commerce

## نظرة عامة على قاعدة البيانات

قاعدة البيانات مصممة لدعم منصة تجارة إلكترونية كاملة مع نظام مؤثرين وعمولات.

---

## 🗂️ المجموعات الرئيسية للجداول

### 1️⃣ جداول المستخدمين والصلاحيات
### 2️⃣ جداول المنتجات
### 3️⃣ جداول الطلبات
### 4️⃣ جداول المؤثرين والعمولات
### 5️⃣ جداول إضافية

---

## 1️⃣ جداول المستخدمين والصلاحيات

### `users`
```sql
- id (bigint, PK, auto_increment)
- name (varchar 255)
- email (varchar 255, unique)
- email_verified_at (timestamp, nullable)
- password (varchar 255)
- phone (varchar 20, nullable)
- type (enum: customer, influencer, admin) DEFAULT customer
- status (enum: active, inactive, suspended) DEFAULT active
- remember_token (varchar 100, nullable)
- timestamps
- softDeletes
```

### `roles` (Spatie)
```sql
- id (bigint, PK)
- name (varchar 255)
- guard_name (varchar 255)
- timestamps
```

### `permissions` (Spatie)
```sql
- id (bigint, PK)
- name (varchar 255)
- guard_name (varchar 255)
- timestamps
```

### `model_has_roles` (Spatie)
### `model_has_permissions` (Spatie)
### `role_has_permissions` (Spatie)

---

## 2️⃣ جداول المنتجات

### `categories`
```sql
- id (bigint, PK, auto_increment)
- parent_id (bigint, nullable, FK → categories.id)
- name (varchar 255)
- slug (varchar 255, unique)
- description (text, nullable)
- image (varchar 255, nullable)
- icon (varchar 255, nullable)
- order (int, default 0)
- is_active (boolean, default true)
- meta_title (varchar 255, nullable)
- meta_description (text, nullable)
- timestamps
- softDeletes
```

**العلاقات:**
- `parent` → Category (belongsTo)
- `children` → Categories (hasMany)
- `products` → Products (hasMany)

---

### `products`
```sql
- id (bigint, PK, auto_increment)
- category_id (bigint, FK → categories.id)
- name (varchar 255)
- slug (varchar 255, unique)
- sku (varchar 100, unique)
- description (text, nullable)
- short_description (text, nullable)
- price (decimal 10,2)
- sale_price (decimal 10,2, nullable)
- cost_price (decimal 10,2, nullable)
- stock (int, default 0)
- low_stock_threshold (int, default 5)
- weight (decimal 8,2, nullable)
- brand (varchar 100, nullable)
- barcode (varchar 100, nullable)
- status (enum: draft, active, inactive) DEFAULT active
- is_featured (boolean, default false)
- views_count (int, default 0)
- sales_count (int, default 0)
- meta_title (varchar 255, nullable)
- meta_description (text, nullable)
- meta_keywords (text, nullable)
- timestamps
- softDeletes
```

**العلاقات:**
- `category` → Category (belongsTo)
- `images` → ProductImages (hasMany)
- `variants` → ProductVariants (hasMany)
- `reviews` → ProductReviews (hasMany)

---

### `product_images`
```sql
- id (bigint, PK, auto_increment)
- product_id (bigint, FK → products.id, onDelete cascade)
- image_path (varchar 255)
- is_primary (boolean, default false)
- order (int, default 0)
- timestamps
```

**العلاقات:**
- `product` → Product (belongsTo)

---

### `product_variants`
```sql
- id (bigint, PK, auto_increment)
- product_id (bigint, FK → products.id, onDelete cascade)
- sku (varchar 100, unique)
- name (varchar 255) -- مثل: أحمر - كبير
- price (decimal 10,2, nullable)
- stock (int, default 0)
- image (varchar 255, nullable)
- attributes (json) -- {"color": "red", "size": "L"}
- timestamps
```

**العلاقات:**
- `product` → Product (belongsTo)

---

### `product_reviews`
```sql
- id (bigint, PK, auto_increment)
- product_id (bigint, FK → products.id, onDelete cascade)
- user_id (bigint, FK → users.id, onDelete cascade)
- order_id (bigint, nullable, FK → orders.id)
- rating (tinyint) -- 1-5
- title (varchar 255, nullable)
- comment (text, nullable)
- images (json, nullable)
- is_verified_purchase (boolean, default false)
- is_approved (boolean, default false)
- helpful_count (int, default 0)
- timestamps
```

**العلاقات:**
- `product` → Product (belongsTo)
- `user` → User (belongsTo)
- `order` → Order (belongsTo)

---

### `product_views`
```sql
- id (bigint, PK, auto_increment)
- product_id (bigint, FK → products.id, onDelete cascade)
- user_id (bigint, nullable, FK → users.id)
- ip_address (varchar 45)
- user_agent (text, nullable)
- viewed_at (timestamp)
```

**العلاقات:**
- `product` → Product (belongsTo)
- `user` → User (belongsTo)

---

## 3️⃣ جداول الطلبات

### `orders`
```sql
- id (bigint, PK, auto_increment)
- order_number (varchar 50, unique)
- user_id (bigint, nullable, FK → users.id)
- discount_code_id (bigint, nullable, FK → discount_codes.id)
- status (enum: pending, processing, shipped, delivered, cancelled) DEFAULT pending
- payment_status (enum: unpaid, paid, failed, refunded) DEFAULT unpaid
- payment_method (enum: cod, card, instapay) DEFAULT cod
- subtotal (decimal 10,2)
- discount_amount (decimal 10,2, default 0)
- shipping_cost (decimal 10,2, default 0)
- tax_amount (decimal 10,2, default 0)
- total (decimal 10,2)
- notes (text, nullable)
- admin_notes (text, nullable)
- payment_transaction_id (varchar 255, nullable)
- paid_at (timestamp, nullable)
- shipped_at (timestamp, nullable)
- delivered_at (timestamp, nullable)
- cancelled_at (timestamp, nullable)
- cancellation_reason (text, nullable)
- timestamps
```

**العلاقات:**
- `user` → User (belongsTo)
- `discountCode` → DiscountCode (belongsTo)
- `items` → OrderItems (hasMany)
- `shippingAddress` → ShippingAddress (hasOne)
- `statusHistory` → OrderStatusHistory (hasMany)

---

### `order_items`
```sql
- id (bigint, PK, auto_increment)
- order_id (bigint, FK → orders.id, onDelete cascade)
- product_id (bigint, FK → products.id)
- product_variant_id (bigint, nullable, FK → product_variants.id)
- product_name (varchar 255)
- product_sku (varchar 100)
- variant_name (varchar 255, nullable)
- price (decimal 10,2)
- quantity (int)
- subtotal (decimal 10,2)
- timestamps
```

**العلاقات:**
- `order` → Order (belongsTo)
- `product` → Product (belongsTo)
- `variant` → ProductVariant (belongsTo)

---

### `shipping_addresses`
```sql
- id (bigint, PK, auto_increment)
- order_id (bigint, unique, FK → orders.id, onDelete cascade)
- user_id (bigint, nullable, FK → users.id)
- full_name (varchar 255)
- phone (varchar 20)
- email (varchar 255)
- governorate (varchar 100)
- city (varchar 100)
- area (varchar 100, nullable)
- street_address (text)
- landmark (varchar 255, nullable)
- postal_code (varchar 20, nullable)
- is_default (boolean, default false)
- timestamps
```

**العلاقات:**
- `order` → Order (belongsTo)
- `user` → User (belongsTo)

---

### `order_status_history`
```sql
- id (bigint, PK, auto_increment)
- order_id (bigint, FK → orders.id, onDelete cascade)
- status (varchar 50)
- notes (text, nullable)
- changed_by (bigint, nullable, FK → users.id)
- timestamps
```

**العلاقات:**
- `order` → Order (belongsTo)
- `changedBy` → User (belongsTo)

---

## 4️⃣ جداول المؤثرين والعمولات

### `influencers`
```sql
- id (bigint, PK, auto_increment)
- user_id (bigint, unique, FK → users.id, onDelete cascade)
- instagram_url (varchar 255, nullable)
- facebook_url (varchar 255, nullable)
- tiktok_url (varchar 255, nullable)
- youtube_url (varchar 255, nullable)
- twitter_url (varchar 255, nullable)
- instagram_followers (int, default 0)
- facebook_followers (int, default 0)
- tiktok_followers (int, default 0)
- youtube_followers (int, default 0)
- twitter_followers (int, default 0)
- content_type (json, nullable)
- commission_rate (decimal 5,2, default 10.00) -- نسبة العمولة الافتراضية
- total_sales (decimal 10,2, default 0)
- total_commission_earned (decimal 10,2, default 0)
- total_commission_paid (decimal 10,2, default 0)
- balance (decimal 10,2, default 0)
- status (enum: active, inactive, suspended) DEFAULT active
- timestamps
```

**العلاقات:**
- `user` → User (belongsTo)
- `application` → InfluencerApplication (hasOne)
- `discountCodes` → DiscountCodes (hasMany)
- `commissions` → Commissions (hasMany)

---

### `influencer_applications`
```sql
- id (bigint, PK, auto_increment)
- user_id (bigint, nullable, FK → users.id)
- full_name (varchar 255)
- email (varchar 255)
- phone (varchar 20)
- instagram_url (varchar 255, nullable)
- facebook_url (varchar 255, nullable)
- tiktok_url (varchar 255, nullable)
- youtube_url (varchar 255, nullable)
- twitter_url (varchar 255, nullable)
- instagram_followers (int, default 0)
- facebook_followers (int, default 0)
- tiktok_followers (int, default 0)
- youtube_followers (int, default 0)
- twitter_followers (int, default 0)
- content_type (json, nullable)
- portfolio (text, nullable)
- status (enum: pending, approved, rejected) DEFAULT pending
- rejection_reason (text, nullable)
- reviewed_by (bigint, nullable, FK → users.id)
- reviewed_at (timestamp, nullable)
- timestamps
```

**العلاقات:**
- `user` → User (belongsTo)
- `reviewedBy` → User (belongsTo)

---

### `discount_codes`
```sql
- id (bigint, PK, auto_increment)
- influencer_id (bigint, nullable, FK → influencers.id, onDelete cascade)
- code (varchar 50, unique)
- type (enum: influencer, general, campaign) DEFAULT influencer
- discount_type (enum: percentage, fixed) DEFAULT percentage
- discount_value (decimal 10,2)
- max_discount_amount (decimal 10,2, nullable)
- min_order_amount (decimal 10,2, default 0)
- commission_type (enum: percentage, fixed) DEFAULT percentage
- commission_value (decimal 10,2)
- usage_limit (int, nullable)
- usage_limit_per_user (int, default 1)
- times_used (int, default 0)
- starts_at (timestamp, nullable)
- expires_at (timestamp, nullable)
- is_active (boolean, default true)
- applies_to_categories (json, nullable)
- applies_to_products (json, nullable)
- timestamps
```

**العلاقات:**
- `influencer` → Influencer (belongsTo)
- `orders` → Orders (hasMany)
- `usages` → CodeUsages (hasMany)

---

### `code_usages`
```sql
- id (bigint, PK, auto_increment)
- discount_code_id (bigint, FK → discount_codes.id, onDelete cascade)
- user_id (bigint, nullable, FK → users.id)
- order_id (bigint, nullable, FK → orders.id)
- discount_amount (decimal 10,2)
- timestamps
```

**العلاقات:**
- `discountCode` → DiscountCode (belongsTo)
- `user` → User (belongsTo)
- `order` → Order (belongsTo)

---

### `influencer_commissions`
```sql
- id (bigint, PK, auto_increment)
- influencer_id (bigint, FK → influencers.id, onDelete cascade)
- order_id (bigint, FK → orders.id, onDelete cascade)
- discount_code_id (bigint, FK → discount_codes.id)
- order_amount (decimal 10,2)
- commission_rate (decimal 5,2)
- commission_amount (decimal 10,2)
- status (enum: pending, due, paid, cancelled) DEFAULT pending
- paid_at (timestamp, nullable)
- payout_id (bigint, nullable, FK → commission_payouts.id)
- timestamps
```

**العلاقات:**
- `influencer` → Influencer (belongsTo)
- `order` → Order (belongsTo)
- `discountCode` → DiscountCode (belongsTo)
- `payout` → CommissionPayout (belongsTo)

---

### `commission_payouts`
```sql
- id (bigint, PK, auto_increment)
- influencer_id (bigint, FK → influencers.id)
- amount (decimal 10,2)
- method (enum: bank_transfer, cash, wallet) DEFAULT bank_transfer
- bank_details (json, nullable)
- status (enum: pending, approved, rejected, paid) DEFAULT pending
- rejection_reason (text, nullable)
- approved_by (bigint, nullable, FK → users.id)
- approved_at (timestamp, nullable)
- paid_by (bigint, nullable, FK → users.id)
- paid_at (timestamp, nullable)
- transaction_reference (varchar 255, nullable)
- notes (text, nullable)
- timestamps
```

**العلاقات:**
- `influencer` → Influencer (belongsTo)
- `commissions` → Commissions (hasMany)
- `approvedBy` → User (belongsTo)
- `paidBy` → User (belongsTo)

---

## 5️⃣ جداول إضافية

### `carts`
```sql
- id (bigint, PK, auto_increment)
- user_id (bigint, nullable, unique, FK → users.id, onDelete cascade)
- session_id (varchar 255, nullable)
- timestamps
```

**العلاقات:**
- `user` → User (belongsTo)
- `items` → CartItems (hasMany)

---

### `cart_items`
```sql
- id (bigint, PK, auto_increment)
- cart_id (bigint, FK → carts.id, onDelete cascade)
- product_id (bigint, FK → products.id)
- product_variant_id (bigint, nullable, FK → product_variants.id)
- quantity (int)
- timestamps
```

**العلاقات:**
- `cart` → Cart (belongsTo)
- `product` → Product (belongsTo)
- `variant` → ProductVariant (belongsTo)

---

### `wishlists`
```sql
- id (bigint, PK, auto_increment)
- user_id (bigint, FK → users.id, onDelete cascade)
- product_id (bigint, FK → products.id, onDelete cascade)
- timestamps
- unique(user_id, product_id)
```

**العلاقات:**
- `user` → User (belongsTo)
- `product` → Product (belongsTo)

---

### `notifications`
```sql
- id (char 36, PK, uuid)
- type (varchar 255)
- notifiable_type (varchar 255)
- notifiable_id (bigint)
- data (json)
- read_at (timestamp, nullable)
- timestamps
- index(notifiable_type, notifiable_id)
```

---

### `settings`
```sql
- id (bigint, PK, auto_increment)
- key (varchar 255, unique)
- value (text, nullable)
- type (enum: string, integer, boolean, json) DEFAULT string
- group (varchar 100, nullable)
- timestamps
```

---

### `pages`
```sql
- id (bigint, PK, auto_increment)
- title (varchar 255)
- slug (varchar 255, unique)
- content (longtext)
- meta_title (varchar 255, nullable)
- meta_description (text, nullable)
- is_active (boolean, default true)
- timestamps
```

---

### `blog_posts`
```sql
- id (bigint, PK, auto_increment)
- author_id (bigint, FK → users.id)
- title (varchar 255)
- slug (varchar 255, unique)
- excerpt (text, nullable)
- content (longtext)
- featured_image (varchar 255, nullable)
- is_published (boolean, default false)
- published_at (timestamp, nullable)
- meta_title (varchar 255, nullable)
- meta_description (text, nullable)
- timestamps
- softDeletes
```

**العلاقات:**
- `author` → User (belongsTo)

---

### `sliders`
```sql
- id (bigint, PK, auto_increment)
- title (varchar 255)
- subtitle (varchar 255, nullable)
- image (varchar 255)
- link (varchar 255, nullable)
- button_text (varchar 100, nullable)
- order (int, default 0)
- is_active (boolean, default true)
- timestamps
```

---

### `banners`
```sql
- id (bigint, PK, auto_increment)
- title (varchar 255)
- image (varchar 255)
- link (varchar 255, nullable)
- position (enum: top, sidebar, bottom, popup) DEFAULT sidebar
- order (int, default 0)
- is_active (boolean, default true)
- starts_at (timestamp, nullable)
- expires_at (timestamp, nullable)
- timestamps
```

---

## 📊 ملخص الإحصائيات

**إجمالي الجداول:** 31 جدول

**التصنيف:**
- 👥 المستخدمين والصلاحيات: 6 جداول
- 📦 المنتجات: 5 جداول
- 🛒 الطلبات: 4 جداول
- 🌟 المؤثرين: 6 جداول
- ➕ إضافية: 10 جداول

---

## 🔑 ملاحظات مهمة

1. **Foreign Keys:** جميع العلاقات محمية بـ Foreign Key Constraints
2. **Soft Deletes:** المستخدمين، المنتجات، الفئات، Blog Posts
3. **Indexes:** على جميع Foreign Keys والحقول المستخدمة في البحث
4. **Timestamps:** في جميع الجداول
5. **JSON Fields:** للبيانات المرنة (attributes, social links, etc.)
6. **Enum Types:** للحقول ذات القيم المحددة
7. **Decimal Precision:** (10,2) للأسعار - دقة عالية

---

تاريخ آخر تحديث: 9 نوفمبر 2025
