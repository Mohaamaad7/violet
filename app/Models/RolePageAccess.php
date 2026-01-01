<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Role Page Access - Zero-Config Approach
 * 
 * This table stores EXCEPTIONS only (denied pages).
 * If a page is NOT in this table -> it's accessible by default.
 */
class RolePageAccess extends Model
{
    use HasFactory;

    protected $table = 'role_page_access';

    protected $fillable = [
        'role_id',
        'page_class',
        'can_access',
    ];

    protected $casts = [
        'can_access' => 'boolean',
    ];

    /**
     * Relationship to Role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if a page is accessible for a specific role
     */
    public static function isAccessibleForRole(string $pageClass, int $roleId): bool
    {
        $record = self::where('page_class', $pageClass)
            ->where('role_id', $roleId)
            ->where('can_access', false)
            ->first();

        return $record === null; // Accessible if no deny record
    }
}
