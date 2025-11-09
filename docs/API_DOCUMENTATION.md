# 📡 Violet E-Commerce API Documentation

**Base URL:** `http://localhost:8000`

**Authentication:** Required for Admin routes (Bearer Token or Session)

---

## 🔐 Authentication

All `/admin/*` routes require authentication and proper permissions.

**Headers:**
```
Authorization: Bearer {your-token}
Accept: application/json
Content-Type: application/json
```

---

## 📊 Dashboard

### Get Dashboard Statistics
```http
GET /admin/dashboard
```

**Response:**
```json
{
  "success": true,
  "data": {
    "orders": {
      "total_orders": 0,
      "pending_orders": 0,
      "total_revenue": 0
    },
    "products": {
      "total": 150,
      "active": 113,
      "in_stock": 149,
      "low_stock": 5,
      "out_of_stock": 1
    },
    "categories": {
      "total": 20,
      "active": 20
    },
    "users": {
      "total": 3,
      "customers": 1,
      "influencers": 0
    }
  }
}
```

---

## 📦 Categories

### List All Categories
```http
GET /admin/categories
GET /api/categories (Public)
```

**Query Parameters:**
- `active` - Filter by status (true/false)
- `parent_id` - Filter by parent category
- `search` - Search in name/description

### Get Category Tree
```http
GET /admin/categories/tree
```

### Get Single Category
```http
GET /admin/categories/{id}
GET /api/categories/{id} (Public)
```

### Create Category
```http
POST /admin/categories
```

**Body:**
```json
{
  "name": "Electronics",
  "slug": "electronics",
  "description": "Electronic products",
  "parent_id": null,
  "is_active": true,
  "order": 0
}
```

### Update Category
```http
PUT /admin/categories/{id}
```

### Delete Category
```http
DELETE /admin/categories/{id}
```

### Toggle Active Status
```http
POST /admin/categories/{id}/toggle-active
```

### Update Order
```http
POST /admin/categories/{id}/update-order
```

**Body:**
```json
{
  "order": 5
}
```

### Move Category
```http
POST /admin/categories/{id}/move
```

**Body:**
```json
{
  "parent_id": 2
}
```

### Get Category Stats
```http
GET /admin/categories/{id}/stats
```

---

## 🛍️ Products

### List All Products
```http
GET /admin/products
GET /api/products (Public)
```

**Query Parameters:**
- `category_id` - Filter by category
- `is_active` - Filter by status
- `is_featured` - Filter featured products
- `stock_status` - in_stock|out_of_stock|low_stock
- `min_price` - Minimum price
- `max_price` - Maximum price
- `search` - Search in name/description/SKU
- `sort_by` - Sort field (created_at, price, name, stock)
- `sort_order` - asc|desc
- `per_page` - Items per page (default: 15)

### Get Featured Products
```http
GET /admin/products/featured
GET /api/products/featured (Public)
```

**Query Parameters:**
- `limit` - Number of products (default: 10)

### Get Products On Sale
```http
GET /admin/products/on-sale
GET /api/products/on-sale (Public)
```

### Get Low Stock Products
```http
GET /admin/products/low-stock
```

**Query Parameters:**
- `threshold` - Stock threshold (default: 10)

### Get Out of Stock Products
```http
GET /admin/products/out-of-stock
```

### Get Single Product
```http
GET /admin/products/{id}
GET /api/products/{id} (Public)
```

### Create Product
```http
POST /admin/products
```

**Body:**
```json
{
  "category_id": 1,
  "name": "iPhone 15 Pro",
  "slug": "iphone-15-pro",
  "sku": "IP15P-001",
  "description": "Latest iPhone",
  "short_description": "Premium smartphone",
  "price": 50000,
  "sale_price": 45000,
  "stock": 10,
  "is_active": true,
  "is_featured": true
}
```

### Update Product
```http
PUT /admin/products/{id}
```

### Delete Product
```http
DELETE /admin/products/{id}
```

### Toggle Active Status
```http
POST /admin/products/{id}/toggle-active
```

### Toggle Featured Status
```http
POST /admin/products/{id}/toggle-featured
```

