# Lessons Learned - Violet Project

## Purpose
This document tracks important lessons, issues, and their solutions discovered during development to prevent future occurrences.

---

## Table of Contents
1. [Filament FileUpload - Missing disk('public')](#1-filament-fileupload---missing-diskpublic)

---

## 1. Filament FileUpload - Missing disk('public')

**Date:** November 12, 2025  
**Severity:** 🔴 High (Affects all file upload features)  
**Components:** SliderResource, BannerResource (any resource with FileUpload)

### Problem Description
Images uploaded through Filament's `FileUpload` component succeeded without errors, but thumbnails didn't appear in `ImageColumn` in admin table lists.

### Symptoms
- ✅ Upload form works without errors
- ✅ Database record created with correct `image_path` value (e.g., `banners/xyz.jpg`)
- ❌ `ImageColumn` shows broken/missing image icon
- ❌ Browser console shows 404 error for image URL

### Root Cause
**Missing `->disk('public')` method in FileUpload configuration.**

Filament's `FileUpload` defaults to Laravel's 'local' disk (`storage/app/`) instead of the 'public' disk (`storage/app/public/`). Files saved to 'local' disk are NOT accessible via web URLs.

### Incorrect Code
```php
// ❌ WRONG - Uses default 'local' disk
FileUpload::make('image_path')
    ->directory('banners')
    ->image()
    ->required()
    ->maxSize(5120);
```

**Result:** File saved to `storage/app/banners/` (not web-accessible)

### Correct Code
```php
// ✅ CORRECT - Explicitly specify 'public' disk
FileUpload::make('image_path')
    ->disk('public')        // THIS LINE IS CRITICAL!
    ->directory('banners')
    ->image()
    ->required()
    ->maxSize(5120);
```

**Result:** File saved to `storage/app/public/banners/` (accessible via `/storage/banners/`)

### Files Modified
1. `app/Filament/Resources/Banners/Schemas/BannerForm.php` - Added `->disk('public')`
2. `app/Filament/Resources/Sliders/Schemas/SliderForm.php` - Added `->disk('public')`

### Prevention Checklist
- [ ] ✅ **Code Review:** Check all FileUpload components have explicit `->disk()` method
- [ ] ✅ **Template:** Add `->disk('public')` to Filament resource generation templates
- [ ] ✅ **Documentation:** Updated `.github/copilot-instructions.md` with FileUpload best practices
- [ ] ✅ **Testing:** Test image upload immediately after creating resources with FileUpload
- [ ] ✅ **Troubleshooting Guide:** Created `docs/TROUBLESHOOTING_IMAGE_UPLOAD.md`

### Related Documentation
- `docs/TROUBLESHOOTING_IMAGE_UPLOAD.md` - Complete troubleshooting guide
- `.github/copilot-instructions.md` - Added FileUpload configuration section
- Filament Docs: https://filamentphp.com/docs/4.x/forms/fields/file-upload#storing-files-on-a-custom-disk

### How to Verify Fix
```powershell
# 1. Check file is in correct location
Get-ChildItem c:\server\www\violet\storage\app\public\banners

# 2. Test URL is accessible
php artisan tinker --execute="echo url('storage/banners/filename.jpg');"
# Should output: http://violet.test/storage/banners/filename.jpg

# 3. Verify ImageColumn displays thumbnail in admin panel
# Navigate to /admin/banners or /admin/sliders
```

### Impact Assessment
- **Affected Features:** All file upload functionality (Sliders, Banners, future features)
- **Data Loss:** No data loss, only display issue
- **User Impact:** Admin users couldn't see uploaded images
- **Time to Resolution:** ~30 minutes

### Key Takeaway
> **When using Filament FileUpload for publicly accessible files (images, documents), ALWAYS explicitly specify `->disk('public')`. Never rely on default behavior.**

---

## Template for Future Entries

```markdown
## X. [Issue Title]

**Date:** YYYY-MM-DD  
**Severity:** 🔴 High / 🟡 Medium / 🟢 Low  
**Components:** Affected files/modules

### Problem Description
Brief description of the issue

### Symptoms
- What users/developers observed

### Root Cause
Technical explanation of what went wrong

### Incorrect Code
```php
// Bad example
```

### Correct Code
```php
// Fixed example
```

### Files Modified
1. File paths and changes

### Prevention Checklist
- [ ] Steps to prevent recurrence

### Related Documentation
- Links to docs/guides

### How to Verify Fix
Steps to confirm the issue is resolved

### Impact Assessment
- Affected features
- Data loss (if any)
- Time to resolution

### Key Takeaway
> Main lesson learned
```

---

**Document Version:** 1.0  
**Created:** November 12, 2025  
**Last Updated:** November 12, 2025  
**Project:** Violet - Content Management System
