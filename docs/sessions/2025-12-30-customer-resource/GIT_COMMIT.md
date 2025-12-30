# Git Commit Message for Customer Resource

## Commit Command:

```bash
git add app/Filament/Resources/Customers/
git add resources/views/filament/resources/customers/
git add lang/ar/admin.php lang/en/admin.php
git add lang/ar/messages.php lang/en/messages.php
git add docs/sessions/2025-12-30-customer-resource/

git commit -m "feat(admin): Add complete CustomerResource with actions and wishlist modal

Features:
- List page with advanced filters (status, date range, orders, spent)
- View page with customer details, statistics, orders, addresses
- Edit page for basic customer info (profile photo, name, email, phone, status, locale)
- Send Email action with RichEditor (uses EmailService)
- Reset Password action (sends secure token via Laravel Password broker)
- View Wishlist action with modal (shows products with images and stock status)
- Bulk actions (activate, block, delete selected)
- Block/Activate toggle button in view page

Technical:
- 7 resource files (CustomerResource + Pages)
- 3 action files (SendEmailAction, ResetPasswordAction, ViewWishlistAction)
- 1 blade view (wishlist-modal.blade.php)
- Complete AR/EN translations for all labels and messages
- Uses existing EmailService and Laravel Password Reset
- Integrates with Spatie Media Library for product images

Security:
- Password cannot be edited from admin panel (security best practice)
- Reset password uses secure token system
- All emails logged in email_logs table

Files: 15 created, 4 modified
LOC: ~2,385 lines (code + docs)
Documentation: Complete session docs in docs/sessions/2025-12-30-customer-resource/

Tested: Ready for server testing"
```

## Or Short Version:

```bash
git add app/Filament/Resources/Customers/
git add resources/views/filament/resources/customers/
git add lang/ar/admin.php lang/en/admin.php
git add lang/ar/messages.php lang/en/messages.php
git add docs/sessions/2025-12-30-customer-resource/

git commit -m "feat(admin): Add CustomerResource with email, password reset, and wishlist features

- List/View/Edit pages with filters and statistics
- Send email, reset password, view wishlist actions
- Bulk actions (activate/block/delete)
- Complete AR/EN translations
- 15 files created, 4 modified

Ready for server testing"
```

## Push Command:

```bash
git push origin main
# OR
git push origin develop
# حسب الـ branch اللي شغال عليه
```
