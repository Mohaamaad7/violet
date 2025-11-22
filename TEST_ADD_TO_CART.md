# 🧪 Add to Cart - Manual Testing Guide

**Date:** November 18, 2025  
**Purpose:** Verify Add to Cart functionality is working after bug fix

---

## ⚡ Quick Start

### 1. Start Server
```powershell
C:\server\bin\php\php-8.3.24-Win32-vs16-x64\php.exe artisan serve
```

Server will run at: **http://127.0.0.1:8000**

---

## ✅ Test Checklist

### Test 1: Product Listing Page Add to Cart

1. **Navigate to:** http://127.0.0.1:8000/products

2. **Open Browser DevTools:** Press `F12`
   - Go to **Console** tab
   - Go to **Network** tab (to monitor Livewire requests)

3. **Find any product card**

4. **Click "Add to Cart" button**

5. **✅ VERIFY ALL OF THESE HAPPEN:**

   | # | Check | Expected Behavior | Status |
   |---|-------|-------------------|--------|
   | 1 | Button State | Shows "Adding..." with spinner (< 1 sec) | ⬜ |
   | 2 | Button Reset | Returns to "Add to Cart" after completion | ⬜ |
   | 3 | **Toast** | Green notification appears top-right with "تمت إضافة المنتج للسلة" | ⬜ |
   | 4 | **Slide-over** | Cart panel slides in from right side | ⬜ |
   | 5 | Product Visible | Product appears in slide-over with image & price | ⬜ |
   | 6 | **Counter** | Red badge appears on header cart icon with "1" | ⬜ |
   | 7 | Console | No JavaScript errors | ⬜ |
   | 8 | Network | Livewire request shows status 200 | ⬜ |

---

### Test 2: Multiple Items

1. **Click "Add to Cart" on a DIFFERENT product**

2. **✅ VERIFY:**
   - [ ] Counter updates to "2"
   - [ ] Slide-over opens again
   - [ ] Both products visible in cart
   - [ ] Toast appears again

---

### Test 3: Quantity Management

1. **In the slide-over cart:**
   - Click **[+]** button on any item
   - Click **[-]** button

2. **✅ VERIFY:**
   - [ ] Quantity updates instantly
   - [ ] Price recalculates
   - [ ] Toast appears
   - [ ] Counter updates if needed

---

### Test 4: Remove Item

1. **Click "إزالة" (Remove)** on any item

2. **✅ VERIFY:**
   - [ ] Item disappears
   - [ ] Counter decrements
   - [ ] Toast shows success
   - [ ] If cart empty → "السلة فارغة" state shows

---

### Test 5: Product Details Page

1. **Navigate to:** http://127.0.0.1:8000/products/{any-slug}

2. **Select variant** (if available)

3. **Adjust quantity** using +/- buttons

4. **Click "Add to Cart"**

5. **✅ VERIFY:**
   - [ ] Same behavior as Test 1
   - [ ] Correct variant added
   - [ ] Correct quantity added

---

## 🎥 Record Video Proof

**For Client Verification:**

1. **Start screen recording** (Windows: Win+G → Record)

2. **Show DevTools Console** (no errors)

3. **Perform Test 1** (all 8 checks)

4. **Perform Test 2** (multiple items)

5. **Perform Test 4** (remove item)

6. **Stop recording**

**What to capture:**
- ✅ Toast notification appearing
- ✅ Slide-over opening smoothly
- ✅ Counter updating
- ✅ Console showing "Livewire initialized"
- ✅ Network tab showing 200 status

---

## ❌ If ANY Test Fails

### 1. Check Server is Running
```powershell
# Should show "Server running on [http://127.0.0.1:8000]"
```

### 2. Clear Browser Cache
```
Hard Refresh: Ctrl + F5
Or: Chrome DevTools → Network tab → Disable cache checkbox
```

### 3. Clear Laravel Caches
```powershell
C:\server\bin\php\php-8.3.24-Win32-vs16-x64\php.exe artisan optimize:clear
```

### 4. Check Laravel Logs
```powershell
Get-Content c:\server\www\violet\storage\logs\laravel.log -Tail 50
```

### 5. Check Console Errors
- Open DevTools Console
- Look for red error messages
- Copy exact error text

### 6. Check Network Tab
- Find the Livewire request
- Check status code (should be 200)
- If 500 → check Laravel log
- If 404 → component not found

---

## 🐛 Common Issues & Solutions

### Issue: Button Stuck in "Adding..." Forever

**Cause:** `<livewire:store.cart-manager />` missing from layout

**Solution:** Check `resources/views/components/store-layout.blade.php` contains:
```blade
<livewire:store.cart-manager />
```

---

### Issue: No Toast Appears

**Cause:** Toast listener not in layout

**Solution:** Check `store-layout.blade.php` has `window.addEventListener('show-toast', ...)`

---

### Issue: Counter Doesn't Update

**Cause:** Event listener not set up

**Solution:** Check header has:
```javascript
window.addEventListener('cart-count-updated', (e) => {
    count = e.detail.count;
});
```

---

### Issue: 500 Error in Network Tab

**Cause:** Database issue or missing columns

**Solution:** 
```powershell
# Re-run migrations
C:\server\bin\php\php-8.3.24-Win32-vs16-x64\php.exe artisan migrate:fresh --seed
```

---

## 📊 Test Results Template

**Tester:** ___________  
**Date:** ___________  
**Browser:** ___________  

| Test | Pass | Fail | Notes |
|------|------|------|-------|
| Test 1: Add to Cart | ⬜ | ⬜ | |
| Test 2: Multiple Items | ⬜ | ⬜ | |
| Test 3: Quantity | ⬜ | ⬜ | |
| Test 4: Remove | ⬜ | ⬜ | |
| Test 5: Product Details | ⬜ | ⬜ | |

**Overall Status:** ⬜ PASS | ⬜ FAIL

**Notes:**
```
[Add any observations here]
```

---

## ✅ Success Criteria

**ALL of these MUST be TRUE:**
- ✅ Button never stuck in loading state
- ✅ Toast notification appears every time
- ✅ Slide-over opens automatically
- ✅ Counter updates correctly
- ✅ No console errors
- ✅ No 500 errors in Network tab
- ✅ Items persist in cart (refresh page → still there)

---

**Test Duration:** ~5 minutes  
**Next Steps:** Record video → Send to client → Deploy to production
