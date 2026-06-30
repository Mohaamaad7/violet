<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComboRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $category1 = \App\Models\Category::first();
        $category2 = \App\Models\Category::skip(1)->first();

        if (!$category1) return;

        $rule = \App\Models\ComboRule::create([
            'name' => 'خصم العودة للمدارس',
            'description' => 'احصل على خصم 20% عند شراء قطعتين من أي قسم',
            'is_active' => true,
            'discount_type' => 'percentage',
            'discount_percentage' => 20,
            'priority' => 10,
        ]);

        $rule->conditions()->create([
            'condition_type' => 'category',
            'category_id' => $category1->id,
            'required_quantity' => 1,
        ]);

        if ($category2) {
            $rule->conditions()->create([
                'condition_type' => 'category',
                'category_id' => $category2->id,
                'required_quantity' => 1,
            ]);
        } else {
            $rule->conditions()->create([
                'condition_type' => 'category',
                'category_id' => $category1->id,
                'required_quantity' => 1,
            ]);
        }
    }
}
