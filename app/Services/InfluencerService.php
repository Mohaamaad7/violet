<?php

namespace App\Services;

use App\Models\Influencer;
use App\Models\InfluencerApplication;
use App\Models\DiscountCode;
use App\Models\InfluencerCommission;
use App\Models\CommissionPayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InfluencerService
{
    /**
     * Get all influencers with filters
     */
    public function getAllInfluencers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Influencer::with('user');

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Search
        if (isset($filters['search'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find influencer by ID
     */
    public function findInfluencer(int $id): ?Influencer
    {
        return Influencer::with(['user', 'discountCodes', 'commissions'])
            ->findOrFail($id);
    }

    /**
     * Get influencer applications
     */
    public function getApplications(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = InfluencerApplication::with('user');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Submit influencer application
     */
    public function submitApplication(array $data): InfluencerApplication
    {
        return InfluencerApplication::create($data);
    }

    /**
     * Approve influencer application
     */
    public function approveApplication(int $applicationId, float $commissionRate, ?int $reviewedBy = null): Influencer
    {
        return DB::transaction(function () use ($applicationId, $commissionRate, $reviewedBy) {
            $application = InfluencerApplication::findOrFail($applicationId);

            // Update application status
            $application->update([
                'status' => 'approved',
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => now(),
            ]);

            // Create influencer record with social media data from application
            $influencer = Influencer::create([
                'user_id' => $application->user_id,
                'commission_rate' => $commissionRate,
                'status' => 'active',
                'instagram_url' => $application->instagram_url,
                'facebook_url' => $application->facebook_url,
                'tiktok_url' => $application->tiktok_url,
                'youtube_url' => $application->youtube_url,
                'twitter_url' => $application->twitter_url,
                'instagram_followers' => $application->instagram_followers,
                'facebook_followers' => $application->facebook_followers,
                'tiktok_followers' => $application->tiktok_followers,
                'youtube_followers' => $application->youtube_followers,
                'twitter_followers' => $application->twitter_followers,
                'content_type' => $application->content_type,
            ]);

            // Create default discount code for influencer
            $this->createDiscountCode($influencer->id, [
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'is_active' => true,
                'commission_type' => 'percentage',
                'commission_value' => $commissionRate,
            ]);

            return $influencer;
        });
    }

    /**
     * Approve influencer application with custom coupon settings
     * 
     * @param int $applicationId
     * @param float $commissionRate
     * @param string $couponCode
     * @param string $discountType
     * @param float $discountValue
     * @param bool $sendWelcomeEmail
     * @param int|null $reviewedBy
     * @return Influencer
     */
    public function approveApplicationWithCoupon(
        int $applicationId,
        float $commissionRate,
        string $couponCode,
        string $discountType,
        float $discountValue,
        bool $sendWelcomeEmail = true,
        ?int $reviewedBy = null
    ): Influencer {
        return DB::transaction(function () use ($applicationId, $commissionRate, $couponCode, $discountType, $discountValue, $sendWelcomeEmail, $reviewedBy) {
            $application = InfluencerApplication::findOrFail($applicationId);

            // Update application status
            $application->update([
                'status' => 'approved',
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => now(),
            ]);

            // Get or create user
            $user = $application->user;
            $generatedPassword = null;

            if (!$user) {
                // Create new user if application was from guest
                $generatedPassword = \Illuminate\Support\Str::random(12);
                $user = \App\Models\User::create([
                    'name' => $application->full_name,
                    'email' => $application->email,
                    'phone' => $application->phone,
                    'password' => \Illuminate\Support\Facades\Hash::make($generatedPassword),
                    'email_verified_at' => now(),
                ]);

                // Link user to application
                $application->update(['user_id' => $user->id]);
            }

            // Assign influencer role
            if (method_exists($user, 'assignRole') && !$user->hasRole('influencer')) {
                $user->assignRole('influencer');
            }

            // Determine primary platform from application data
            $primaryPlatform = $this->determinePrimaryPlatform($application);

            // Create influencer record
            $influencer = Influencer::create([
                'user_id' => $user->id,
                'primary_platform' => $primaryPlatform,
                'handle' => $application->instagram_url ? $this->extractHandle($application->instagram_url) : null,
                'commission_rate' => $commissionRate,
                'status' => 'active',
                'instagram_url' => $application->instagram_url,
                'facebook_url' => $application->facebook_url,
                'tiktok_url' => $application->tiktok_url,
                'youtube_url' => $application->youtube_url,
                'twitter_url' => $application->twitter_url,
                'instagram_followers' => $application->instagram_followers,
                'facebook_followers' => $application->facebook_followers,
                'tiktok_followers' => $application->tiktok_followers,
                'youtube_followers' => $application->youtube_followers,
                'twitter_followers' => $application->twitter_followers,
                'content_type' => $application->content_type,
                'total_sales' => 0,
                'total_commission_earned' => 0,
                'total_commission_paid' => 0,
                'balance' => 0,
            ]);

            // Create discount code with custom settings
            DiscountCode::create([
                'influencer_id' => $influencer->id,
                'code' => strtoupper($couponCode),
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'commission_type' => 'percentage',
                'commission_value' => $commissionRate,
                'is_active' => true,
            ]);

            // Send welcome email
            if ($sendWelcomeEmail && $generatedPassword) {
                try {
                    $user->notify(new \App\Notifications\InfluencerInvitationNotification(
                        $generatedPassword,
                        $couponCode
                    ));
                } catch (\Exception $e) {
                    \Log::error('Failed to send influencer welcome email: ' . $e->getMessage());
                }
            } elseif ($sendWelcomeEmail) {
                // Send approval notification without password (existing user)
                try {
                    $user->notify(new \App\Notifications\ApplicationApprovedNotification(
                        $influencer,
                        $couponCode
                    ));
                } catch (\Exception $e) {
                    \Log::error('Failed to send approval notification: ' . $e->getMessage());
                }
            }

            return $influencer;
        });
    }

    /**
     * Determine primary platform from application data
     */
    protected function determinePrimaryPlatform(InfluencerApplication $application): ?string
    {
        $platforms = [
            'instagram' => $application->instagram_followers ?? 0,
            'facebook' => $application->facebook_followers ?? 0,
            'tiktok' => $application->tiktok_followers ?? 0,
            'youtube' => $application->youtube_followers ?? 0,
            'twitter' => $application->twitter_followers ?? 0,
        ];

        arsort($platforms);
        $topPlatform = key($platforms);

        return $platforms[$topPlatform] > 0 ? $topPlatform : null;
    }

    /**
     * Extract handle from social media URL
     */
    protected function extractHandle(?string $url): ?string
    {
        if (!$url)
            return null;

        $parts = parse_url($url, PHP_URL_PATH);
        if (!$parts)
            return null;

        $segments = array_filter(explode('/', $parts));
        $handle = end($segments);

        return $handle ?: null;
    }


    /**
     * Reject influencer application
     */
    public function rejectApplication(int $applicationId, string $reason, ?int $reviewedBy = null): InfluencerApplication
    {
        $application = InfluencerApplication::findOrFail($applicationId);

        $application->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
        ]);

        return $application;
    }

    /**
     * Create discount code for influencer
     */
    public function createDiscountCode(int $influencerId, array $data): DiscountCode
    {
        // Auto-generate code if not provided
        if (!isset($data['code']) || empty($data['code'])) {
            $influencer = $this->findInfluencer($influencerId);
            $data['code'] = $this->generateDiscountCode($influencer->user->name);
        }

        $data['influencer_id'] = $influencerId;
        $data['code'] = strtoupper($data['code']);

        return DiscountCode::create($data);
    }

    /**
     * Generate unique discount code
     */
    protected function generateDiscountCode(string $name): string
    {
        $base = strtoupper(Str::slug($name, ''));
        $base = substr($base, 0, 6);

        do {
            $code = $base . strtoupper(Str::random(4));
        } while (DiscountCode::where('code', $code)->exists());

        return $code;
    }

    /**
     * Update influencer status
     */
    public function updateStatus(int $id, string $status): Influencer
    {
        $influencer = $this->findInfluencer($id);
        $influencer->update(['status' => $status]);
        return $influencer->fresh();
    }

    /**
     * Update commission rate
     */
    public function updateCommissionRate(int $id, float $rate): Influencer
    {
        $influencer = $this->findInfluencer($id);
        $influencer->update(['commission_rate' => $rate]);
        return $influencer->fresh();
    }

    /**
     * Calculate commission for order
     */
    public function calculateCommission(int $influencerId, float $orderTotal, ?int $discountCodeId = null): float
    {
        $influencer = $this->findInfluencer($influencerId);

        // Get discount code to check for custom commission
        if ($discountCodeId) {
            $discountCode = DiscountCode::find($discountCodeId);

            if ($discountCode && $discountCode->commission_type === 'fixed') {
                return $discountCode->commission_value;
            }

            if ($discountCode && $discountCode->commission_type === 'percentage') {
                return ($orderTotal * $discountCode->commission_value) / 100;
            }
        }

        // Use influencer's default commission rate
        return ($orderTotal * $influencer->commission_rate) / 100;
    }

    /**
     * Record commission for order
     */
    public function recordCommission(array $data): InfluencerCommission
    {
        return InfluencerCommission::create($data);
    }

    /**
     * Get influencer commissions
     */
    public function getCommissions(int $influencerId, array $filters = []): Collection
    {
        $query = InfluencerCommission::where('influencer_id', $influencerId)
            ->with(['order', 'discountCode']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get pending commission balance
     */
    public function getPendingBalance(int $influencerId): float
    {
        return InfluencerCommission::where('influencer_id', $influencerId)
            ->where('status', 'pending')
            ->sum('commission_amount');
    }

    /**
     * Create payout
     */
    public function createPayout(int $influencerId, float $amount, array $data): CommissionPayout
    {
        return DB::transaction(function () use ($influencerId, $amount, $data) {
            $influencer = $this->findInfluencer($influencerId);

            // Create payout
            $payout = CommissionPayout::create([
                'influencer_id' => $influencerId,
                'amount' => $amount,
                'method' => $data['method'] ?? 'bank_transfer',
                'bank_details' => $data['bank_details'] ?? null,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            return $payout;
        });
    }

    /**
     * Approve payout request
     */
    public function approvePayout(int $payoutId, ?int $approvedBy = null): CommissionPayout
    {
        $payout = CommissionPayout::findOrFail($payoutId);

        $payout->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        return $payout->fresh();
    }

    /**
     * Reject payout request
     */
    public function rejectPayout(int $payoutId, string $reason, ?int $rejectedBy = null): CommissionPayout
    {
        $payout = CommissionPayout::findOrFail($payoutId);

        $payout->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
        ]);

        return $payout->fresh();
    }

    /**
     * Process/complete payout (mark as paid)
     */
    public function processPayout(int $payoutId, string $transactionReference, ?int $paidBy = null): CommissionPayout
    {
        return DB::transaction(function () use ($payoutId, $transactionReference, $paidBy) {
            $payout = CommissionPayout::findOrFail($payoutId);
            $influencer = $payout->influencer;

            // Update payout status
            $payout->update([
                'status' => 'paid',
                'transaction_reference' => $transactionReference,
                'paid_by' => $paidBy,
                'paid_at' => now(),
            ]);

            // Mark pending commissions as paid and link to payout
            InfluencerCommission::where('influencer_id', $payout->influencer_id)
                ->where('status', 'pending')
                ->update([
                        'status' => 'paid',
                        'payout_id' => $payout->id,
                        'paid_at' => now(),
                    ]);

            // Update influencer balance
            $influencer->decrement('balance', $payout->amount);
            $influencer->increment('total_commission_paid', $payout->amount);

            return $payout->fresh();
        });
    }

    /**
     * Complete payout (legacy method - calls processPayout)
     */
    public function completePayout(int $payoutId, ?int $processedBy = null): CommissionPayout
    {
        return $this->processPayout($payoutId, 'MANUAL-' . now()->format('YmdHis'), $processedBy);
    }

    /**
     * Get influencer statistics
     */
    public function getInfluencerStats(int $influencerId): array
    {
        $influencer = $this->findInfluencer($influencerId);

        return [
            'total_discount_codes' => $influencer->discountCodes()->count(),
            'active_discount_codes' => $influencer->discountCodes()->active()->count(),
            'total_orders' => InfluencerCommission::where('influencer_id', $influencerId)->count(),
            'total_earned' => InfluencerCommission::where('influencer_id', $influencerId)->sum('commission_amount'),
            'pending_balance' => $this->getPendingBalance($influencerId),
            'total_paid' => $influencer->total_paid,
        ];
    }

    /**
     * Get top influencers by earnings
     */
    public function getTopInfluencers(int $limit = 10): Collection
    {
        return Influencer::with('user')
            ->select('influencers.*')
            ->selectRaw('(SELECT SUM(commission_amount) FROM influencer_commissions WHERE influencer_id = influencers.id) as total_earnings')
            ->orderByDesc('total_earnings')
            ->limit($limit)
            ->get();
    }
}
