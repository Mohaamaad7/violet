# 🧪 Customer Resource Testing Guide

**Last Updated:** December 30, 2025

---

## 🎯 Pre-Testing Setup

### 1. Database Check:
```bash
# Make sure customers table has data
php artisan tinker
>>> \App\Models\Customer::count()
# Should return > 0
```

### 2. Create Test Customer (if needed):
```php
php artisan tinker
>>> $customer = \App\Models\Customer::create([
    'name' => 'Test Customer',
    'email' => 'test@customer.com',
    'password' => bcrypt('password123'),
    'phone' => '01234567890',
    'status' => 'active',
    'locale' => 'ar',
]);
>>> echo "Customer ID: " . $customer->id;
```

### 3. Create Test Data:
```php
// Add some orders
>>> $order = \App\Models\Order::create([
    'customer_id' => $customer->id,
    'order_number' => 'ORD-TEST-001',
    'status' => \App\Enums\OrderStatus::PENDING,
    'payment_status' => 'unpaid',
    'payment_method' => 'cod',
    'subtotal' => 500,
    'total' => 550,
]);

// Add wishlist items
>>> $product = \App\Models\Product::first();
>>> \App\Models\Wishlist::create([
    'customer_id' => $customer->id,
    'product_id' => $product->id,
]);

// Add shipping address
>>> \App\Models\ShippingAddress::create([
    'customer_id' => $customer->id,
    'full_name' => 'Test Customer',
    'phone' => '01234567890',
    'email' => 'test@customer.com',
    'governorate' => 'Cairo',
    'city' => 'Nasr City',
    'street_address' => '123 Test St',
    'is_default' => true,
]);
```

---

## 📋 Testing Scenarios

### Scenario 1: List Page - Basic Display

**Steps:**
1. Login to admin panel: `/admin`
2. Navigate to: **Customers** in sidebar
3. Verify page loads successfully

**Expected Results:**
- ✅ Page title: "العملاء" (AR) or "Customers" (EN)
- ✅ Navigation badge shows customer count
- ✅ Table displays with all columns
- ✅ No "Create" button (customers register from storefront)
- ✅ Auto-refresh indicator visible (30s)

**Screenshot Checklist:**
- [ ] Full page view
- [ ] Navigation sidebar showing "العملاء"
- [ ] Table with data

---

### Scenario 2: List Page - Columns Display

**Steps:**
1. On customers list page
2. Examine each column

**Expected Results:**

**Profile Photo:**
- ✅ Circular avatar (40px)
- ✅ If no photo: shows ui-avatars.com with initials
- ✅ If has photo: displays uploaded image

**Name:**
- ✅ Bold text
- ✅ Clickable (sortable)
- ✅ Searchable in table search

**Email:**
- ✅ Envelope icon visible
- ✅ Click to copy (shows "تم النسخ" / "Copied" tooltip)
- ✅ Sortable
- ✅ Searchable

**Phone:**
- ✅ Phone icon visible
- ✅ Shows number OR "غير متوفر" / "Not Available"
- ✅ Searchable

**Total Orders:**
- ✅ Badge format (blue/info color)
- ✅ Centered alignment
- ✅ Number format (no decimals)
- ✅ Sortable

**Total Spent:**
- ✅ Shows "XXX.XX EGP" format
- ✅ Right-aligned
- ✅ Sortable

**Last Order At:**
- ✅ Date format: d/m/Y (30/12/2025)
- ✅ Shows "لا توجد طلبات بعد" / "No orders yet" if empty
- ✅ Sortable

**Status:**
- ✅ Badge with correct color:
  - Active: Green (success)
  - Blocked: Red (danger)
  - Inactive: Yellow (warning)
- ✅ Text translated correctly
- ✅ Sortable

**Created At (toggleable):**
- ✅ Hidden by default
- ✅ Can toggle visibility
- ✅ Date format: d/m/Y

---

### Scenario 3: List Page - Filters

**Test Status Filter:**
1. Click "Filters" button
2. Select "Status" filter
3. Choose "Active" → Verify only active customers show
4. Choose "Blocked" → Verify only blocked customers show
5. Choose "Inactive" → Verify only inactive customers show
6. Clear filter

**Expected:**
- ✅ Filter applies immediately
- ✅ Results update without page reload
- ✅ Active filter chip shows below filters
- ✅ Can clear individual filter

**Test Date Range Filter:**
1. Click "Filters"
2. Expand "Created At" filter
3. Set "Date From": 01/01/2025
4. Set "Date To": 31/12/2025
5. Apply filter

