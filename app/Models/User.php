<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model - For Staff/Admins only
 * 
 * Note: Customers are now in a separate `customers` table.
 * This model is only for admin panel users (staff, managers, etc.)
 */
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'profile_photo_path',
        'status',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Determine if the user can access a Filament panel.
     * 
     * - Admin Panel: Staff roles (super-admin, admin, manager, etc.)
     * - Partners Panel: Influencers with active status
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Admin Panel: Staff/Employees only
        if ($panel->getId() === 'admin') {
            return $this->hasRole(['super-admin', 'admin', 'manager', 'sales', 'accountant', 'content-manager', 'delivery']);
        }

        // Partners Panel: Active Influencers only
        if ($panel->getId() === 'partners') {
            return $this->hasRole('influencer')
                && $this->influencer?->status === 'active';
        }

        return false;
    }

    // Relations

    /**
     * Influencer profile (if user is an influencer staff member)
     */
    public function influencer(): HasOne
    {
        return $this->hasOne(Influencer::class);
    }

    /**
     * Orders processed by this admin user
     */
    public function processedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Blog posts authored by this user
     */
    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ==================== Dashboard Customization Relationships ====================

    /**
     * User-specific widget preferences
     */
    public function widgetPreferences(): HasMany
    {
        return $this->hasMany(UserWidgetPreference::class);
    }
}
