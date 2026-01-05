<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ChecksPageAccess;
use App\Models\HelpEntry;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class HelpCenter extends Page
{
    use ChecksPageAccess;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;
    protected static ?int $navigationSort = 111;
    protected string $view = 'filament.pages.help-center';

    #[Url]
    public string $search = '';

    public static function getNavigationLabel(): string
    {
        return __('admin.help_center.title');
    }

    public function getTitle(): string
    {
        return __('admin.help_center.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.system');
    }

    /**
     * Get all active help entries grouped by category
     */
    public function getGroupedEntries(): Collection
    {
        $query = HelpEntry::active()->ordered();

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        return $query->get()->groupBy('category');
    }

    /**
     * Get category name translation
     */
    public function getCategoryName(string $category): string
    {
        return HelpEntry::CATEGORIES[$category] ?? $category;
    }

    /**
     * Get category icon
     */
    public function getCategoryIcon(string $category): string
    {
        return match ($category) {
            'orders' => 'heroicon-o-shopping-cart',
            'products' => 'heroicon-o-cube',
            'marketing' => 'heroicon-o-megaphone',
            'inventory' => 'heroicon-o-archive-box',
            'sales' => 'heroicon-o-chart-bar',
            'system' => 'heroicon-o-cog-6-tooth',
            default => 'heroicon-o-document-text',
        };
    }

    /**
     * Get category color
     */
    public function getCategoryColor(string $category): string
    {
        return match ($category) {
            'orders' => 'info',
            'products' => 'success',
            'marketing' => 'warning',
            'inventory' => 'danger',
            'sales' => 'primary',
            'system' => 'gray',
            default => 'gray',
        };
    }
}
