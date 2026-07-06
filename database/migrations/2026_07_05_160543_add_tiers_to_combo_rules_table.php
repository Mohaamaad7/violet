<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('combo_rules', function (Blueprint $table) {
            $table->json('tiers')->nullable()->after('show_on_homepage');
        });

        // Migrate existing rules to the new JSON tiers structure
        $rules = \Illuminate\Support\Facades\DB::table('combo_rules')->get();
        
        foreach ($rules as $rule) {
            // Find the required quantity from the first condition, default to 1
            $condition = \Illuminate\Support\Facades\DB::table('combo_rule_conditions')
                ->where('combo_rule_id', $rule->id)
                ->first();
                
            $quantity = $condition ? $condition->required_quantity : 1;
            
            $tier = [
                'quantity' => $quantity,
                'discount_type' => $rule->discount_type ?? 'percentage',
                'discount_percentage' => $rule->discount_percentage,
                'fixed_price' => $rule->fixed_price,
            ];
            
            \Illuminate\Support\Facades\DB::table('combo_rules')
                ->where('id', $rule->id)
                ->update(['tiers' => json_encode([$tier])]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('combo_rules', function (Blueprint $table) {
            $table->dropColumn('tiers');
        });
    }
};