**Expected:**
- ✅ Shows customers registered in date range
- ✅ Date picker in Arabic/English based on locale
- ✅ Can clear both dates independently

**Test Total Orders Filter:**
1. Click "Filters"
2. Expand "Total Orders" filter
3. Set "Min Orders": 1
4. Set "Max Orders": 10
5. Apply filter

**Expected:**
- ✅ Shows customers with 1-10 orders
- ✅ Numeric inputs only
- ✅ Min/Max validation works

**Test Total Spent Filter:**
1. Click "Filters"
2. Expand "Total Spent" filter
3. Set "Min Spent": 100
4. Set "Max Spent": 1000
5. Apply filter

**Expected:**
- ✅ Shows customers with 100-1000 EGP spent
- ✅ "EGP" prefix shows
- ✅ Numeric inputs only

---

### Scenario 4: List Page - Search

**Steps:**
1. Use table search box
2. Search by name: "Test"
3. Search by email: "test@customer.com"
4. Search by phone: "0123"

**Expected:**
- ✅ Search is instant (debounced)
- ✅ Results update without page reload
- ✅ Searches across: name, email, phone
- ✅ Case-insensitive search

---

### Scenario 5: List Page - Sorting

**Steps:**
1. Click each sortable column header twice (ascending, descending)
2. Test columns: Name, Email, Total Orders, Total Spent, Last Order At, Status, Created At

**Expected:**
- ✅ Sort arrow indicator shows
- ✅ Data re-orders correctly
- ✅ Ascending (A-Z, 0-9, oldest-newest)
- ✅ Descending (Z-A, 9-0, newest-oldest)

---

### Scenario 6: List Page - Bulk Actions

**Test Activate Selected:**
1. Select 2-3 blocked customers (checkbox)
2. Open bulk actions menu
3. Click "Activate Selected"
4. Confirm in modal
5. Verify success notification
6. Check status badges changed to green "Active"

**Test Block Selected:**
1. Select 2-3 active customers
2. Open bulk actions menu
3. Click "Block Selected"
4. Confirm in modal
5. Verify success notification
6. Check status badges changed to red "Blocked"

**Test Delete:**
1. Select 1 customer
2. Open bulk actions menu
3. Click "Delete"
4. Confirm in modal
5. Verify success notification
6. Verify customer removed from list
7. Check database: `deleted_at` should be set (soft delete)

```sql
SELECT id, name, email, deleted_at FROM customers WHERE deleted_at IS NOT NULL;
```

---

### Scenario 7: View Page - Customer Info

**Steps:**
1. Click "View" icon (eye) on any customer
2. Verify "Customer Information" section

**Expected:**

**Profile Photo:**
- ✅ Larger circular avatar (100px)
- ✅ Fallback works if no photo

**Name:**
- ✅ Bold, large size
- ✅ Displays correctly

**Email:**
- ✅ Envelope icon visible
- ✅ Click to copy works
- ✅ Shows "تم النسخ" / "Copied" message

**Phone:**
- ✅ Phone icon visible
- ✅ Shows number or "غير متوفر" / "Not Available"

**Status:**
- ✅ Badge with correct color and text
- ✅ Matches customer's actual status

**Locale:**
- ✅ Shows "🇪🇬 العربية" for ar
- ✅ Shows "🇬🇧 English" for en

---

### Scenario 8: View Page - Statistics

**Steps:**
1. On customer view page
2. Examine "Statistics" section

**Expected:**

**Total Orders:**
- ✅ Shopping bag icon
- ✅ Blue badge
- ✅ Number formatted (1,234)
- ✅ Accurate count matches orders

**Total Spent:**
- ✅ Currency icon
- ✅ Bold, large text
- ✅ Money format: "1,234.56 EGP"
- ✅ Accurate sum from paid orders

**Last Order At:**
- ✅ Calendar icon
- ✅ DateTime format: d/m/Y - h:i A
- ✅ Shows "لا توجد طلبات بعد" if no orders
- ✅ Accurate timestamp

**Verify Accuracy:**
```sql
SELECT 
    COUNT(*) as total_orders,
    SUM(total) as total_spent,
    MAX(created_at) as last_order
FROM orders 
WHERE customer_id = [CUSTOMER_ID] 
  AND payment_status = 'paid';
```

---

### Scenario 9: View Page - Recent Orders

**Steps:**
1. On customer view page
2. Expand "Recent Orders" section (if collapsed)

