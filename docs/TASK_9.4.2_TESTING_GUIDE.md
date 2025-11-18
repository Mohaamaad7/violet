# 🧪 Task 9.4.2 - Testing Guide for Amazon-Style Image Gallery

**Test Date:** November 15, 2025  
**Tester:** [Your Name]  
**Environment:** Local Development (Laragon)

---

## 🎯 Quick Test URL

```
http://localhost/products/similique-quis-maxime
```

Or any product with multiple images from: `http://localhost/products`

---

## ✅ Acceptance Criteria Checklist

### 1️⃣ **ONE Main Image Display**
**What to Test:**
- [ ] Open any product page
- [ ] Count how many full-size images are visible in the main area
- [ ] **PASS:** Only ONE main image is visible
- [ ] **FAIL:** Multiple full-size images stacked/duplicated

**Expected Result:**
```
✅ PASS: Single main image visible
❌ FAIL: Multiple images or duplicates
```

---

### 2️⃣ **Thumbnail Click Switches Main Image**
**What to Test:**
1. [ ] Locate vertical thumbnail column on the left
2. [ ] Click the 2nd thumbnail
3. [ ] Verify main image changes to the 2nd image
4. [ ] Verify 2nd thumbnail gets orange border
5. [ ] Click the 3rd thumbnail
6. [ ] Verify main image changes to 3rd image
7. [ ] Verify 3rd thumbnail gets orange border
8. [ ] Verify 2nd thumbnail loses orange border

**Expected Result:**
```
✅ PASS: Main image switches instantly when thumbnail is clicked
✅ PASS: Active thumbnail has orange border
✅ PASS: Previous thumbnail loses active state
❌ FAIL: Nothing happens when clicking thumbnails
```

---

### 3️⃣ **Zoom on Hover (Drift.js)**
**What to Test:**
1. [ ] Hover your mouse over the main image
2. [ ] Look for an orange bounding box on the image
3. [ ] Look for a separate zoom pane appearing to the right/side
4. [ ] Move mouse slowly around the image
5. [ ] Verify zoom pane follows your mouse movement
6. [ ] Move mouse off the image
7. [ ] Verify zoom pane disappears

**Expected Result:**
```
✅ PASS: Orange bounding box appears on hover
✅ PASS: Magnified zoom pane appears beside image
✅ PASS: Zoom pane follows mouse movement
✅ PASS: Zoom pane disappears when mouse leaves
❌ FAIL: No zoom effect or image just scales
```

**Visual Guide:**
```
┌──────────────────────┐  ┌─────────────────┐
│ [Main Image]         │  │  ZOOM PANE      │
│                      │  │  (Magnified 3x) │
│   ┌───────┐          │  │                 │
│   │Orange │ ◄───────────┤  Shows this     │
│   │ Box   │          │  │  area zoomed    │
│   └───────┘          │  │                 │
│  (Cursor Position)   │  └─────────────────┘
└──────────────────────┘
```

---

### 4️⃣ **Lightbox Modal with Navigation (Spotlight.js)**

#### Part A: Opening Lightbox
**What to Test:**
1. [ ] Click on the main image
2. [ ] Verify a full-screen dark modal opens
3. [ ] Verify the clicked image is displayed in the modal
4. [ ] Verify you can see navigation arrows (left/right)
5. [ ] Verify you can see toolbar buttons (zoom, fullscreen, close)

**Expected Result:**
```
✅ PASS: Full-screen black/dark modal opens
✅ PASS: Image is centered and large
✅ PASS: Navigation controls visible
❌ FAIL: No modal or broken layout
```

#### Part B: Navigation Controls
**What to Test:**
1. [ ] Click the **Right Arrow** button
2. [ ] Verify next image appears
3. [ ] Click the **Left Arrow** button
4. [ ] Verify previous image appears
5. [ ] Press **Right Arrow Key** on keyboard
6. [ ] Verify next image appears
7. [ ] Press **Left Arrow Key** on keyboard
8. [ ] Verify previous image appears
9. [ ] Verify page counter shows (e.g., "3 / 9")

