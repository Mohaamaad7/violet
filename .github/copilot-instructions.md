# ## Violet Project - Senior Laravel AI Agent Instructions ##

## Project Overview

**Violet** is a Laravel application located at `c:\server\www\violet`. This is a Windows development environment using PowerShell as the default shell.

## 1. Primary Goal: Code Quality & Accuracy

Your main objective is to act as a **senior Laravel developer**. You must write clean, readable, secure, and highly maintainable code. **NEVER guess a solution.** Your credibility depends on providing accurate, modern, and verifiable code.

## 2. Research & Verification Protocol (Crucial)

### 🚨 CRITICAL RULE: ZERO TOLERANCE FOR GUESSING

> **يُمنع التخمين منعاً نهائياً طالما لدينا توثيق رسمي يمكن الرجوع إليه**
> 
> **NEVER GUESS. ALWAYS VERIFY WITH OFFICIAL DOCUMENTATION.**

**Mandatory Research Steps:**

1. ✅ **Official Documentation First (ALWAYS)**
   - **Laravel:** https://laravel.com/docs/11.x
   - **Filament v4:** https://filamentphp.com/docs/4.x (NOT v3!)
   - **Livewire v3:** https://livewire.laravel.com/docs
   - Read the EXACT version documentation - DO NOT assume compatibility

2. ✅ **Check Upgrade Guides for Breaking Changes**
   - Filament v3 → v4: https://filamentphp.com/docs/4.x/upgrade-guide
   - Laravel 11: https://laravel.com/docs/11.x/upgrade
   - **Example Critical Change:**
     ```php
     // ❌ Filament v3 (WRONG)
     use Filament\Tables\Actions\Action;
     
     // ✅ Filament v4 (CORRECT)
     use Filament\Actions\Action;
     ```

3. ✅ **Verify with IDE Autocomplete**
   - Use your IDE's autocomplete to confirm available classes/methods
   - If autocomplete doesn't show it, the class might not exist in that namespace

4. ✅ **Check Official GitHub Repository**
   - Search for usage examples in official repo
   - Read tests to understand correct usage
   - Check CHANGELOG.md for breaking changes

5. ❌ **NEVER DO THIS:**
   - ❌ Assume namespace based on "logical structure"
   - ❌ Copy code from old Stack Overflow answers (check dates!)
   - ❌ Use deprecated methods without verification
   - ❌ Guess parameter types or return types
   - ❌ Port v3 code to v4 without checking upgrade guide

**State Your Source:**
- When providing code, cite the documentation version
- Example: "This uses Filament v4 Actions (see: https://filamentphp.com/docs/4.x/actions)"

**Avoid Deprecated Code:**
- Actively check for `@deprecated` tags
- Always provide modern alternatives with documentation links

## 3. Core Development Principles

### Architecture & Code Quality
- **Service Layer Pattern**: Controllers handle HTTP concerns only. All business logic goes in dedicated Service classes in `app/Services/`.
- **Dependency Injection**: Use Laravel's container for DI. Avoid direct facade calls in business logic.
- **Form Requests**: ALL validation and authorization for POST/PUT/PATCH requests **MUST** use dedicated Form Request classes (`app/Http/Requests/`).
- **Single Responsibility**: Each class/method has one reason to change.

### Eloquent & Database
- **Eager Loading Required**: Always use `->with()` to prevent N+1 queries when loading relationships.
- **Route Model Binding**: Use implicit/explicit binding.
- **Eloquent First**: Prefer Eloquent ORM over Query Builder or raw SQL.

### Filament FileUpload Configuration (CRITICAL)
- **⚠️ ALWAYS add `->disk('public')` to FileUpload for publicly accessible files**
- Default disk is 'local' (`storage/app/`) which is NOT web-accessible
- For images/documents that need URLs, use 'public' disk (`storage/app/public/`)
- **Example:**
  ```php
  // ✅ CORRECT - For images in admin tables
  FileUpload::make('image_path')
      ->disk('public')        // REQUIRED for web-accessible files
      ->directory('products')
      ->image()
      ->maxSize(5120);
  
  // ❌ WRONG - Missing disk() will save to storage/app/ (not accessible)
  FileUpload::make('image_path')
      ->directory('products')  // Will use 'local' disk by default!
      ->image();
  ```
- **Reference:** See `docs/TROUBLESHOOTING_IMAGE_UPLOAD.md` for complete troubleshooting guide

### Code Style (PSR-12)
- Classes: `PascalCase`, Methods/Variables: `camelCase`, DB tables/columns: `snake_case`.

## 4. Development Workflows (Windows/PowerShell)

### Environment Prerequisites (Critical)

⚠️ **Before starting development, verify PHP configuration:**

```powershell
# Check upload_tmp_dir setting
php -i | Select-String "upload_tmp_dir"
```

**Required PHP Settings (`php.ini`):**
- `upload_tmp_dir` MUST be uncommented and set to valid path
- For Laragon: `upload_tmp_dir = C:\server\tmp`
- For XAMPP: `upload_tmp_dir = C:\xampp\tmp`
- **After changing php.ini, restart web server (Laragon/Apache)**

Common Issue: Commented `upload_tmp_dir` causes Livewire FileUpload to fail with "Path cannot be empty" error.

**Verification Steps:**
1. Check php.ini has uncommented `upload_tmp_dir` line
2. Verify directory exists and is writable
3. Run `php -i | Select-String "upload_tmp_dir"` to confirm active setting
4. Restart server after any php.ini changes

See `docs/TROUBLESHOOTING.md` for detailed diagnostics.

### Running Commands
```powershell
# Artisan
php artisan make:controller UserController --resource
php artisan make:model Post -mfsc

# Composer
composer install
composer require package/name

## 3.1 Route Verification & Validation (Critical)

All routes must be **verified, tested, and documented** before being merged into any branch.  
Failure to verify routes may cause system-wide crashes or unauthorized access.

### Route Validation Requirements

1. **List & Verify**
   - After adding or modifying routes, run:
     ```powershell
     php artisan route:list | Select-String "keyword"
     ```
     Ensure each new route is properly registered with the expected method (GET/POST/PUT/etc), middleware, and controller action.

2. **Namespace & Controller Consistency**
   - Verify that each route’s controller method exists and matches the correct namespace.
   - No route should reference deprecated or renamed controllers.

3. **Authorization & Middleware**
   - Confirm that each route is protected by the correct middleware (`auth`, `verified`, `can:*`, etc.).
   - Public routes must be explicitly justified in the PR description.

4. **Automated Tests**
   - Each route **must** have a corresponding **Feature Test**:
     - Test returns expected HTTP status (`200`, `302`, `403`, etc.)
     - Test correct redirect behavior and permission checks.
     - Example:
       ```php
       $this->get('/admin/translations')->assertStatus(200);
       $this->actingAs($user)->get('/admin/protected')->assertForbidden();
       ```
   - Run:
     ```powershell
     php artisan test --filter=Route
     ```

5. **Error Logging & Debug**
   - If a route triggers an exception (e.g., 404 or 500), use:
     ```powershell
     php artisan route:list --name=yourRouteName
     php artisan route:clear
     php artisan optimize:clear
     ```
     Then re-run the failing route test until resolved.

6. **CI Enforcement**
   - CI/CD pipelines must fail the build if:
     - Any route test fails.
     - `php artisan route:list` outputs undefined or missing controllers.

7. **Documentation**
   - Every new route must be documented in the module’s `README.md` or relevant section of `/docs/`.
   - Include route path, HTTP method, middleware, and description of its purpose.

✅ **Goal:** No untested or undocumented routes reach the main branch.
