# Translation System Quick Start Guide

## 🚀 Accessing the Translation Manager

1. **Start the server:**
   ```powershell
   php artisan serve
   ```

2. **Login to Filament Admin:**
   - URL: http://127.0.0.1:8000/admin
   - Credentials: Use the admin account created during setup

3. **Navigate to Translations:**
   - Sidebar → **System** → **Translations**

---

## ✏️ Editing Translations

### Via Admin Panel (Recommended)

1. Go to **System → Translations**
2. Click **Edit** on any translation
3. Modify the `value` field
4. Save → **Cache invalidated automatically**
5. **UI updates immediately** (no page refresh needed if Livewire listeners active)

### Programmatically

```php
use App\Services\TranslationService;

$service = app(TranslationService::class);

// Set/Update a translation
$service->set(
    key: 'messages.new_key',
    locale: 'ar',
    value: 'النص الجديد',
    group: 'messages',
    active: true,
    updatedBy: auth()->id()
);

// Get translation (with automatic fallback to files)
$text = $service->get('messages.welcome', 'ar');
// Returns: "مرحباً بك في Violet"
```

### Helper Functions

```php
// Quick access (uses current locale)
echo trans_db('messages.home'); // الرئيسية

// Set translation
set_trans('custom.key', 'en', 'Custom Value', 'custom', true);
```

---

## 📥 Import/Export

### Export Current Locale to JSON

1. Go to **Translations** page
2. Click **Export JSON** button
3. Downloads `translations_{locale}.json`

### Import from JSON

1. Prepare JSON file:
   ```json
   {
     "messages.welcome": "مرحباً بك",
     "messages.goodbye": "وداعاً"
   }
   ```
2. Click **Import JSON**
3. Upload file
4. Translations imported (existing keys not overridden by default)

---

## 🌍 Switching Language

### Admin Panel

- **Topbar buttons**: Click **عربي** or **English**
- Changes take effect immediately (no page reload)

### Public Store

- Language links in the store layout
- URL: `GET /language/{locale}` (e.g., `/language/ar`)

### Programmatically

```php
app()->setLocale('ar');
session(['locale' => 'ar']);
```

---

## 🧪 Testing

### Command Line

```powershell
# Run comprehensive translation system tests
php artisan test:translations

# Should output:
# ✅ All tests passed! DB-backed translation system is working.
```

### Manual Browser Test

1. Go to `/admin`
2. Switch language (عربي → English → عربي)
3. Edit a translation in **System → Translations**
4. Verify change appears immediately on next page load

---

## 🔧 Troubleshooting

### Translations not updating?

```powershell
php artisan optimize:clear
```

### Cache issues?

```powershell
php artisan cache:clear
php artisan view:clear
```

### Admin panel not showing Translations resource?

```powershell
# Re-run migration
php artisan migrate

# Clear Filament cache
php artisan filament:cache-clear

# Check resource is registered
php artisan route:list | Select-String "translations"
```

---

## 📖 Full Documentation

See: `docs/TRANSLATION_SYSTEM.md` for comprehensive documentation including:
- Architecture details
- API reference
- Caching strategy
- Security considerations
- Performance optimization
- Future enhancements roadmap

---

## ✅ What's Working Now

- ✅ **DB-backed translations** with file fallbacks
- ✅ **Dynamic editing** from Filament admin
- ✅ **Real-time cache invalidation**
- ✅ **Import/Export** (JSON format)
- ✅ **Locale switcher** in admin topbar (Livewire-powered)
- ✅ **Enhanced SetLocale** middleware (user → cookie → session → header → default)
- ✅ **Helper functions** (`trans_db()`, `set_trans()`)
- ✅ **Full compatibility** with existing `trans()` and `__()` helpers

---

## 🎯 Next Steps (Optional Enhancements)

1. **Inline editing**: Add "pencil" icon on storefront pages (admin-only) for live editing
2. **Translation history**: Track changes for revert functionality
3. **AI translation**: Auto-translate missing keys via Google Translate API
4. **Approval workflow**: Require approval before translations go live
5. **Multi-tenant**: Separate translation sets per tenant/store

---

**Ready to use! 🚀**

For questions or issues, refer to `docs/TRANSLATION_SYSTEM.md` or run:
```powershell
php artisan test:translations
```
