<?php

namespace App\Filament\Partners\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfilePage extends Page
{
    protected static ?int $navigationSort = 2;

    // Password change properties
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    public function getView(): string
    {
        return 'filament.partners.pages.profile-page';
    }

    public function getLayout(): string
    {
        return 'components.layouts.partners';
    }

    public function getTitle(): string
    {
        return __('messages.partners.nav.profile');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.partners.nav.profile');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /**
     * Test notification system
     */
    public function testNotification(): void
    {
        Notification::make()
            ->title('🎉 اختبار ناجح!')
            ->body('الإشعارات تعمل بشكل صحيح. يمكنك الآن تغيير كلمة المرور.')
            ->success()
            ->duration(5000)
            ->send();
    }

    /**
     * Update password
     */
    public function updatePassword(): void
    {
        $user = Auth::user();

        // Validate current password
        if (!Hash::check($this->currentPassword, $user->password)) {
            Notification::make()
                ->title('خطأ في كلمة المرور')
                ->body('كلمة المرور الحالية غير صحيحة')
                ->danger()
                ->send();
            return;
        }

        // Validate new password length
        if (strlen($this->newPassword) < 8) {
            Notification::make()
                ->title('كلمة مرور ضعيفة')
                ->body('يجب أن تكون كلمة المرور 8 أحرف على الأقل')
                ->danger()
                ->send();
            return;
        }

        // Validate password confirmation
        if ($this->newPassword !== $this->newPasswordConfirmation) {
            Notification::make()
                ->title('خطأ في التأكيد')
                ->body('كلمة المرور الجديدة وتأكيدها غير متطابقين')
                ->danger()
                ->send();
            return;
        }

        // Update password
        $user->update([
            'password' => Hash::make($this->newPassword)
        ]);

        // Clear fields
        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);

        // Show success notification
        Notification::make()
            ->title('تم التحديث بنجاح')
            ->body('تم تحديث كلمة المرور بنجاح. سيتم تسجيل خروجك الآن...')
            ->success()
            ->duration(3000)
            ->send();

        // Dispatch browser event for logout
        $this->dispatch('password-changed');
    }

    /**
     * Update profile (placeholder for future implementation)
     */
    public function updateProfile(): void
    {
        Notification::make()
            ->title('قيد التطوير')
            ->body('هذه الميزة قيد التطوير حالياً')
            ->warning()
            ->send();
    }

    /**
     * Update social media (placeholder for future implementation)
     */
    public function updateSocialMedia(): void
    {
        Notification::make()
            ->title('قيد التطوير')
            ->body('هذه الميزة قيد التطوير حالياً')
            ->warning()
            ->send();
    }
}