**Expected Result:**
```
✅ PASS: Right arrow shows next image
✅ PASS: Left arrow shows previous image
✅ PASS: Keyboard arrows work
✅ PASS: Page counter updates (e.g., "1 / 9" → "2 / 9")
❌ FAIL: Navigation doesn't work
```

#### Part C: Closing Lightbox
**What to Test:**
1. [ ] Press **ESC key**
2. [ ] Verify modal closes
3. [ ] Re-open lightbox (click main image)
4. [ ] Click the **X button** (top-right)
5. [ ] Verify modal closes
6. [ ] Re-open lightbox
7. [ ] Click on the **dark background** (outside image)
8. [ ] Verify modal closes

**Expected Result:**
```
✅ PASS: ESC key closes modal
✅ PASS: X button closes modal
✅ PASS: Clicking outside closes modal
✅ PASS: Returns to product page normally
❌ FAIL: Cannot close modal or page breaks
```

---

### 5️⃣ **Professional Amazon-Like Design**

**What to Test:**
1. [ ] Check vertical thumbnail layout (left side)
2. [ ] Verify thumbnails have square aspect ratio
3. [ ] Verify active thumbnail has orange border/ring
4. [ ] Verify hover state on thumbnails (lighter orange border)
5. [ ] Verify main image has clean white background
6. [ ] Verify main image has subtle gray border
7. [ ] Verify sale badge (if applicable) is in top-left
8. [ ] Verify "Hover to zoom" hint text is below image
9. [ ] Verify overall spacing and alignment is clean

**Expected Result:**
```
✅ PASS: Thumbnails on LEFT side (vertical column)
✅ PASS: Square thumbnails with borders
✅ PASS: Orange accent color (Amazon style)
✅ PASS: Clean, professional spacing
✅ PASS: Responsive layout (test mobile too)
❌ FAIL: Messy layout or misaligned elements
```

**Visual Comparison:**

**Amazon Layout:**
```
[T1] │ ┌─────────────────┐
[T2] │ │                 │
[T3] │ │  MAIN IMAGE     │
[T4] │ │                 │
[T5] │ └─────────────────┘
```

**Our Layout (Should Match):**
```
[T1] │ ┌─────────────────┐
[T2] │ │                 │
[T3] │ │  MAIN IMAGE     │
[T4] │ │                 │
[T5] │ └─────────────────┘
```

---

## 📱 Mobile Responsiveness Test

### Desktop (≥1024px)
**What to Test:**
1. [ ] Thumbnails are on the LEFT side
2. [ ] Thumbnails are stacked vertically
3. [ ] Main image is on the RIGHT side
4. [ ] Zoom works on hover
5. [ ] Layout is 2-column (thumbnails | main)

**Expected Result:**
```
✅ PASS: Vertical thumbnail column on left
✅ PASS: Zoom works on hover
```

---

### Tablet (768px - 1023px)
**What to Test:**
1. [ ] Resize browser window to tablet size
2. [ ] Verify thumbnails stay vertical (left side)
3. [ ] Verify main image scales appropriately
4. [ ] Test zoom on hover (if trackpad available)

---

### Mobile (<768px)
**What to Test:**
1. [ ] Resize browser to mobile size (or use DevTools)
2. [ ] Verify layout switches to single column
3. [ ] Verify thumbnails may move below main image (horizontal row)
4. [ ] Verify zoom is DISABLED (no hover effect)
5. [ ] Verify lightbox still works (touch to open)
6. [ ] Verify you can swipe left/right in lightbox

**Expected Result:**
```
✅ PASS: Single column layout on mobile
✅ PASS: Zoom disabled (handleTouch: false)
✅ PASS: Lightbox works with touch
✅ PASS: Can swipe to navigate in lightbox
```

---

## 🐛 Common Issues & Solutions

### Issue 1: Zoom Pane Not Appearing
**Symptoms:** Hovering does nothing.

