# Image Upload Troubleshooting Guide

## Problem: Image thumbnails not appearing in Filament ImageColumn

### Symptom
When using `FileUpload` in Filament resources (SliderResource, BannerResource), files appear to upload successfully, but the `ImageColumn` in the table list shows a missing/broken image icon.

---

## Root Causes & Solutions

### 1. ❌ **CRITICAL: `public/storage` is a directory instead of a symlink**

**How to identify:**
```powershell
Get-Item "c:\server\www\violet\public\storage" | Select-Object LinkType, Target
```

Expected output:
```
LinkType Target
-------- ------
Junction {C:\server\www\violet\storage\app\public}
```

If you see `LinkType: (empty)` or the directory exists but has no Target, the symlink is broken.

**Solution:**
```powershell
# Remove the existing directory
Remove-Item "c:\server\www\violet\public\storage" -Recurse -Force

# Recreate the symbolic link
php artisan storage:link
```

**Verification:**
```powershell
# Should show Junction/SymbolicLink with target pointing to storage/app/public
Get-Item "c:\server\www\violet\public\storage" | Select-Object LinkType, Target
```

---

### 2. ⚠️ **APP_URL mismatch with actual access URL**

**Problem:** 
If `APP_URL` in `.env` is set to `http://localhost` but you access the site via `http://violet.test`, the browser will try to load images from the wrong domain.

**How to identify:**
```bash
# Check .env file
grep APP_URL .env

# In PowerShell
Select-String -Path .env -Pattern "APP_URL"
```

**Solution:**
Update `.env` to match your actual access URL:
```env
# Before
APP_URL=http://localhost

# After
APP_URL=http://violet.test
```

Then clear config cache:
```bash
php artisan config:clear
php artisan optimize:clear
```

---

### 3. 🔧 **Missing storage directories**

**Problem:**
The `sliders/` and `banners/` directories don't exist in `storage/app/public/`.

**Solution:**
Filament's `FileUpload` should create these automatically, but you can create them manually:
```powershell
New-Item -ItemType Directory -Path "c:\server\www\violet\storage\app\public\sliders" -Force
New-Item -ItemType Directory -Path "c:\server\www\violet\storage\app\public\banners" -Force
```

**Verification:**
```powershell
Test-Path "c:\server\www\violet\storage\app\public\sliders"
Test-Path "c:\server\www\violet\storage\app\public\banners"
```

Both should return `True`.

---

### 4. 📝 **PHP upload_tmp_dir configuration (Less Common)**

**Problem:**
The `upload_tmp_dir` in `php.ini` is commented out or points to a non-existent directory.

**How to identify:**
```bash
php -i | Select-String "upload_tmp_dir"
```

Expected output (for XAMPP):
```
upload_tmp_dir => C:\xampp\tmp => C:\xampp\tmp
```

Expected output (for Laragon):
```
upload_tmp_dir => C:\server\tmp => C:\server\tmp
```

**Solution:**
Edit `php.ini` and uncomment/set the correct path:

For XAMPP (`C:\xampp\php\php.ini`):
```ini
upload_tmp_dir = C:\xampp\tmp
```

For Laragon (`C:\laragon\bin\php\phpX.X.X\php.ini`):
```ini
upload_tmp_dir = C:\server\tmp
```

**IMPORTANT:** Restart your web server after editing `php.ini`.

---

### 5. 🔍 **File was never uploaded (Database has path but file doesn't exist)**

**How to identify:**
```bash
php artisan tinker
```

Then in Tinker:
```php
$slider = App\Models\Slider::first();
echo $slider->image_path; // Shows: sliders/01K9WXQPNASBZSAFQDMVBR8KNP.png

// Check if file exists
$fullPath = storage_path('app/public/' . $slider->image_path);
echo file_exists($fullPath) ? 'EXISTS' : 'MISSING';
```

If it shows `MISSING`, the upload failed but the record was saved anyway.

**Solution:**
1. Delete the broken record:
   ```php
   App\Models\Slider::where('image_path', 'sliders/01K9WXQPNASBZSAFQDMVBR8KNP.png')->delete();
   ```

