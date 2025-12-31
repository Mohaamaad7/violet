<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\ChecksResourceAccess;
use Filament\Resources\Resource;

/**
 * Base Resource Class
 * 
 * All new Filament Resources should extend this class instead of Resource.
 * This automatically applies role-based access control.
 * 
 * Usage:
 * class MyNewResource extends BaseResource
 * {
 *     // Your resource code
 * }
 */
abstract class BaseResource extends Resource
{
    use ChecksResourceAccess;
}
