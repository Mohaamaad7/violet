<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * System Reset Service
 * 
 * Handles selective data deletion for transitioning from testing to production.
 * Super Admin only - with comprehensive safety measures.
 */
class SystemResetService
{
    /**
     * Reset categories with their associated tables, directories, and dependencies
     */
    protected array $categories = [
        'customers' => [
            'label' => 'العملاء',
            'icon' => 'heroicon-o-users',
            'tables' => ['wishlists', 'cart_items', 'carts', 'shipping_addresses', 'customers'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
        ],
        'orders' => [
            'label' => 'الطلبات والمبيعات',
            'icon' => 'heroicon-o-shopping-bag',
            'tables' => ['order_status_history', 'return_items', 'order_returns', 'order_items', 'orders'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
        ],
        'products' => [
            'label' => 'المنتجات والتصنيفات',
            'icon' => 'heroicon-o-cube',
            'tables' => ['product_images', 'product_reviews', 'product_variants', 'products', 'categories'],
            'directories' => ['products'],
            'media_models' => [Product::class, Category::class],
            'dependencies' => ['orders'], // Orders depend on products
        ],
        'inventory' => [
            'label' => 'حركات المخزون',
            'icon' => 'heroicon-o-archive-box',
            'tables' => ['stock_count_items', 'stock_counts', 'stock_movements', 'batches', 'warehouses'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
        ],
        'finance' => [
            'label' => 'المدفوعات والعمولات',
            'icon' => 'heroicon-o-banknotes',
            'tables' => ['payments', 'commission_payouts', 'influencer_commissions', 'code_usages'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
        ],
        'influencers' => [
            'label' => 'المؤثرين وأكواد الخصم',
            'icon' => 'heroicon-o-user-group',
            'tables' => ['influencer_applications', 'influencers', 'discount_codes'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => ['finance'],
        ],
        'content' => [
            'label' => 'المحتوى',
            'icon' => 'heroicon-o-document-text',
            'tables' => ['blog_posts', 'pages', 'banners', 'sliders', 'help_entries'],
            'directories' => ['banners', 'sliders'],
            'media_models' => [],
            'dependencies' => [],
        ],
        'staff' => [
            'label' => 'الموظفين',
            'icon' => 'heroicon-o-user-circle',
            'tables' => ['users'], // Will be handled specially to exclude current user
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
            'special' => true,
        ],
        'activity_logs' => [
            'label' => 'سجلات النشاط',
            'icon' => 'heroicon-o-clipboard-document-list',
            'tables' => ['activity_log'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
        ],
    ];

    /**
     * Preset configurations for quick reset
     */
    protected array $presets = [
        'factory_reset_lite' => [
            'label' => 'حذف كل البيانات (إبقاء الإعدادات والموظفين)',
            'categories' => ['customers', 'orders', 'products', 'inventory', 'finance', 'influencers', 'content', 'activity_logs'],
        ],
        'developer_mode' => [
            'label' => 'وضع المطور (إبقاء المنتجات)',
            'categories' => ['customers', 'orders', 'inventory', 'finance', 'activity_logs'],
        ],
        'keep_products' => [
            'label' => 'الاحتفاظ بالمنتجات والإعدادات فقط',
            'categories' => ['customers', 'orders', 'inventory', 'finance', 'influencers', 'content', 'staff', 'activity_logs'],
        ],
    ];

    /**
     * Get all available categories with their configuration
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Get all available presets
     */
    public function getPresets(): array
    {
        return $this->presets;
    }

    /**
     * Get statistics for each category (record counts)
     */
    public function getCategoryStats(): array
    {
        $stats = [];

        foreach ($this->categories as $key => $category) {
            $totalRecords = 0;

            foreach ($category['tables'] as $table) {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();

                    // Special handling for staff - exclude current user
                    if ($table === 'users' && Auth::check()) {
                        $count = max(0, $count - 1); // Exclude current user
                    }

                    $totalRecords += $count;
                }
            }

            $stats[$key] = [
                'label' => $category['label'],
                'icon' => $category['icon'],
                'count' => $totalRecords,
                'special' => $category['special'] ?? false,
            ];
        }

        return $stats;
    }

    /**
     * Get tables that will be affected by selected categories
     */
    public function getAffectedTables(array $selectedCategories): array
    {
        $tables = [];

        foreach ($selectedCategories as $categoryKey) {
            if (isset($this->categories[$categoryKey])) {
                $tables = array_merge($tables, $this->categories[$categoryKey]['tables']);
            }
        }

        return array_unique($tables);
    }

    /**
     * Execute the reset operation
     * 
     * @param array $selectedCategories Categories to reset
     * @param bool $createBackup Whether to create a backup first
     * @param callable|null $onProgress Progress callback
     * @return array Result summary
     */
    public function reset(array $selectedCategories, bool $createBackup = true, ?callable $onProgress = null): array
    {
        $result = [
            'success' => true,
            'backup_created' => false,
            'deleted_records' => [],
            'cleaned_directories' => [],
            'errors' => [],
            'started_at' => now()->toDateTimeString(),
        ];

        // Log the start of the operation
        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'categories' => $selectedCategories,
                'create_backup' => $createBackup,
            ])
            ->log('بدء عملية إعادة تعيين النظام');

        try {
            // Step 1: Create backup if requested
            if ($createBackup) {
                $this->notify($onProgress, 'جاري إنشاء نسخة احتياطية...');
                Artisan::call('backup:run', ['--only-db' => true]);
                $result['backup_created'] = true;
            }

            // Step 2: Disable foreign key checks
            Schema::disableForeignKeyConstraints();

            // Step 3: Process each category
            $totalCategories = count($selectedCategories);
            $currentIndex = 0;

            foreach ($selectedCategories as $categoryKey) {
                $currentIndex++;

                if (!isset($this->categories[$categoryKey])) {
                    continue;
                }

                $category = $this->categories[$categoryKey];
                $this->notify($onProgress, "جاري حذف {$category['label']} ({$currentIndex}/{$totalCategories})...");

                // Delete from tables
                foreach ($category['tables'] as $table) {
                    if (!Schema::hasTable($table)) {
                        continue;
                    }

                    try {
                        $count = 0;

                        // Special handling for users table
                        if ($table === 'users' && Auth::check()) {
                            $count = DB::table($table)
                                ->where('id', '!=', Auth::id())
                                ->count();
                            DB::table($table)
                                ->where('id', '!=', Auth::id())
                                ->delete();
                        } else {
                            $count = DB::table($table)->count();
                            DB::table($table)->truncate();
                        }

                        $result['deleted_records'][$table] = $count;

                        // Reset auto-increment (only for truncated tables)
                        if ($table !== 'users' || !Auth::check()) {
                            $this->resetAutoIncrement($table);
                        }
                    } catch (\Exception $e) {
                        $result['errors'][] = "خطأ في حذف جدول {$table}: " . $e->getMessage();
                    }
                }

                // Clean up media library entries
                foreach ($category['media_models'] as $modelClass) {
                    try {
                        Media::where('model_type', $modelClass)->delete();
                    } catch (\Exception $e) {
                        $result['errors'][] = "خطأ في حذف ملفات الميديا لـ {$modelClass}: " . $e->getMessage();
                    }
                }

                // Clean up directories
                foreach ($category['directories'] as $directory) {
                    try {
                        $path = storage_path("app/public/{$directory}");
                        if (File::isDirectory($path)) {
                            File::deleteDirectory($path);
                            File::makeDirectory($path, 0755, true);
                            $result['cleaned_directories'][] = $directory;
                        }
                    } catch (\Exception $e) {
                        $result['errors'][] = "خطأ في تنظيف مجلد {$directory}: " . $e->getMessage();
                    }
                }
            }

            // Step 4: Re-enable foreign key checks
            Schema::enableForeignKeyConstraints();

            // Step 5: Clear caches
            $this->notify($onProgress, 'جاري تنظيف الذاكرة المؤقتة...');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');

            $result['completed_at'] = now()->toDateTimeString();

        } catch (\Exception $e) {
            Schema::enableForeignKeyConstraints();
            $result['success'] = false;
            $result['errors'][] = 'خطأ عام: ' . $e->getMessage();
        }

        // Log the completion
        activity()
            ->causedBy(Auth::user())
            ->withProperties($result)
            ->log($result['success'] ? 'اكتملت عملية إعادة تعيين النظام بنجاح' : 'فشلت عملية إعادة تعيين النظام');

        return $result;
    }

    /**
     * Reset auto-increment for a table
     */
    protected function resetAutoIncrement(string $table): void
    {
        $connection = config('database.default');

        if ($connection === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
        } elseif ($connection === 'sqlite') {
            DB::statement("DELETE FROM sqlite_sequence WHERE name = '{$table}'");
        } elseif ($connection === 'pgsql') {
            // Get the sequence name and reset it
            $sequences = DB::select("SELECT pg_get_serial_sequence('{$table}', 'id') as seq");
            if (!empty($sequences) && $sequences[0]->seq) {
                DB::statement("ALTER SEQUENCE {$sequences[0]->seq} RESTART WITH 1");
            }
        }
    }

    /**
     * Notify progress callback if provided
     */
    protected function notify(?callable $callback, string $message): void
    {
        if ($callback) {
            $callback($message);
        }
    }

    /**
     * Verify user password for confirmation
     */
    public function verifyPassword(string $password): bool
    {
        return Auth::check() && \Hash::check($password, Auth::user()->password);
    }

    /**
     * Verify confirmation phrase
     */
    public function verifyConfirmationPhrase(string $phrase): bool
    {
        return $phrase === __('admin.system_reset.confirmation_phrase');
    }
}