**Check:**
```javascript
// Open browser console (F12)
// Type:
console.log(window.Drift);
// Should show: function Drift() {...}

// If undefined:
// - Assets not built (run npm run build)
// - Cache not cleared (php artisan optimize:clear)
// - Check for console errors
```

---

### Issue 2: Lightbox Opens at Wrong Image
**Symptoms:** Click thumbnail 3, lightbox opens at image 1.

**Solution:** Already fixed in code.
```javascript
// Verify in browser console:
Alpine.store('gallery').currentIndex
// Should match the clicked thumbnail index
```

---

### Issue 3: Thumbnails Not Clickable
**Symptoms:** Clicking thumbnails does nothing.

**Check:**
```javascript
// Open console, look for errors like:
// "changeImage is not defined"
// "Cannot read property of undefined"

// Verify Alpine.js loaded:
console.log(window.Alpine);
// Should show Alpine object

// Verify Livewire loaded:
console.log(window.Livewire);
// Should show Livewire object
```

---

### Issue 4: Assets Not Loading (404 Errors)
**Symptoms:** Images broken, console shows 404 errors.

**Solution:**
```bash
# 1. Ensure storage link exists
php artisan storage:link

# 2. Verify images exist in public disk
# Check: storage/app/public/products/

# 3. Clear caches
php artisan optimize:clear

# 4. Rebuild assets
npm run build
```

---

## 📊 Performance Check

### Load Time
**What to Test:**
1. [ ] Open Network tab (F12 → Network)
2. [ ] Reload page (Ctrl+R)
3. [ ] Check total page load time
4. [ ] Verify JavaScript files load:
   - [ ] `app-*.js` (main bundle)
5. [ ] Verify CSS files load:
   - [ ] `app-*.css` (styles)

**Expected Result:**
```
✅ PASS: Page loads in < 3 seconds
✅ PASS: No 404 errors for assets
✅ PASS: Drift and Spotlight libraries loaded
```

---

### Bundle Size
**What to Test:**
1. [ ] Check `app-*.js` file size in Network tab
2. [ ] Should be ~277KB (uncompressed) or ~88KB (gzipped)

**Expected Result:**
```
✅ PASS: Reasonable bundle size (<300KB)
✅ PASS: Gzip compression enabled (check Response Headers)
```

---

## ✅ Final Sign-Off

### Test Summary

| Acceptance Criteria | Status | Notes |
|---------------------|--------|-------|
| 1. Single main image | ⬜ PASS / ❌ FAIL | |
| 2. Thumbnail switching | ⬜ PASS / ❌ FAIL | |
| 3. Zoom on hover | ⬜ PASS / ❌ FAIL | |
| 4. Lightbox with navigation | ⬜ PASS / ❌ FAIL | |
| 5. Professional design | ⬜ PASS / ❌ FAIL | |

---

### Overall Result:
```
[ ] ✅ ALL TESTS PASSED - Ready for production
[ ] ⚠️ MINOR ISSUES - Needs small fixes
[ ] ❌ CRITICAL FAILURES - Requires major rework
```

---

### Tester Notes:
```
[Add your observations here]

Bugs Found:
- [ ] None
- [ ] List any issues...

Suggestions:
- [ ] None
- [ ] List improvements...
```

---

### Tested By:
- **Name:** ___________________
- **Date:** ___________________
- **Browser:** ___________________
- **OS:** ___________________

---

## 📷 Screenshot Checklist

**Please capture:**
1. [ ] Full page view showing thumbnails + main image
2. [ ] Hover state with zoom pane visible
3. [ ] Lightbox modal open
4. [ ] Mobile view (responsive)
5. [ ] Active thumbnail with orange border

**Save screenshots to:** `docs/screenshots/task-9.4.2/`

---

## 🎉 Ready for Review

Once all tests pass:
1. ✅ Mark all checkboxes
2. ✅ Fill in tester information
3. ✅ Capture screenshots
4. ✅ Submit for final review

---

**End of Testing Guide** 🚀
