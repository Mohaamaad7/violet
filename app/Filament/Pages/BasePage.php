<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ChecksPageAccess;
use Filament\Pages\Page;

/**
 * BasePage - Zero-Config Page with Access Control
 * 
 * Extend this class instead of Filament\Pages\Page to automatically
 * enable role-based access control for your page.
 * 
 * Usage:
 * class MyNewPage extends BasePage
 * {
 *     // Your page logic here
 * }
 */
abstract class BasePage extends Page
{
    use ChecksPageAccess;
}
