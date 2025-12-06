<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Task 5.1: Advanced Search & Filtering System
     * Adds database indexes to optimize product filtering queries.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Individual column indexes for filtering
            // Note: Using raw SQL to check if index exists (Laravel 12 compatible)
            
            $this->addIndexIfNotExists($table, 'products', 'price', 'products_price_index');
            $this->addIndexIfNotExists($table, 'products', 'sale_price', 'products_sale_price_index');
            $this->addIndexIfNotExists($table, 'products', 'average_rating', 'products_average_rating_index');
            $this->addIndexIfNotExists($table, 'products', 'sales_count', 'products_sales_count_index');
            $this->addIndexIfNotExists($table, 'products', 'brand', 'products_brand_index');
            $this->addIndexIfNotExists($table, 'products', 'stock', 'products_stock_index');
            $this->addIndexIfNotExists($table, 'products', 'is_featured', 'products_is_featured_index');
        });
        
        // Composite indexes using raw SQL
        $this->addCompositeIndexIfNotExists(
            'products',
            ['status', 'category_id'],
            'products_status_category_index'
        );
        
        $this->addCompositeIndexIfNotExists(
            'products',
            ['is_featured', 'sales_count', 'created_at'],
            'products_default_sort_index'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop indexes safely
            $this->dropIndexIfExists('products', 'products_default_sort_index');
            $this->dropIndexIfExists('products', 'products_status_category_index');
            $this->dropIndexIfExists('products', 'products_is_featured_index');
            $this->dropIndexIfExists('products', 'products_stock_index');
            $this->dropIndexIfExists('products', 'products_brand_index');
            $this->dropIndexIfExists('products', 'products_sales_count_index');
            $this->dropIndexIfExists('products', 'products_average_rating_index');
            $this->dropIndexIfExists('products', 'products_sale_price_index');
            $this->dropIndexIfExists('products', 'products_price_index');
        });
    }
    
    /**
     * Check if an index exists on a table (MySQL compatible)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($result) > 0;
    }
    
    /**
     * Add index if it doesn't exist
     */
    private function addIndexIfNotExists(Blueprint $table, string $tableName, string $column, string $indexName): void
    {
        if (!$this->indexExists($tableName, $indexName)) {
            $table->index($column, $indexName);
        }
    }
    
    /**
     * Add composite index if it doesn't exist
     */
    private function addCompositeIndexIfNotExists(string $tableName, array $columns, string $indexName): void
    {
        if (!$this->indexExists($tableName, $indexName)) {
            $columnList = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
            DB::statement("CREATE INDEX `{$indexName}` ON `{$tableName}` ({$columnList})");
        }
    }
    
    /**
     * Drop index if it exists
     */
    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            DB::statement("DROP INDEX `{$indexName}` ON `{$tableName}`");
        }
    }
};