### Update Stock
```http
POST /admin/products/{id}/update-stock
```

**Body:**
```json
{
  "quantity": 50
}
```

### Update Price
```http
POST /admin/products/{id}/update-price
```

**Body:**
```json
{
  "price": 55000,
  "sale_price": 48000
}
```

### Get Product Stats
```http
GET /admin/products/{id}/stats
```

**Response:**
```json
{
  "success": true,
  "data": {
    "views": 0,
    "orders": 0,
    "total_sold": 0,
    "reviews_count": 0,
    "average_rating": null,
    "wishlist_count": 0
  }
}
```

---

## 📋 Orders

### List All Orders
```http
GET /admin/orders
```

**Query Parameters:**
- `status` - pending|processing|shipped|delivered|cancelled
- `payment_status` - pending|paid|failed|refunded
- `payment_method` - cash|card|bank_transfer
- `user_id` - Filter by user
- `start_date` - Filter by date range
- `end_date` - Filter by date range
- `search` - Search by order number
- `per_page` - Items per page (default: 15)

### Get Single Order
```http
GET /admin/orders/{id}
```

### Get Recent Orders
```http
GET /admin/orders/recent
```

**Query Parameters:**
- `limit` - Number of orders (default: 10)

### Update Order Status
```http
POST /admin/orders/{id}/update-status
```

**Body:**
```json
{
  "status": "processing",
  "notes": "Order is being prepared"
}
```

### Update Payment Status
```http
POST /admin/orders/{id}/update-payment
```

**Body:**
```json
{
  "payment_status": "paid",
  "transaction_id": "TXN123456"
}
```

### Cancel Order
```http
POST /admin/orders/{id}/cancel
```

**Body:**
```json
{
  "reason": "Customer requested cancellation"
}
```

### Get Order Stats
```http
GET /admin/orders/stats
```

**Query Parameters:**
- `start_date` - Filter by date range
- `end_date` - Filter by date range

**Response:**
```json
{
  "success": true,
  "data": {
    "total_orders": 0,
    "pending_orders": 0,
    "processing_orders": 0,
    "shipped_orders": 0,
    "delivered_orders": 0,
    "cancelled_orders": 0,
    "total_revenue": 0,
    "pending_revenue": 0
  }
}
```

---

## ⚡ Testing Examples

### Using cURL

**Get All Products:**
```bash
curl http://localhost:8000/api/products
```

**Get All Categories:**
```bash
curl http://localhost:8000/api/categories
```

**Get Featured Products:**
```bash
curl http://localhost:8000/api/products/featured?limit=5
```

**Create Category (Authenticated):**
```bash
curl -X POST http://localhost:8000/admin/categories \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Category",
    "is_active": true
  }'
```

---

## 📝 Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "error": "Detailed error"
}
```

### Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

---

## 🔒 Permissions Required

| Route | Permission |
|-------|-----------|
| Dashboard | `view dashboard` |
| Categories List/View | `view categories` |
| Categories Create | `create categories` |
| Categories Edit | `edit categories` |
| Categories Delete | `delete categories` |
| Products List/View | `view products` |
| Products Create | `create products` |
| Products Edit | `edit products` |
| Products Delete | `delete products` |
| Orders List/View | `view orders` |
| Orders Edit | `edit orders` |

---

## 🚀 Quick Start

1. **Start Server:**
```bash
php artisan serve
```

2. **Login as Admin:**
```
Email: admin@violet.com
Password: password
```

3. **Test Public API:**
```bash
curl http://localhost:8000/api/products
curl http://localhost:8000/api/categories
```

4. **Test Admin API (after login):**
```bash
curl http://localhost:8000/admin/dashboard \
  -H "Accept: application/json"
```

---

## 📊 Current Database State

- **Categories:** 20 (5 parent + 15 subcategories)
- **Products:** 150 (10 per subcategory)
- **Users:** 3 (Admin, Manager, Customer)
- **Orders:** 0 (ready to create)

---

**Status:** ✅ Phase 3 Complete - All API endpoints ready for testing!
