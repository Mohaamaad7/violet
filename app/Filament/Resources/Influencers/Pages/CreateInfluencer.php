<?php

namespace App\Filament\Resources\Influencers\Pages;

use App\Filament\Resources\Influencers\InfluencerResource;
use App\Models\DiscountCode;
use App\Models\Influencer;
use App\Models\User;
use App\Notifications\InfluencerInvitationNotification;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateInfluencer extends CreateRecord
{
    protected static string $resource = InfluencerResource::class;

    /**
     * Holds the generated password for invitation email
     */
    protected ?string $generatedPassword = null;

    /**
     * Holds the coupon code for invitation email
     */
    protected ?string $couponCode = null;

    /**
     * Override the record creation to handle User + Influencer + Coupon in one transaction
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            // ==========================================
            // 1. إنشاء أو جلب المستخدم
            // ==========================================
            $existingUser = User::where('email', $data['email'])->first();

            if ($existingUser) {
                $user = $existingUser;
            } else {
                // توليد كلمة مرور عشوائية
                $this->generatedPassword = Str::random(12);

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'password' => Hash::make($this->generatedPassword),
                    'email_verified_at' => now(),
                ]);
            }

            // ==========================================
            // 2. تعيين دور المؤثر
            // ==========================================
            if (method_exists($user, 'assignRole') && !$user->hasRole('influencer')) {
                $user->assignRole('influencer');
            }

            // ==========================================
            // 3. إنشاء ملف المؤثر
            // ==========================================
            $influencer = Influencer::create([
                'user_id' => $user->id,
                'primary_platform' => $data['primary_platform'] ?? null,
                'handle' => $data['handle'] ?? null,
                'instagram_url' => $data['instagram_url'] ?? null,
                'facebook_url' => $data['facebook_url'] ?? null,
                'tiktok_url' => $data['tiktok_url'] ?? null,
                'youtube_url' => $data['youtube_url'] ?? null,
                'twitter_url' => $data['twitter_url'] ?? null,
                'commission_rate' => $data['commission_rate'] ?? 10,
                'status' => $data['status'] ?? 'active',
                'total_sales' => 0,
                'total_commission_earned' => 0,
                'total_commission_paid' => 0,
                'balance' => 0,
            ]);

            // ==========================================
            // 4. إنشاء كود الخصم
            // ==========================================
            $this->couponCode = $data['coupon_code'];

            DiscountCode::create([
                'influencer_id' => $influencer->id,
                'code' => strtoupper($data['coupon_code']),
                'discount_type' => $data['discount_type'] ?? 'percentage',
                'discount_value' => $data['discount_value'] ?? 15,
                'commission_type' => $data['commission_type'] ?? 'percentage',
                'commission_value' => $data['commission_rate'] ?? 10,
                'is_active' => true,
            ]);

            // ==========================================
            // 5. إرسال دعوة الترحيب
            // ==========================================
            if (!empty($data['send_invitation']) && $this->generatedPassword) {
                try {
                    $user->notify(new InfluencerInvitationNotification(
                        $this->generatedPassword,
                        $this->couponCode
                    ));

                    \Log::info('Influencer invitation email sent', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'coupon' => $this->couponCode,
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the creation
                    \Log::error('Failed to send influencer invitation', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            return $influencer;
        });
    }

    /**
     * After creation notification
     */
    protected function getCreatedNotification(): ?Notification
    {
        $message = trans_db('admin.influencers.notifications.created');

        if ($this->couponCode) {
            $message .= ' | ' . trans_db('admin.influencers.fields.coupon_code') . ': ' . $this->couponCode;
        }

        return Notification::make()
            ->success()
            ->title($message);
    }

    /**
     * Redirect URL after creation
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
