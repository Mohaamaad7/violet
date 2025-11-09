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

            // Update user type to influencer
            $application->user->update(['type' => 'influencer']);

            // Create influencer record
            $influencer = Influencer::create([
                'user_id' => $application->user_id,
                'commission_rate' => $commissionRate,
                'status' => 'active',
                'social_platform' => $application->social_platform,
                'social_handle' => $application->social_handle,
                'followers_count' => $application->followers_count,
                'content_type' => $application->content_type,
            ]);

            return $influencer;
        });
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
                'payment_method' => $data['payment_method'],
                'payment_details' => $data['payment_details'] ?? null,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            // Mark commissions as paid
            $pendingCommissions = InfluencerCommission::where('influencer_id', $influencerId)
                ->where('status', 'pending')
                ->get();

            foreach ($pendingCommissions as $commission) {
                $commission->update([
                    'status' => 'paid',
                    'payout_id' => $payout->id,
                ]);
            }

            // Update influencer balance
            $influencer->update([
                'total_paid' => $influencer->total_paid + $amount,
                'pending_balance' => 0,
            ]);

            return $payout;
        });
    }

    /**
     * Complete payout
     */
    public function completePayout(int $payoutId, ?int $processedBy = null): CommissionPayout
    {
        $payout = CommissionPayout::findOrFail($payoutId);

        $payout->update([
            'status' => 'completed',
            'processed_by' => $processedBy,
            'processed_at' => now(),
        ]);

        return $payout->fresh();
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
