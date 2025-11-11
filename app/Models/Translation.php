<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'locale', 'value', 'group', 'is_active', 'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
