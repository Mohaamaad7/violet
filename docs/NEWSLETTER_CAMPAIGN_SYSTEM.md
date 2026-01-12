# 📧 Newsletter & Email Campaign System - Technical Documentation

## 📋 Table of Contents
1. [System Overview](#system-overview)
2. [Database Schema](#database-schema)
3. [Filament v4 Compatibility](#filament-v4-compatibility)
4. [Architecture](#architecture)
5. [Implementation Plan](#implementation-plan)
6. [Email Templates](#email-templates)
7. [Queue System](#queue-system)
8. [Testing Strategy](#testing-strategy)

---

## 🎯 System Overview

### Purpose
Complete Newsletter subscription and Email Campaign management system that allows:
- ✅ Customer subscription via Frontend (Footer, Contact page)
- ✅ Admin management in Filament v4 dashboard
- ✅ Bulk email campaigns (Offers OR Custom messages)
- ✅ Queue-based email sending
- ✅ Campaign tracking and statistics

### Tech Stack
- **Laravel**: 12.41.1
- **PHP**: 8.3.28
- **Filament**: v4.2 ⚠️ **CRITICAL: NOT v3!**
- **Livewire**: v3
- **Database**: MySQL/MariaDB
- **Queue Driver**: Database (can be Redis/Horizon later)
- **Mail Driver**: SMTP (Gmail/SendGrid/SES)

---

## 🗄️ Database Schema

### Current Database Structure (Relevant Tables)

Based on existing migrations in `database/migrations/`:

#### 1. **customers** (Existing)
```sql
CREATE TABLE customers (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(255) NULL,
    profile_photo_path VARCHAR(255) NULL,
    status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    locale VARCHAR(5) DEFAULT 'ar',
    total_orders INT UNSIGNED DEFAULT 0,
    total_spent DECIMAL(12,2) DEFAULT 0,
    last_order_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_phone (phone),
    INDEX idx_created_at (created_at)
);
```

#### 2. **products** (Existing)
```sql
CREATE TABLE products (
    id BIGINT UNSIGNED PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    description TEXT NULL,
    short_description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    cost_price DECIMAL(10,2) NULL,
    stock INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 5,
    weight DECIMAL(8,2) NULL,
    brand VARCHAR(100) NULL,
    barcode VARCHAR(100) NULL,
    status ENUM('draft', 'active', 'inactive') DEFAULT 'active',
    is_featured BOOLEAN DEFAULT FALSE,
    views_count INT DEFAULT 0,
    sales_count INT DEFAULT 0,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    meta_keywords TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category_id (category_id),
    INDEX idx_slug (slug),
    INDEX idx_sku (sku),
    INDEX idx_status (status),
    INDEX idx_is_featured (is_featured)
);
```

#### 3. **discount_codes** (Existing - for Offers)
```sql
CREATE TABLE discount_codes (
    id BIGINT UNSIGNED PRIMARY KEY,
    influencer_id BIGINT UNSIGNED NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('influencer', 'general', 'campaign') DEFAULT 'influencer',
    discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    max_discount_amount DECIMAL(10,2) NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    commission_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    commission_value DECIMAL(10,2) NOT NULL,
    usage_limit INT NULL,
    usage_limit_per_user INT DEFAULT 1,
    times_used INT DEFAULT 0,
    starts_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    applies_to_categories JSON NULL,
    applies_to_products JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (influencer_id) REFERENCES influencers(id) ON DELETE CASCADE,
    INDEX idx_code (code),
    INDEX idx_influencer_id (influencer_id),
    INDEX idx_is_active (is_active)
);
```

---

### 🆕 New Tables to Create

#### 4. **newsletter_subscriptions** (NEW)
```sql
CREATE TABLE newsletter_subscriptions (
    id BIGINT UNSIGNED PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    customer_id BIGINT UNSIGNED NULL, -- Link to customers if registered
    status ENUM('active', 'unsubscribed', 'bounced') DEFAULT 'active',
    source VARCHAR(50) NULL, -- 'footer', 'contact', 'popup', 'checkout'
    ip_address VARCHAR(45) NULL, -- IPv4/IPv6
    user_agent TEXT NULL,
    subscribed_at TIMESTAMP NULL,
    unsubscribed_at TIMESTAMP NULL,
    unsubscribe_reason TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_customer_id (customer_id),
    INDEX idx_subscribed_at (subscribed_at)
);
```

**Why these fields?**
- `customer_id`: Link to registered customers for better insights
- `source`: Track where subscriptions come from (analytics)
- `ip_address` + `user_agent`: Security & fraud prevention
- `unsubscribe_reason`: Understand why people leave (improve service)

---

#### 5. **email_campaigns** (NEW)
```sql
CREATE TABLE email_campaigns (
    id BIGINT UNSIGNED PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    type ENUM('offers', 'custom', 'newsletter') DEFAULT 'custom',
    subject VARCHAR(255) NOT NULL,
    preview_text VARCHAR(255) NULL, -- Email preview text
    content_html LONGTEXT NULL, -- Full HTML email body
    content_json JSON NULL, -- Store structured content (for future editors)
    status ENUM('draft', 'scheduled', 'sending', 'sent', 'paused', 'cancelled') DEFAULT 'draft',
    
    -- Targeting
    send_to ENUM('all', 'active_only', 'recent', 'custom') DEFAULT 'active_only',
    custom_filters JSON NULL, -- Store complex filters
    
    -- Statistics
    recipients_count INT DEFAULT 0,
    emails_sent INT DEFAULT 0,
    emails_failed INT DEFAULT 0,
    emails_bounced INT DEFAULT 0,
    emails_opened INT DEFAULT 0, -- Optional (requires tracking pixel)
    emails_clicked INT DEFAULT 0, -- Optional (requires link tracking)
    
    -- Timing
    scheduled_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    
    -- Settings
    send_rate_limit INT DEFAULT 50, -- Emails per minute (avoid SPAM filters)
    
    created_by BIGINT UNSIGNED NULL, -- Admin user who created
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_created_by (created_by),
    INDEX idx_scheduled_at (scheduled_at)
);
```

**Important Design Decisions:**
- `content_html`: Full flexibility for custom HTML emails
- `content_json`: Future-proof for visual email builders (like Unlayer)
- `send_rate_limit`: Prevent SPAM filters (Gmail: 100/day, SendGrid: varies)
- Statistics columns: Track performance WITHOUT external services

---

#### 6. **campaign_offers** (NEW - Pivot Table)
```sql
CREATE TABLE campaign_offers (
    id BIGINT UNSIGNED PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    offer_id BIGINT UNSIGNED NOT NULL, -- Links to discount_codes
    display_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (campaign_id) REFERENCES email_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (offer_id) REFERENCES discount_codes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_campaign_offer (campaign_id, offer_id),
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_offer_id (offer_id)
);
```

**Usage:**
When sending "Offers" type campaign, admin selects multiple `discount_codes` to include.

---

#### 7. **campaign_logs** (NEW - Detailed Tracking)
```sql
CREATE TABLE campaign_logs (
    id BIGINT UNSIGNED PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    subscriber_id BIGINT UNSIGNED NOT NULL,
    status ENUM('queued', 'sending', 'sent', 'failed', 'bounced') DEFAULT 'queued',
    error_message TEXT NULL,
    sent_at TIMESTAMP NULL,
    opened_at TIMESTAMP NULL, -- Optional tracking
    clicked_at TIMESTAMP NULL, -- Optional tracking
    unsubscribed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (campaign_id) REFERENCES email_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (subscriber_id) REFERENCES newsletter_subscriptions(id) ON DELETE CASCADE,
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_subscriber_id (subscriber_id),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
);
```

**Purpose:**
- Audit trail for every email sent
- Debug failed emails
- Calculate campaign statistics
- Identify problematic email addresses (bounces)

---

## ⚠️ Filament v4 Compatibility (CRITICAL!)

### Current Project Uses Filament v4.2

**From `composer.json`:**
```json
{
    "require": {
        "filament/filament": "^4.2",
        "filament/spatie-laravel-media-library-plugin": "^4.2",
        "filament/tables": "^4.2"
    }
}
```

### 🚨 Breaking Changes from Filament v3 → v4

#### ❌ WRONG (Filament v3 Code)
```php
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Tables\Actions\Action; // ❌ WRONG NAMESPACE!

public static function form(Form $form): Form
{
    return $form->schema([...]);
}

public static function table(Table $table): Table
{
    return $table->columns([...]);
}
```

#### ✅ CORRECT (Filament v4 Code)
```php
use Filament\Schemas\Schema; // ✅ NEW in v4
use Filament\Tables\Table;
use Filament\Actions\Action; // ✅ CORRECT NAMESPACE

public static function form(Schema $schema): Schema
{
    return $schema->schema([...]); // Note: schema() method
}

public static function table(Table $table): Table
{
    return $table->columns([...]);
}
```

### Key Namespace Changes in Filament v4

| Component | Filament v3 (❌) | Filament v4 (✅) |
|-----------|------------------|------------------|
| **Forms** | `Filament\Resources\Form` | `Filament\Schemas\Schema` |
| **Form Components** | `Filament\Forms\Components\*` | `Filament\Schemas\Components\*` |
| **Actions** | `Filament\Tables\Actions\Action` | `Filament\Actions\Action` |
| **Bulk Actions** | `Filament\Tables\Actions\BulkAction` | `Filament\Actions\BulkActionGroup` |
| **Pages** | Same | Same (no change) |
| **Widgets** | Same | Same (no change) |

### Example from Current Project

**File:** `app/Filament/Resources/CategoryResource.php` (Existing Code)
```php
<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema; // ✅ v4
use Filament\Schemas\Components; // ✅ v4
use Filament\Tables\Table; // ✅ Correct
use Filament\Actions; // ✅ v4

class CategoryResource extends Resource
{
    public static function form(Schema $schema): Schema // ✅ v4 syntax
    {
        return $schema->schema([
            // Components...
        ]);
    }
}
```

**File:** `app/Filament/Resources/Orders/OrderResource.php` (Existing Code)
```php
use Filament\Actions\Action; // ✅ Correct v4 namespace
use Filament\Resources\Resource;
use Filament\Schemas\Schema; // ✅ v4
use Filament\Tables\Table;
```

### ⚡ Action Buttons in v4

```php
// ✅ CORRECT Filament v4 Syntax
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

Actions::make([
    Action::make('export')
        ->label('Export Campaign')
        ->icon('heroicon-o-arrow-down-tray')
        ->action(fn () => /* ... */),
        
    Action::make('send')
        ->label('Send Campaign')
        ->requiresConfirmation()
        ->action(fn (EmailCampaign $record) => /* ... */),
]),

BulkActionGroup::make([
    DeleteBulkAction::make(),
])
```

---

## 🏗️ Architecture

### Directory Structure

```
app/
├── Models/
│   ├── NewsletterSubscription.php       (NEW)
│   ├── EmailCampaign.php                (NEW)
│   ├── CampaignOffer.php                (NEW)
│   └── CampaignLog.php                  (NEW)
│
├── Livewire/Store/
│   └── NewsletterSubscription.php       (NEW - Frontend Component)
│
├── Filament/Resources/
│   ├── Newsletter/
│   │   ├── NewsletterSubscriptionResource.php (NEW)
│   │   ├── Pages/
│   │   │   ├── ListSubscriptions.php
│   │   │   └── ViewSubscription.php
│   │   └── Widgets/
│   │       ├── SubscriberStatsWidget.php
│   │       └── SubscriptionTrendWidget.php
│   │
│   └── EmailCampaigns/
│       ├── EmailCampaignResource.php    (NEW)
│       ├── Pages/
│       │   ├── ListCampaigns.php
│       │   ├── CreateCampaign.php
│       │   ├── EditCampaign.php
│       │   └── ViewCampaign.php
│       └── Widgets/
│           └── CampaignStatsWidget.php
│
├── Jobs/
│   ├── SendCampaignEmail.php            (NEW - Queue Job)
│   └── ProcessEmailCampaign.php         (NEW - Main Campaign Job)
│
├── Mail/
│   ├── CampaignMail.php                 (NEW - Mailable)
│   └── WelcomeSubscriber.php            (NEW - Optional)
│
├── Services/
│   ├── CampaignService.php              (NEW - Business Logic)
│   └── NewsletterService.php            (NEW - Subscription Logic)
│
└── Http/
    └── Controllers/
        └── NewsletterController.php     (NEW - Handle unsubscribe links)

resources/
└── views/
    ├── livewire/
    │   └── store/
    │       └── newsletter-subscription.blade.php (NEW)
    │
    └── emails/
        ├── campaign.blade.php           (NEW - Main template)
        ├── campaign-offers.blade.php    (NEW - Offers template)
        └── layouts/
            └── email.blade.php          (NEW - Email layout)

database/
└── migrations/
    ├── 2026_01_12_100000_create_newsletter_subscriptions_table.php (NEW)
    ├── 2026_01_12_100001_create_email_campaigns_table.php (NEW)
    ├── 2026_01_12_100002_create_campaign_offers_table.php (NEW)
    └── 2026_01_12_100003_create_campaign_logs_table.php (NEW)
```

---

## 📝 Implementation Plan

### Phase 1: Database & Models (Priority: HIGH)

#### Step 1.1: Create Migrations
```bash
php artisan make:migration create_newsletter_subscriptions_table
php artisan make:migration create_email_campaigns_table
php artisan make:migration create_campaign_offers_table
php artisan make:migration create_campaign_logs_table
```

#### Step 1.2: Create Models with Relationships
```php
// app/Models/NewsletterSubscription.php
class NewsletterSubscription extends Model
{
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function campaignLogs(): HasMany
    {
        return $this->hasMany(CampaignLog::class, 'subscriber_id');
    }
    
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }
}

// app/Models/EmailCampaign.php
class EmailCampaign extends Model
{
    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(DiscountCode::class, 'campaign_offers', 'campaign_id', 'offer_id')
            ->withPivot('display_order')
            ->orderBy('campaign_offers.display_order');
    }
    
    public function logs(): HasMany
    {
        return $this->hasMany(CampaignLog::class, 'campaign_id');
    }
    
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
```

---

### Phase 2: Frontend Subscription (Livewire)

#### Step 2.1: Create Livewire Component
```bash
php artisan make:livewire Store/NewsletterSubscription
```

#### Step 2.2: Component Logic
```php
// app/Livewire/Store/NewsletterSubscription.php
namespace App\Livewire\Store;

use App\Models\NewsletterSubscription as Subscription;
use Livewire\Component;
use Livewire\Attributes\Validate;

class NewsletterSubscription extends Component
{
    #[Validate('required|email|unique:newsletter_subscriptions,email')]
    public string $email = '';
    
    public bool $loading = false;
    public ?string $message = null;
    public ?string $messageType = null;
    
    public function subscribe()
    {
        $this->loading = true;
        
        try {
            $this->validate();
            
            Subscription::create([
                'email' => $this->email,
                'status' => 'active',
                'source' => 'footer',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'subscribed_at' => now(),
            ]);
            
            $this->message = __('newsletter.success_message');
            $this->messageType = 'success';
            $this->email = '';
            
            // Optional: Send welcome email
            // Mail::to($this->email)->queue(new WelcomeSubscriber());
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->messageType = 'error';
            throw $e;
        } catch (\Exception $e) {
            $this->message = __('newsletter.error_message');
            $this->messageType = 'error';
        } finally {
            $this->loading = false;
        }
    }
    
    public function render()
    {
        return view('livewire.store.newsletter-subscription');
    }
}
```

#### Step 2.3: Update Footer
```php
// resources/views/components/store/footer.blade.php
// Replace form with:
<livewire:store.newsletter-subscription />
```

---

### Phase 3: Filament Admin Resources

#### Step 3.1: Newsletter Subscription Resource
```php
// app/Filament/Resources/Newsletter/NewsletterSubscriptionResource.php
namespace App\Filament\Resources\Newsletter;

use App\Models\NewsletterSubscription;
use Filament\Resources\Resource;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;

class NewsletterSubscriptionResource extends Resource
{
    protected static ?string $model = NewsletterSubscription::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    
    protected static ?string $navigationGroup = 'Marketing';
    
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Components\TextInput::make('email')
                ->email()
                ->required(),
                
            Components\Select::make('status')
                ->options([
                    'active' => 'Active',
                    'unsubscribed' => 'Unsubscribed',
                    'bounced' => 'Bounced',
                ])
                ->required(),
                
            Components\Select::make('source')
                ->options([
                    'footer' => 'Footer',
                    'contact' => 'Contact Page',
                    'popup' => 'Popup',
                    'checkout' => 'Checkout',
                ]),
                
            Components\DateTimePicker::make('subscribed_at'),
            
            Components\Textarea::make('unsubscribe_reason')
                ->rows(3),
        ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'unsubscribed',
                        'warning' => 'bounced',
                    ]),
                    
                Tables\Columns\TextColumn::make('source')
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('subscribed_at')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('campaignLogs.sent_count')
                    ->counts('campaignLogs')
                    ->label('Emails Received'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'unsubscribed' => 'Unsubscribed',
                        'bounced' => 'Bounced',
                    ]),
                    
                Tables\Filters\SelectFilter::make('source'),
                
                Tables\Filters\Filter::make('subscribed_last_30_days')
                    ->query(fn ($query) => $query->where('subscribed_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Actions\BulkAction::make('export')
                        ->label('Export to CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            // Export logic using pxlrbt/filament-excel
                        }),
                ]),
            ]);
    }
    
    public static function getWidgets(): array
    {
        return [
            SubscriberStatsWidget::class,
        ];
    }
}
```

#### Step 3.2: Email Campaign Resource
```php
// app/Filament/Resources/EmailCampaigns/EmailCampaignResource.php
// Full implementation with:
// - Campaign type selection (Offers/Custom)
// - Rich text editor for custom messages
// - Multi-select for offers (if type = offers)
// - Recipient targeting
// - Send/Schedule actions
// - Statistics dashboard
```

---

### Phase 4: Queue Jobs & Email Sending

#### Step 4.1: Main Campaign Processing Job
```php
// app/Jobs/ProcessEmailCampaign.php
namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Jobs\SendCampaignEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessEmailCampaign implements ShouldQueue
{
    use InteractsWithQueue, Queueable;
    
    public function __construct(
        public EmailCampaign $campaign
    ) {}
    
    public function handle(): void
    {
        $this->campaign->update(['status' => 'sending', 'started_at' => now()]);
        
        // Get target subscribers
        $subscribers = $this->getTargetSubscribers();
        
        $this->campaign->update(['recipients_count' => $subscribers->count()]);
        
        // Dispatch individual email jobs
        foreach ($subscribers as $subscriber) {
            SendCampaignEmail::dispatch($this->campaign, $subscriber)
                ->delay(now()->addSeconds(rand(1, 60))); // Rate limiting
        }
        
        $this->campaign->update(['status' => 'sent', 'completed_at' => now()]);
    }
    
    private function getTargetSubscribers()
    {
        $query = NewsletterSubscription::query()->where('status', 'active');
        
        return match($this->campaign->send_to) {
            'all' => $query->get(),
            'active_only' => $query->get(),
            'recent' => $query->where('subscribed_at', '>=', now()->subDays(30))->get(),
            'custom' => $this->applyCustomFilters($query)->get(),
        };
    }
}
```

#### Step 4.2: Individual Email Sending Job
```php
// app/Jobs/SendCampaignEmail.php
namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\NewsletterSubscription;
use App\Models\CampaignLog;
use App\Mail\CampaignMail;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmail implements ShouldQueue
{
    public function handle(): void
    {
        $log = CampaignLog::create([
            'campaign_id' => $this->campaign->id,
            'subscriber_id' => $this->subscriber->id,
            'status' => 'sending',
        ]);
        
        try {
            Mail::to($this->subscriber->email)->send(
                new CampaignMail($this->campaign, $this->subscriber)
            );
            
            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
            
            $this->campaign->increment('emails_sent');
            
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            $this->campaign->increment('emails_failed');
        }
    }
}
```

---

## 📧 Email Templates

### Base Email Layout
```php
// resources/views/emails/layouts/email.blade.php
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Responsive email styles */
        /* RTL support */
        /* Violet brand colors */
    </style>
</head>
<body style="background-color: #f3f4f6; padding: 20px;">
    <table style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px;">
        <tr>
            <td style="padding: 40px; text-align: center;">
                <img src="{{ asset('images/logo.png') }}" alt="Violet" style="height: 50px;">
            </td>
        </tr>
        <tr>
            <td style="padding: 0 40px 40px;">
                @yield('content')
            </td>
        </tr>
        <tr>
            <td style="padding: 20px; text-align: center; background: #7c3aed; color: white; border-radius: 0 0 12px 12px;">
                <p>© 2026 Violet Cosmetics. جميع الحقوق محفوظة.</p>
                <p style="font-size: 12px;">
                    <a href="{{ route('newsletter.unsubscribe', ['token' => $subscriber->unsubscribe_token]) }}" 
                       style="color: #e0e7ff;">
                        إلغاء الاشتراك
                    </a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
```

---

## ⚙️ Queue System Configuration

### Step 1: Update `.env`
```env
QUEUE_CONNECTION=database

# Mail Settings
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@flowerviolet.com
MAIL_FROM_NAME="Violet Cosmetics"
```

### Step 2: Run Queue Worker
```powershell
# Development
php artisan queue:work --tries=3 --timeout=60

# Production (with Supervisor)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

### Step 3: Monitor Queue
```powershell
# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

## 🧪 Testing Strategy

### Unit Tests
```php
// tests/Unit/NewsletterSubscriptionTest.php
test('can subscribe to newsletter', function () {
    $email = 'test@example.com';
    
    $subscription = NewsletterSubscription::create([
        'email' => $email,
        'status' => 'active',
        'source' => 'footer',
    ]);
    
    expect($subscription)->toBeInstanceOf(NewsletterSubscription::class);
    expect($subscription->email)->toBe($email);
    expect($subscription->status)->toBe('active');
});

test('cannot subscribe with duplicate email', function () {
    NewsletterSubscription::create(['email' => 'test@example.com']);
    
    expect(fn () => NewsletterSubscription::create(['email' => 'test@example.com']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
```

### Feature Tests
```php
// tests/Feature/NewsletterSubscriptionTest.php
test('livewire component can subscribe', function () {
    Livewire::test(NewsletterSubscription::class)
        ->set('email', 'test@example.com')
        ->call('subscribe')
        ->assertHasNoErrors()
        ->assertSet('messageType', 'success');
    
    $this->assertDatabaseHas('newsletter_subscriptions', [
        'email' => 'test@example.com',
        'status' => 'active',
    ]);
});

test('campaign can be sent to subscribers', function () {
    Queue::fake();
    
    $subscribers = NewsletterSubscription::factory()->count(10)->create();
    $campaign = EmailCampaign::factory()->create();
    
    ProcessEmailCampaign::dispatch($campaign);
    
    Queue::assertPushed(SendCampaignEmail::class, 10);
});
```

---

## 📊 Performance Considerations

### Rate Limiting (Important!)
```php
// Prevent SPAM filters
// Gmail: Max 100 emails/day for free accounts
// SendGrid: Depends on plan
// Amazon SES: No strict limit but monitor bounce rate

// config/queue.php
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
    ],
],
```

### Database Indexing
All critical indexes already included in migration designs:
- `newsletter_subscriptions`: email, status, subscribed_at
- `email_campaigns`: status, type, scheduled_at
- `campaign_logs`: campaign_id, subscriber_id, status

---

## 🔐 Security Considerations

### 1. Unsubscribe Token
```php
// Add to newsletter_subscriptions table
$table->string('unsubscribe_token', 64)->unique()->nullable();

// Generate on creation
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($subscription) {
        $subscription->unsubscribe_token = Str::random(64);
    });
}
```

### 2. GDPR Compliance
- ✅ Clear consent checkbox
- ✅ Easy unsubscribe link in every email
- ✅ Privacy policy link
- ✅ Data export capability
- ✅ Right to be forgotten (soft delete)

### 3. SPAM Prevention
- ✅ Rate limiting (50 emails/minute)
- ✅ Double opt-in (optional)
- ✅ SPF/DKIM records (DNS configuration)
- ✅ Bounce handling

---

## 📚 Required Translations

### Arabic Translations
```php
// lang/ar/newsletter.php
return [
    'subscribe' => 'اشترك في النشرة الإخبارية',
    'email_placeholder' => 'أدخل بريدك الإلكتروني',
    'subscribe_button' => 'اشترك',
    'success_message' => 'تم الاشتراك بنجاح! سنرسل لك آخر العروض والتحديثات.',
    'error_message' => 'عذراً، حدث خطأ. يرجى المحاولة مرة أخرى.',
    'already_subscribed' => 'هذا البريد مشترك بالفعل.',
    'unsubscribe_success' => 'تم إلغاء الاشتراك بنجاح.',
];

// lang/ar/admin.php (add to existing file)
'newsletter' => [
    'title' => 'المشتركون',
    'subscribers' => 'المشتركون في النشرة',
    'campaigns' => 'حملات البريد الإلكتروني',
    'create_campaign' => 'إنشاء حملة جديدة',
    'send_campaign' => 'إرسال الحملة',
],
```

---

## ✅ Implementation Checklist

- [ ] Create 4 migrations (newsletter_subscriptions, email_campaigns, campaign_offers, campaign_logs)
- [ ] Run migrations: `php artisan migrate`
- [ ] Create 4 models with relationships
- [ ] Create Livewire NewsletterSubscription component
- [ ] Create NewsletterSubscription view (Blade)
- [ ] Update Footer component to use Livewire component
- [ ] Create NewsletterSubscriptionResource (Filament v4)
- [ ] Create EmailCampaignResource (Filament v4)
- [ ] Create SubscriberStatsWidget
- [ ] Create CampaignStatsWidget
- [ ] Create ProcessEmailCampaign Job
- [ ] Create SendCampaignEmail Job
- [ ] Create CampaignMail Mailable
- [ ] Create email templates (layouts + content)
- [ ] Configure SMTP in `.env`
- [ ] Test queue: `php artisan queue:work`
- [ ] Add translations (AR/EN)
- [ ] Create NewsletterController (for unsubscribe)
- [ ] Add unsubscribe route
- [ ] Write unit tests
- [ ] Write feature tests
- [ ] Update Privacy Policy page
- [ ] Configure DNS (SPF/DKIM records)

---

## 🎯 Success Metrics

### Key Performance Indicators (KPIs)
- **Subscription Rate**: Target 3-5% of website visitors
- **Email Deliverability**: Target >98% (monitor bounces)
- **Open Rate**: Target >20% (optional tracking)
- **Unsubscribe Rate**: Target <2%
- **Campaign ROI**: Track purchases after campaigns

---

## 📖 References

- **Laravel 12 Docs**: https://laravel.com/docs/12.x
- **Filament v4 Docs**: https://filamentphp.com/docs/4.x
- **Livewire v3 Docs**: https://livewire.laravel.com/docs
- **Laravel Queue Docs**: https://laravel.com/docs/12.x/queues
- **Laravel Mail Docs**: https://laravel.com/docs/12.x/mail

---

## 🚀 Ready to Implement!

**Next Steps:**
1. Review this documentation
2. Confirm database schema
3. Start with Phase 1 (Database & Models)
4. Test each phase before proceeding

**Estimated Timeline:** 3-4 hours for full implementation

---

*Last Updated: January 12, 2026*
*Project: Violet Cosmetics E-commerce*
*Developer: Senior Laravel AI Agent*