**Expected:**

**Display:**
- ✅ Shows max 5 orders
- ✅ Newest first (latest at top)
- ✅ Section hidden if customer has 0 orders

**Each Order Row:**
- ✅ **Order Number:** Bold, clickable link
- ✅ Click order number → navigates to order view page
- ✅ **Total:** Money format "XXX.XX EGP"
- ✅ **Status:** Badge with enum label (translated)
- ✅ **Payment Status:** Badge with translation
- ✅ **Created At:** Date format d/m/Y

**Test Order Link:**
1. Click any order number
2. Verify navigates to: `/admin/orders/[ORDER_ID]`
3. Verify order detail page opens correctly

---

### Scenario 10: View Page - Addresses

**Steps:**
1. On customer view page
2. Expand "Saved Addresses" section

**Expected:**

**Display:**
- ✅ Shows all addresses (not limited)
- ✅ Section hidden if customer has 0 addresses

**Each Address:**
- ✅ **Full Name:** User icon, bold
- ✅ **Phone:** Phone icon
- ✅ **Is Default:** Green "Yes" badge (visible only if default)
- ✅ **Formatted Address:** Map pin icon, full span
- ✅ Address includes: governorate, city, street

**Verify Formatting:**
- Address should be formatted by `ShippingAddress::getFormattedAddressAttribute()`
- Example: "123 Test St, Nasr City, Cairo"

---

### Scenario 11: View Page - Timestamps

**Steps:**
1. On customer view page
2. Expand "Timestamps" section (collapsed by default)

**Expected:**

**Created At:**
- ✅ DateTime format: d/m/Y - h:i A
- ✅ Example: "30/12/2025 - 03:45 PM"

**Email Verified At:**
- ✅ DateTime format (if verified)
- ✅ Shows "غير مفعّل" / "Not Verified" if null
- ✅ Placeholder text in gray

**Updated At:**
- ✅ DateTime format: d/m/Y - h:i A
- ✅ Updates when customer data changes

---

### Scenario 12: Edit Page

**Steps:**
1. Click "Edit" button on view page OR list page
2. Verify form loads with pre-filled data

**Expected:**

**Form Fields:**
- ✅ Profile Photo: Shows current image or placeholder
- ✅ Name: Pre-filled with current value
- ✅ Email: Pre-filled with current value
- ✅ Phone: Pre-filled (or empty if no phone)
- ✅ Status: Dropdown with current selection
- ✅ Locale: Dropdown with current selection

**Test Photo Upload:**
1. Click "Browse" on profile photo field
2. Select image (< 2MB, jpg/png)
3. Verify image preview appears
4. Test image editor (crop, rotate)
5. Save

**Expected:**
- ✅ Image uploads successfully
- ✅ Preview updates immediately
- ✅ Image saved to: `storage/app/public/customers/profiles/`
- ✅ View page shows new photo

**Test Email Validation:**
1. Change email to existing customer email
2. Try to save

**Expected:**
- ✅ Validation error: "Email already exists"
- ✅ Error shows in red below field
- ✅ Form doesn't submit

**Test Status Change:**
1. Change status from Active to Blocked
2. Save
3. Go back to list page

**Expected:**
- ✅ Status badge now shows red "Blocked"
- ✅ Customer can no longer login to storefront

**Test Locale Change:**
1. Change locale from ar to en
2. Save
3. Check customer's next login

**Expected:**
- ✅ Storefront loads in English for this customer
- ✅ Emails sent in English

**Security Note Section:**
- ✅ Section exists and is collapsible
- ✅ Shows security message about password
- ✅ Message explains: "Cannot edit password from admin panel"
- ✅ Mentions "Reset Password" action

---

### Scenario 13: Send Email Action

**Steps:**
1. On view page, click "Send Email" button
2. Modal opens

**Expected Modal:**
- ✅ Title: "إرسال بريد إلكتروني" / "Send Email"
- ✅ Width: xl (extra large)
- ✅ Two form fields visible

**Test Form:**
1. Enter subject: "Test Email"
2. Enter message in RichEditor:
   - Try bold, italic, underline
   - Try bullet list
   - Try link
3. Click "Send" / "إرسال"

**Expected:**
- ✅ RichEditor toolbar works (6 buttons)
- ✅ Submit button shows
- ✅ Success notification appears
- ✅ Notification shows: "Email sent to: [email]"
- ✅ Modal closes