2. Fix the root cause (usually #1, #2, or #4 above)

3. Re-upload the image through the admin panel

---

### 6. 🚨 **MOST COMMON: Missing `disk('public')` in FileUpload** ⭐

**Problem:** 
`FileUpload` component **defaults to 'local' disk** (`storage/app/`) instead of 'public' disk (`storage/app/public/`). This causes files to be saved in the wrong location, making them inaccessible via the `/storage/` URL.

**Symptoms:**
- ✅ Upload succeeds without errors
- ✅ Database record created with correct `image_path` (e.g., `banners/xyz.jpg`)
- ❌ File saved to `storage/app/banners/` instead of `storage/app/public/banners/`
- ❌ ImageColumn shows broken image (file not accessible via `/storage/banners/xyz.jpg`)

**How to identify:**
```powershell
# Check if file is in wrong location (storage/app instead of storage/app/public)
Get-ChildItem c:\server\www\violet\storage\app\banners -ErrorAction SilentlyContinue
Get-ChildItem c:\server\www\violet\storage\app\sliders -ErrorAction SilentlyContinue

# If these directories exist, files are being saved to the wrong disk!
```

**Root Cause - Missing disk() method:**
```php
// ❌ WRONG - Uses 'local' disk by default
FileUpload::make('image_path')
    ->directory('banners')
    ->image()
    ->required();

// ✅ CORRECT - Explicitly specify 'public' disk
FileUpload::make('image_path')
    ->disk('public')        // THIS LINE IS CRITICAL!
    ->directory('banners')
    ->image()
    ->required();
```

**Solution:**
Update all FileUpload components to explicitly specify `->disk('public')`:

**Example Fix for BannerForm.php:**
```php
FileUpload::make('image_path')
    ->label('Image')
    ->image()
    ->required()
    ->disk('public')           // ⭐ Add this line
    ->directory('banners')
    ->maxSize(5120)
    ->imageEditor()
    ->helperText('Upload banner image. Max 5MB.')
    ->columnSpanFull();
```

**Example Fix for SliderForm.php:**
```php
FileUpload::make('image_path')
    ->label('Image')
    ->image()
    ->required()
    ->disk('public')           // ⭐ Add this line
    ->directory('sliders')
    ->maxSize(5120)
    ->imageEditor()
    ->helperText('Upload slider image. Max 5MB. Recommended: 1920x800px')
    ->columnSpanFull();
```

**After fixing, clear cache:**
```bash
php artisan optimize:clear
```

**Clean up old records with missing files:**
```bash
php artisan tinker --execute="App\Models\Banner::truncate();"
php artisan tinker --execute="App\Models\Slider::truncate();"
```

**Verification:**
1. Create a new banner/slider with image upload
2. Check file location:
   ```powershell
   Get-ChildItem c:\server\www\violet\storage\app\public\banners
   ```
3. Verify ImageColumn displays thumbnail correctly in admin table

**Why this happens:**
- Filament v4 FileUpload uses Laravel's default disk ('local') unless explicitly specified
- The 'local' disk points to `storage/app/`
- The 'public' disk points to `storage/app/public/` (accessible via `/storage/` URL)
- ImageColumn generates URLs like `/storage/banners/file.jpg`, which only work if files are in 'public' disk

**Prevention:**
- ✅ **ALWAYS add `->disk('public')` to FileUpload** when files need to be publicly accessible
- ✅ Add this to code review checklist
- ✅ Document in team guidelines

**Reference:**
- Filament FileUpload docs: https://filamentphp.com/docs/4.x/forms/fields/file-upload#storing-files-on-a-custom-disk
- Laravel Filesystem docs: https://laravel.com/docs/11.x/filesystem#the-public-disk

---

## Complete Diagnostic Script

Run this script to check all configurations at once:
```bash
php c:\server\www\violet\check_upload_config.php
```

**Expected output:**
```
=== PHP Upload Configuration ===
upload_tmp_dir: C:\xampp\tmp (or C:\server\tmp)
upload_max_filesize: 40M
post_max_size: 40M
file_uploads: Enabled

=== Directory Permissions ===
✅ PHP upload_tmp_dir exists
✅ Livewire tmp exists
✅ Public storage exists
✅ Sliders directory exists
✅ Banners directory exists

=== Storage Link ===
✅ public/storage exists
   Type: Symbolic Link (or Junction)
   Target: C:\server\www\violet\storage\app/public

=== APP_URL Configuration ===
APP_URL: http://violet.test
```

---

## Testing Image Upload After Fixes

1. Navigate to `/admin/sliders` or `/admin/banners`
2. Click "Create"
3. Fill in the form and upload an image (max 5MB)
4. Click "Save"
5. Verify the image appears in the list table

If the image still doesn't appear:
1. Right-click the broken image → "Inspect Element"
2. Check the `src` attribute of the `<img>` tag
3. Copy the URL and try accessing it directly in the browser
4. Check for 404 errors or permission issues

---

## Prevention Checklist

✅ **ALWAYS add `->disk('public')` to FileUpload components** (Most common issue!)  
✅ Always run `php artisan storage:link` after cloning the project  
✅ Verify `APP_URL` matches your local domain before testing uploads  
✅ Check `php.ini` has `upload_tmp_dir` uncommented  
✅ Ensure storage directories have write permissions (755 or 777)  
✅ Clear config cache after changing `.env`: `php artisan config:clear`  
✅ Test image upload immediately after creating new resources with FileUpload

## Quick Reference: FileUpload Best Practices

```php
// ✅ CORRECT - For publicly accessible files (images, documents)
FileUpload::make('image_path')
    ->disk('public')        // Files go to storage/app/public/
    ->directory('banners')  // Final path: storage/app/public/banners/
    ->image()
    ->maxSize(5120);

// ✅ CORRECT - For private files (user documents, invoices)
FileUpload::make('document_path')
    ->disk('local')         // Files go to storage/app/
    ->directory('private')  // Final path: storage/app/private/
    ->acceptedFileTypes(['application/pdf']);

// ❌ WRONG - Missing disk() for public files
FileUpload::make('image_path')
    ->directory('banners')  // Will use 'local' disk by default!
    ->image();
```

---

## References

- **Laravel Storage:** https://laravel.com/docs/11.x/filesystem#the-public-disk
- **Filament FileUpload:** https://filamentphp.com/docs/4.x/forms/fields/file-upload
- **Livewire File Uploads:** https://livewire.laravel.com/docs/uploads

---

## Summary: Troubleshooting Steps Priority

When images don't appear in Filament tables, check in this order:

1. **🔴 Check #6 first** - Missing `->disk('public')` in FileUpload (90% of cases)
2. **🟡 Check #1** - Broken `public/storage` symlink
3. **🟡 Check #2** - APP_URL mismatch
4. **🟢 Check #3** - Missing storage directories (auto-created usually)
5. **🟢 Check #4** - PHP upload_tmp_dir config (rare)
6. **🟢 Check #5** - Clean up orphaned database records

---

**Document Version:** 1.1  
**Created:** November 12, 2025  
**Last Updated:** November 12, 2025 (Added Section #6: Missing disk('public'))  
**Project:** Violet - Content Management System
