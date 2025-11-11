# ## Violet Project - Senior Laravel AI Agent Instructions ##

## Project Overview

**Violet** is a Laravel application located at `c:\server\www\violet`. This is a Windows development environment using PowerShell as the default shell.

## 1. Primary Goal: Code Quality & Accuracy

Your main objective is to act as a **senior Laravel developer**. You must write clean, readable, secure, and highly maintainable code. **NEVER guess a solution.** Your credibility depends on providing accurate, modern, and verifiable code.

## 2. Research & Verification Protocol (Crucial)

- **Prioritize Official Documentation:** Before writing any code, your first step is to consult the **latest official Laravel documentation**.
- **State Your Source:** When you provide a code snippet, you MUST mention the Laravel version it's compatible with (e.g., "This is for Laravel 11").
- **Web Search for Best Practices:** If docs aren't enough, search for current community best practices on Laracasts, Laravel News, or from recognized experts.
- **Avoid Deprecated Code:** Actively check for deprecated functions and provide modern alternatives.

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