**Verify Email Sent:**
```sql
SELECT * FROM email_logs 
WHERE recipient = '[CUSTOMER_EMAIL]' 
ORDER BY created_at DESC 
LIMIT 1;
```

**Expected:**
- ✅ Record exists with correct subject
- ✅ `sent_at` is not null
- ✅ `status` = 'sent'

**Test Error Handling:**
1. Disconnect internet / disable SMTP
2. Try sending email
3. Verify failure notification shows
4. Verify error message displayed

---

### Scenario 14: Reset Password Action

**Steps:**
1. On view page, click "Reset Password" button
2. Confirmation modal opens

**Expected Modal:**
- ✅ Heading: "إعادة تعيين كلمة المرور" / "Reset Password"
- ✅ Description: "A password reset link will be sent..."
- ✅ Two buttons: Cancel + "Send Reset Link"
- ✅ Warning color (yellow/orange)
- ✅ Key icon visible

**Test Action:**
1. Click "Send Reset Link"
2. Wait for response

**Expected:**
- ✅ Success notification appears
- ✅ Message: "Reset link sent to: [email]"
- ✅ Modal closes automatically

**Verify Token Created:**
```sql
SELECT * FROM customer_password_reset_tokens 
WHERE email = '[CUSTOMER_EMAIL]' 
ORDER BY created_at DESC 
LIMIT 1;
```

**Expected:**
- ✅ Record exists
- ✅ `token` is hashed (not plain text)
- ✅ `created_at` is recent (within last minute)

**Test Email Sent:**
- ✅ Customer receives email
- ✅ Email contains reset link
- ✅ Link format: `/customer/password/reset/[TOKEN]?email=[EMAIL]`

**Test Link:**
1. Click link in email
2. Enter new password twice
3. Submit

**Expected:**
- ✅ Password reset successful
- ✅ Customer can login with new password
- ✅ Token is consumed (deleted from table)

---

### Scenario 15: View Wishlist Action

**Test with Wishlist Items:**
1. Ensure customer has 2-3 wishlist items
2. On view page, click "View Wishlist" button

**Expected Modal:**
- ✅ Title: "قائمة أمنيات [Name]" / "[Name]'s Wishlist"
- ✅ Width: 3xl (very wide)
- ✅ No submit button (view only)
- ✅ Cancel button: "إغلاق" / "Close"

**Expected Content:**
- ✅ Total items count: "إجمالي المنتجات: 3"
- ✅ Grid layout with cards

**Each Wishlist Item Card:**
- ✅ **Product Image:**
  - Thumbnail (80x80px)
  - Rounded corners
  - OR placeholder icon if no image
- ✅ **Product Name:**
  - Truncated if long
  - Dark text (readable)
- ✅ **SKU:**
  - Gray text below name
  - Format: "SKU: PROD-123"
- ✅ **Price:**
  - Bold, primary color
  - Format: "XXX.XX EGP"
- ✅ **Sale Price (if exists):**
  - Strikethrough
  - Gray text
  - Smaller font
  - To the right of main price
- ✅ **Stock Status Badge:**
  - Green "متوفر" / "In Stock" if stock > 0
  - Red "نفذ من المخزون" / "Out of Stock" if stock = 0
- ✅ **Added Date:**
  - Label: "أُضيف في" / "Added on"
  - Date format: d/m/Y
  - Gray text

**Test Empty Wishlist:**
1. Use customer with 0 wishlist items
2. Action button should NOT be visible

**OR:**
1. Delete all wishlist items for a customer
2. Refresh view page
3. Verify "View Wishlist" button disappears

---

### Scenario 16: Block/Activate Toggle

**Test Block Action:**
1. View an active customer
2. Click "Block" / "حظر" button (red, no-symbol icon)
3. Confirm in modal

**Expected:**
- ✅ Success notification
- ✅ Status badge updates to red "Blocked"
- ✅ Button changes to green "Activate" / "تفعيل"
- ✅ Button icon changes to check-circle
- ✅ Customer cannot login to storefront

**Test Activate Action:**
1. On same customer (now blocked)
2. Click "Activate" / "تفعيل" button (green)
3. Confirm in modal

**Expected:**
- ✅ Success notification
- ✅ Status badge updates to green "Active"
- ✅ Button changes back to red "Block"
- ✅ Customer can login to storefront again

**Test Visibility:**
- ✅ Button visible when status = active OR blocked
- ✅ Button hidden when status = inactive

---

### Scenario 17: Delete Customer

