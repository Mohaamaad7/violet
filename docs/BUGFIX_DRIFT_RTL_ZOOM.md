# Bugfix: Drift.js Image Zoom RTL Mode Coordinate Issue

## Issue Summary
The product image zoom lens (Drift.js) had miscalculated coordinates when the page was in RTL (Right-to-Left) mode (Arabic language). The zoom lens would track the mouse incorrectly, causing the zoom preview to show the wrong area of the image.

## Root Cause Analysis

### Understanding the Problem
Drift.js uses `getBoundingClientRect()` and `clientX/clientY` for mouse position tracking. These browser APIs **always** use a left-to-right coordinate system regardless of the page's text direction.

The issue occurs because:
1. In RTL mode, CSS `direction: rtl` affects the layout flow of child elements
2. Drift.js positions the bounding box using `element.style.left = ...`
3. The `left` property behaves differently in RTL containers
4. This causes a mismatch between mouse position calculation (always LTR) and element positioning (affected by RTL)

### Technical Details
From Drift.js `Trigger.js` (line 171-203):
```javascript
_handleMovement(e) {
    const rect = el.getBoundingClientRect();
    const offsetX = movementX - rect.left;
    const percentageOffsetX = offsetX / this.settings.el.clientWidth;
    // ...
    this.boundingBox.setPosition(percentageOffsetX, percentageOffsetY, rect);
}
```

The `getBoundingClientRect()` returns coordinates in **viewport** space, which is always LTR. When the bounding box is placed in an RTL container, its `left` positioning is affected by the RTL direction, causing visual misalignment.

## Solution Applied

### 1. CSS Direction Fix (`resources/css/app.css`)
Added explicit LTR direction to all Drift.js elements:

```css
/* 
 * RTL FIX: Force LTR direction on all Drift elements
 * This ensures coordinate calculations work correctly regardless of page direction.
 */
.drift-zoom-pane,
.drift-bounding-box,
.drift-amazon-zoom-pane,
.drift-amazon-bounding-box,
[class*="drift-"] {
    direction: ltr !important;
}
```

### 2. HTML Container Fix (`product-details.blade.php`)
Added explicit `dir="ltr"` to the zoom container:

```html
<div id="zoom-container" dir="ltr" class="aspect-square flex items-center justify-center p-8 relative overflow-hidden">
```

### 3. Bounding Box Container Configuration
Explicitly set `boundingBoxContainer` to ensure the bounding box is within the same LTR container:

```javascript
this.driftInstance = new window.Drift(mainImage, {
    // ...existing options...
    boundingBoxContainer: zoomContainer, // RTL Fix: Ensure bounding box is in same container
});
```

## Why This Works

1. **Consistent Coordinate Space**: By forcing LTR direction on Drift elements, we ensure that:
   - `getBoundingClientRect()` returns LTR coordinates
   - `element.style.left` positioning also uses LTR interpretation
   
2. **Isolated Container**: The zoom container uses `dir="ltr"` which creates an isolated LTR context for all Drift operations, while the rest of the page remains RTL.

3. **No Impact on UX**: The zoom lens behavior is identical in LTR and RTL - users hover over an area and see it zoomed. The direction of text/layout doesn't affect this interaction.

## Files Modified

1. `resources/css/app.css`
   - Added RTL fix CSS rules for Drift elements

2. `resources/views/livewire/store/product-details.blade.php`
   - Added `dir="ltr"` to zoom container
   - Added `boundingBoxContainer` configuration to Drift options
   - Added debug logging for page direction

## Testing

1. **RTL Mode Test**:
   - Switch to Arabic language
   - Navigate to any product details page
   - Hover over the product image
   - The zoom lens should track the mouse correctly
   - The zoomed preview should show the correct area

2. **LTR Mode Test**:
   - Switch to English language
   - Verify zoom still works correctly (no regression)

## Alternative Solutions Considered

### 1. JavaScript Coordinate Inversion
We could have intercepted mouse events and inverted the X coordinate in RTL mode:
```javascript
const isRtl = document.documentElement.dir === 'rtl';
const adjustedOffsetX = isRtl ? (rect.width - offsetX) : offsetX;
```

**Rejected**: This would require patching Drift.js internals or creating a wrapper, which is more complex and harder to maintain.

### 2. CSS Transform Mirror
```css
[dir="rtl"] .drift-bounding-box {
    transform: scaleX(-1);
}
```

**Rejected**: This would visually flip the element but not fix the underlying coordinate issue.

### 3. Custom Zoom Implementation
Build a custom zoom solution with native RTL support.

**Rejected**: Unnecessary complexity when the CSS direction fix solves the problem elegantly.

## References

- [Drift.js GitHub Repository](https://github.com/strawdynamics/drift)
- [MDN: getBoundingClientRect()](https://developer.mozilla.org/en-US/docs/Web/API/Element/getBoundingClientRect)
- [MDN: CSS direction property](https://developer.mozilla.org/en-US/docs/Web/CSS/direction)

## Date
January 2025
