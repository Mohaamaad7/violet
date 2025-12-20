<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default warehouse if not exists
        Warehouse::firstOrCreate(
            ['code' => 'WH-001'],
            [
                'name' => 'المخزن الرئيسي',
                'code' => 'WH-001',
                'address' => null,
                'phone' => null,
                'is_default' => true,
                'is_active' => true,
            ]
        );
    }
}
