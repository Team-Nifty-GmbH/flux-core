<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->decimal('weighted_gross_profit', 40, 10)->nullable()->after('score');
            $table->decimal('weighted_revenue', 40, 10)->nullable()->after('weighted_gross_profit');
        });

        $this->calculateWeightedValues();
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->dropColumn(['weighted_gross_profit', 'weighted_revenue']);
        });
    }

    protected function calculateWeightedValues(): void
    {
        DB::statement('UPDATE leads
            SET weighted_gross_profit = COALESCE(expected_gross_profit, 0) * COALESCE(probability_percentage, 0),
                weighted_revenue = COALESCE(expected_revenue, 0) * COALESCE(probability_percentage, 0)'
        );
    }
};
