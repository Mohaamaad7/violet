# Violet - Live Clean Deployment Playbook

> **Purpose**: Deploy a 100% clean production instance for testing Email notifications and Google OAuth login under real HTTPS/domain conditions.

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Database Setup](#database-setup)
3. [Minimal Seeding](#minimal-seeding)
4. [Storage Setup](#storage-setup)
5. [Super Admin Creation](#super-admin-creation)
6. [Verify Clean State](#verify-clean-state)
7. [SMTP Configuration](#smtp-configuration)
8. [Google OAuth Configuration](#google-oauth-configuration)
9. [Testing Checklists](#testing-checklists)
10. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Server Requirements
- PHP 8.2+
- Composer 2.x
- Node.js 18+ (for asset building)
- MySQL 8.0+ or PostgreSQL 14+
- Redis (optional, for caching/sessions)
- SSL certificate (required for Google OAuth)

### Domain Requirements
- Valid domain with HTTPS configured
- DNS properly pointing to server
- SSL certificate installed (Let's Encrypt recommended)

---

## Database Setup

### Step 1: Create Fresh Database

```bash
# MySQL
mysql -u root -p
CREATE DATABASE violet_live CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'violet_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON violet_live.* TO 'violet_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 2: Configure Environment

Create/update `.env` file on live server:

```env
APP_NAME="Violet Store"
APP_ENV=production
APP_KEY=  # Will be generated
APP_DEBUG=false
APP_URL=https://your-domain.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=violet_live
DB_USERNAME=violet_user
DB_PASSWORD=STRONG_PASSWORD_HERE

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=public
```

> **📝 Note on Drivers**: Using `database` for session/cache/queue is acceptable for initial testing and low-traffic scenarios. For **scalable production** deployments with higher traffic, consider migrating to **Redis** for better performance and reliability.

### Step 3: Generate Application Key

```bash
php artisan key:generate --force
```

### Step 4: Run Migrations

```bash
php artisan migrate --force
```

This creates all required tables in an **empty** state.

---

## Minimal Seeding

> ⚠️ **CRITICAL**: The `CleanDeploySeeder` seeds **SYSTEM DATA ONLY**:
> - Roles & Permissions (required for Spatie)
> - One Super Admin user
> 
> **ZERO demo/business content** (no products, categories, orders, etc.)

### Option A: Using the Clean Deploy Seeder (Recommended)

Run the minimal seeder that creates ONLY:
- Roles & Permissions
- One Super Admin user

```bash
php artisan db:seed --class=CleanDeploySeeder --force
```

### Option B: Individual Seeders

If you prefer granular control:

```bash
# 1. Seed roles and permissions only
php artisan db:seed --class=RolesAndPermissionsSeeder --force

# 2. Create Super Admin via Tinker (see next section)
```

### What Gets Seeded

| Seeded ✅ | NOT Seeded ❌ |
|-----------|---------------|
| Roles (super-admin, admin, manager, etc.) | Categories |
| Permissions (70+ permissions) | Products |
| Super Admin user | Orders |
| | Wishlists |
| | Reviews |
| | Shipping Addresses |
| | Sliders/Banners |
| | Blog Posts |
| | Discount Codes |

---

## Storage Setup

### Create Storage Symlink

```bash
php artisan storage:link
```

### Set Permissions (Linux)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Verify Storage Access

```bash
# Test write access
echo "test" > storage/app/public/test.txt
curl https://your-domain.com/storage/test.txt
rm storage/app/public/test.txt
```

---

## Super Admin Creation

### Option A: Via Tinker (Interactive)

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::create([
    'name' => 'Super Admin',
    'email' => 'admin@your-domain.com',  // Use your real email!
    'password' => Hash::make('YourSecurePassword123!'),
    'phone' => '+1234567890',
    'type' => 'admin',
    'status' => 'active',
    'email_verified_at' => now(),
]);

$admin->assignRole('super-admin');

echo "Super Admin created with ID: " . $admin->id;
exit;
```

### Option B: Via Artisan Command

```bash
# If CleanDeploySeeder was run, Super Admin already exists:
# Email: admin@violet.com
# Password: ChangeThisPassword!
#
# ⚠️ IMMEDIATELY change this password after first login!
```

### Verify Admin Access

1. Navigate to: `https://your-domain.com/admin`
2. Login with Super Admin credentials
3. Verify you see empty dashboard with no products/orders

---

## Verify Clean State

### Quick Verification Script

Run this in Tinker to confirm all business tables are empty:

```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\DB;

$tables = [
    'categories',
    'products', 
    'product_images',
    'product_variants',
    'product_reviews',
    'orders',
    'order_items',
    'carts',
    'cart_items',
    'wishlists',
    'shipping_addresses',
    'sliders',
    'banners',
    'blog_posts',
    'discount_codes',
    'influencers',
    'influencer_applications',
];

echo "=== Content Tables Status ===\n";
$allEmpty = true;

foreach ($tables as $table) {
    $count = DB::table($table)->count();
    $status = $count === 0 ? '✅ Empty' : "❌ Has {$count} records";
    echo "{$table}: {$status}\n";
    if ($count > 0) $allEmpty = false;
}

echo "\n=== System Tables Status ===\n";
echo "roles: " . DB::table('roles')->count() . " records\n";
echo "permissions: " . DB::table('permissions')->count() . " records\n";
echo "users (admin): " . DB::table('users')->where('type', 'admin')->count() . " records\n";

echo "\n" . ($allEmpty ? "✅ CLEAN DEPLOY VERIFIED!" : "❌ SOME TABLES HAVE DATA!") . "\n";
exit;
```

### Expected Output

```
=== Content Tables Status ===
categories: ✅ Empty
products: ✅ Empty
product_images: ✅ Empty
...all should be Empty...

=== System Tables Status ===
roles: 7 records
permissions: 24 records
users (admin): 1 records

✅ CLEAN DEPLOY VERIFIED!
```

---

## SMTP Configuration

### Step 1: Choose Email Provider

| Provider | Best For | Cost |
|----------|----------|------|
| **Mailgun** | Transactional emails | Free tier: 5,000/month |
| **Amazon SES** | High volume, low cost | $0.10/1,000 emails |
| **Postmark** | Deliverability focus | Free trial + paid |
| **SendGrid** | Marketing + transactional | Free tier: 100/day |
| **Resend** | Modern API, good DX | Free tier: 3,000/month |

### Step 2: Configure .env for SMTP

#### Mailgun Example

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@mg.your-domain.com
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Amazon SES Example

```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Resend Example

```env
MAIL_MAILER=resend
RESEND_API_KEY=re_xxxxxxxxxxxx
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 3: Verify Email Configuration

```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

Mail::raw('Test email from Violet deployment', function (Message $message) {
    $message->to('your-email@example.com')
            ->subject('Violet SMTP Test');
});

echo "Email sent! Check your inbox.";
exit;
```

### Step 4: Test Email Flows

| Flow | How to Test |
|------|-------------|
| **Password Reset** | Go to login → "Forgot Password" → Enter admin email |
| **Email Verification** | Register new customer → Check for verification email |
| **Order Confirmation** | (Requires product setup - skip for clean deploy) |

---

## Google OAuth Configuration

### Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create new project: "Violet Store Production"
3. Navigate to **APIs & Services** → **Credentials**

### Step 2: Configure OAuth Consent Screen

1. Go to **OAuth consent screen**
2. Select **External** (for public users)
3. Fill required fields:
   - App name: "Violet Store"
   - User support email: admin@your-domain.com
   - Developer contact: admin@your-domain.com
4. Add scopes:
   - `email`
   - `profile`
   - `openid`
5. Save and continue

### Step 3: Create OAuth Credentials

1. Go to **Credentials** → **Create Credentials** → **OAuth client ID**
2. Application type: **Web application**
3. Name: "Violet Web Client"
4. Add Authorized JavaScript origins:
   ```
   https://your-domain.com
   ```
5. Add Authorized redirect URIs:
   ```
   https://your-domain.com/auth/google/callback
   ```
6. Click **Create**
7. Copy the **Client ID** and **Client Secret**

### Step 4: Configure .env

```env
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=https://your-domain.com/auth/google/callback
```

### Step 5: Socialite Package

> **Note**: `laravel/socialite` must be added to the project via a PR to the repository.
> Do NOT run `composer require` directly on production servers.
> Ensure the package is already included in `composer.json` before deployment.

### Step 6: Configure services.php

Add to `config/services.php`:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
```

### Step 7: OAuth Controller Reference

> **📋 Schema-Dependent Example**: The controller below aligns with Violet's `users` table schema.
> Role assignment via `assignRole('customer')` is the source of truth for user permissions.
> Adjust fields if your schema differs.

The `GoogleController` is already included in the repository at `app/Http/Controllers/Auth/GoogleController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if ($user) {
                // Existing user - login
                Auth::login($user, remember: true);
            } else {
                // New user - create with Violet's users table schema
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                    'phone' => null,                    // Optional field
                    'profile_photo_path' => null,       // Optional field
                    'type' => 'customer',               // User type
                    'status' => 'active',               // Account status
                    'locale' => config('app.locale'),   // User language preference
                    'email_verified_at' => now(),       // Google emails are pre-verified
                ]);
                
                // Role assignment is the source of truth for permissions
                $user->assignRole('customer');
                Auth::login($user, remember: true);
            }
            
            return redirect()->intended('/');
            
        } catch (\Exception $e) {
            Log::error('Google OAuth failed', ['error' => $e->getMessage()]);
            return redirect('/login')->with('error', 'Google login failed. Please try again.');
        }
    }
}
```

### Step 8: Add Routes

Add to `routes/auth.php`:

```php
use App\Http\Controllers\Auth\GoogleController;

// Google OAuth Routes
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
```

### Step 9: Add Login Button

Add to your login Blade view:

```blade
<a href="{{ route('auth.google') }}" 
   class="flex items-center justify-center gap-2 px-4 py-2 border rounded-lg hover:bg-gray-50">
    <svg class="w-5 h-5" viewBox="0 0 24 24">
        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
    </svg>
    <span>Continue with Google</span>
</a>
```

---

## Testing Checklists

### ✅ Email Flow Tests

| Test | Steps | Expected Result |
|------|-------|-----------------|
| **Password Reset** | 1. Go to /login<br>2. Click "Forgot Password"<br>3. Enter Super Admin email<br>4. Check inbox | Email received with reset link |
| **Email Verification** | 1. Register new customer account<br>2. Don't verify immediately<br>3. Check inbox | Verification email received |
| **Resend Verification** | 1. Login as unverified user<br>2. Click "Resend" button | New verification email sent |

### ✅ Google OAuth Tests

| Test | Steps | Expected Result |
|------|-------|-----------------|
| **New User Login** | 1. Go to /login<br>2. Click "Continue with Google"<br>3. Select Google account | New user created, logged in, redirected to home |
| **Existing User Login** | 1. Register email manually<br>2. Logout<br>3. Login with Google (same email) | Existing user logged in |
| **Cancel Flow** | 1. Click "Continue with Google"<br>2. Cancel on Google screen | Returned to login with error message |

### ✅ Clean State Verification

| Check | How | Expected |
|-------|-----|----------|
| Admin Dashboard | Login to /admin | Shows 0 products, 0 orders |
| Products Page | Visit /products | Shows empty state or "No products" |
| Categories | Check admin categories | Empty list |
| Homepage | Visit / | Works but no featured products |

---

## Troubleshooting

### Email Issues

**Problem**: Emails not sending  
**Solution**:
```bash
# Check mail configuration
php artisan tinker
>>> config('mail')

# Test connection
php artisan queue:work --once  # If using queued emails
```

**Problem**: Emails going to spam  
**Solution**:
- Verify SPF/DKIM records for your domain
- Use a reputable email service provider
- Ensure FROM address matches domain

### Google OAuth Issues

**Problem**: "redirect_uri_mismatch"  
**Solution**:
- Ensure redirect URI in Google Console **exactly** matches `GOOGLE_REDIRECT_URI`
- Include `https://` prefix
- No trailing slash unless specified in Console

**Problem**: "Access blocked: App not verified"  
**Solution**:
- For testing: Add your email as test user in OAuth consent screen
- For production: Submit app for verification

**Problem**: "User cancelled login"  
**Solution**: This is expected when user clicks cancel - ensure graceful error handling

### Database Issues

**Problem**: Tables have data after seeding  
**Solution**:
```bash
# Fresh migration (DESTROYS ALL DATA)
php artisan migrate:fresh --force

# Then run only minimal seeder
php artisan db:seed --class=CleanDeploySeeder --force
```

---

## Quick Reference Commands

```bash
# Full clean deployment sequence
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --class=CleanDeploySeeder --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear all caches
php artisan optimize:clear

# Verify routes
php artisan route:list | grep -E "(google|auth)"

# Test email
php artisan tinker --execute="Mail::raw('Test', fn(\$m) => \$m->to('test@example.com'));"
```

---

## Security Reminders

1. ⚠️ **Change default admin password immediately after first login**
2. ⚠️ **Never commit `.env` file to version control**
3. ⚠️ **Use strong, unique passwords for database**
4. ⚠️ **Keep `APP_DEBUG=false` in production**
5. ⚠️ **Regularly rotate OAuth client secrets**

---

*Last updated: December 4, 2025*