**Steps:**
1. On view page, click "Delete" button (red, trash icon)
2. Confirmation modal appears

**Expected:**
- ✅ Warning modal with red theme
- ✅ Message asks: "Are you sure?"
- ✅ Two buttons: Cancel + Delete

**Test Delete:**
1. Click "Delete"
2. Wait for response

**Expected:**
- ✅ Success notification
- ✅ Redirects to customers list
- ✅ Deleted customer not in list

**Verify Soft Delete:**
```sql
SELECT id, name, email, deleted_at 
FROM customers 
WHERE id = [CUSTOMER_ID];
```

**Expected:**
- ✅ Record still exists
- ✅ `deleted_at` is set (not null)
- ✅ Timestamp is recent

**Test Restore (if needed):**
```sql
UPDATE customers SET deleted_at = NULL WHERE id = [CUSTOMER_ID];
```

---

### Scenario 18: Translations - Arabic

**Steps:**
1. Set admin locale to Arabic
2. Navigate through all customer pages

**Expected:**
- ✅ Sidebar: "العملاء"
- ✅ Page title: "العملاء"
- ✅ All column headers in Arabic
- ✅ All filter labels in Arabic
- ✅ All action buttons in Arabic
- ✅ All modal titles in Arabic
- ✅ All form labels in Arabic
- ✅ All notifications in Arabic
- ✅ Date format: Arabic numerals (٣٠/١٢/٢٠٢٥) OR standard (30/12/2025)
- ✅ RTL layout: content flows right-to-left

---

### Scenario 19: Translations - English

**Steps:**
1. Set admin locale to English
2. Navigate through all customer pages

**Expected:**
- ✅ Sidebar: "Customers"
- ✅ Page title: "Customers"
- ✅ All column headers in English
- ✅ All filter labels in English
- ✅ All action buttons in English
- ✅ All modal titles in English
- ✅ All form labels in English
- ✅ All notifications in English
- ✅ Date format: English (30/12/2025)
- ✅ LTR layout: content flows left-to-right

---

### Scenario 20: Performance Testing

**Test Large Dataset:**
1. Create 500 customers (using factory)
```php
\App\Models\Customer::factory()->count(500)->create();
```

2. Navigate to customers list

**Expected:**
- ✅ Page loads in < 2 seconds
- ✅ Pagination works smoothly
- ✅ Filters apply quickly (< 1 second)
- ✅ Search is instant (debounced)
- ✅ No timeout errors

**Test N+1 Queries:**
1. Enable Laravel Debugbar
2. Navigate to view page for customer with:
   - 10+ orders
   - 5+ addresses
   - 10+ wishlist items

**Expected:**
- ✅ Total queries < 20
- ✅ No duplicate queries
- ✅ Eager loading works (orders, addresses, wishlists)

**Check Queries:**
```php
// In ViewCustomer page, verify eager loading:
$customer->load(['orders' => fn($q) => $q->latest()->take(5), 'shippingAddresses', 'wishlists.product.media']);
```

---

## ✅ Sign-Off Checklist

After completing all scenarios, check:

- [ ] All 20 scenarios passed
- [ ] All translations work (AR + EN)
- [ ] All actions work (Send Email, Reset Password, View Wishlist, Block, Delete)
- [ ] All filters work correctly
- [ ] All bulk actions work correctly
- [ ] Performance is acceptable (< 2s page load)
- [ ] No console errors in browser
- [ ] No errors in Laravel logs (`storage/logs/laravel.log`)
- [ ] No N+1 query issues
- [ ] RTL/LTR layouts work correctly
- [ ] Mobile responsive (if applicable)

---

## 🐛 Bug Report Template

If you find a bug, report it using this template:

```markdown
## Bug Report

**Scenario:** [Scenario number and name]
**Date:** [Date found]
**Browser:** [Chrome/Firefox/Safari/Edge + version]

**Steps to Reproduce:**
1. Step 1
2. Step 2
3. Step 3

**Expected Result:**
[What should happen]

**Actual Result:**
[What actually happened]

**Screenshots:**
[Attach screenshots if applicable]

**Error Messages:**
[Copy error from browser console or Laravel logs]

**Environment:**
- Laravel Version: 12.37
- PHP Version: 8.2
- Filament Version: 4.2
- Database: MySQL 8.0
```

---

## 📞 Support

If you encounter issues during testing:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JS errors
3. Use Laravel Debugbar for query inspection
4. Review SESSION_REPORT.md for implementation details

---

**Happy Testing! 🚀**
