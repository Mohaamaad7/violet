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
     * 
     * IMPORTANT: 'main_table' is used for accurate counting (shows only primary records)
     * 'tables' contains all related tables to be deleted (including foreign key dependencies)
     */
    protected array $categories = [
        'customers' => [
            'label' => 'العملاء',
            'icon' => 'heroicon-o-users',
            'main_table' => 'customers',
            'tables' => ['wishlists', 'cart_items', 'carts', 'shipping_addresses', 'customers'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
        ],
        'orders' => [
            'label' => 'الطلبات',
            'icon' => 'heroicon-o-shopping-bag',
            'main_table' => 'orders',
            'tables' => ['order_items', 'orders'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
        ],
        'returns' => [
            'label' => 'المرتجعات',
            'icon' => 'heroicon-o-arrow-uturn-left',
            'main_table' => 'returns',
            'tables' => ['return_items', 'returns'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
        ],
        'products' => [
            'label' => 'المنتجات والتصنيفات',
            'icon' => 'heroicon-o-cube',
            'main_table' => 'products',
            'tables' => ['product_images', 'product_reviews', 'product_variants', 'products', 'categories'],
            'directories' => ['products'],
            'media_models' => [Product::class, Category::class],
            'dependencies' => ['orders', 'returns'],
        ],
        'inventory' => [
            'label' => 'حركات المخزون',
            'icon' => 'heroicon-o-archive-box',
            'main_table' => 'stock_movements',
            'tables' => ['stock_count_items', 'stock_counts', 'stock_movements', 'batches', 'warehouses'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
        ],
        'finance' => [
            'label' => 'المدفوعات والعمولات',
            'icon' => 'heroicon-o-banknotes',
            'main_table' => 'payments',
            'tables' => ['payments', 'commission_payouts', 'influencer_commissions', 'code_usages'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
        ],
        'influencers' => [
            'label' => 'المؤثرين وأكواد الخصم',
            'icon' => 'heroicon-o-user-group',
            'main_table' => 'influencers',
            'tables' => ['influencer_applications', 'influencers', 'discount_codes'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => ['finance'],
        ],
        'content' => [
            'label' => 'المحتوى (مقالات، صفحات، بانرات)',
            'icon' => 'heroicon-o-document-text',
            'main_table' => 'pages',
            'tables' => ['blog_posts', 'pages', 'banners', 'sliders', 'help_entries'],
            'directories' => ['banners', 'sliders'],
            'media_models' => [],
            'dependencies' => [],
        ],
        'email_logs' => [
            'label' => 'سجلات الإيميلات',
            'icon' => 'heroicon-o-envelope',
            'main_table' => 'email_logs',
            'tables' => ['email_logs'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
        ],

        'failed_jobs' => [
            'label' => 'المهام الفاشلة',
            'icon' => 'heroicon-o-exclamation-triangle',
            'main_table' => 'failed_jobs',
            'tables' => ['failed_jobs', 'jobs'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
        ],
        'staff' => [
            'label' => 'الموظفين (ما عدا أنت)',
            'icon' => 'heroicon-o-user-circle',
            'main_table' => 'users',
            'tables' => ['users'],
            'directories' => [],
            'media_models' => [],
            'dependencies' => [],
            'special' => true,
        ],
        'activity_logs' => [
            'label' => 'سجلات النشاط',
            'icon' => 'heroicon-o-clipboard-document-list',
            'main_table' => 'activity_log',
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
            'categories' => ['customers', 'orders', 'returns', 'products', 'inventory', 'finance', 'influencers', 'content', 'email_logs', 'failed_jobs', 'activity_logs'],
        ],
        'developer_mode' => [
            'label' => 'وضع المطور (إبقاء المنتجات)',
            'categories' => ['customers', 'orders', 'returns', 'inventory', 'finance', 'email_logs', 'failed_jobs', 'activity_logs'],
        ],
        'keep_products' => [
            'label' => 'الاحتفاظ بالمنتجات والإعدادات فقط',
            'categories' => ['customers', 'orders', 'returns', 'inventory', 'finance', 'influencers', 'content', 'staff', 'email_logs', 'failed_jobs', 'activity_logs'],
        ],
        'clear_logs_only' => [
            'label' => 'تنظيف السجلات فقط',
            'categories' => ['email_logs', 'failed_jobs', 'activity_logs'],
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
     * 
     * Uses main_table for accurate counting (shows only primary records)
     */
    public function getCategoryStats(): array
    {
        $stats = [];

        foreach ($this->categories as $key => $category) {
            $mainTable = $category['main_table'] ?? $category['tables'][0] ?? null;
            $count = 0;

            if ($mainTable && Schema::hasTable($mainTable)) {
                $count = DB::table($mainTable)->count();

                // Special handling for staff - exclude current user
                if ($mainTable === 'users' && Auth::check()) {
                    $count = max(0, $count - 1);
                }
            }

            // Also get count of related tables for tooltip
            $relatedCount = 0;
            foreach ($category['tables'] as $table) {
                if ($table !== $mainTable && Schema::hasTable($table)) {
                    $relatedCount += DB::table($table)->count();
                }
            }

            $stats[$key] = [
                'label' => $category['label'],
                'icon' => $category['icon'],
                'count' => $count,
                'related_count' => $relatedCount,
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
