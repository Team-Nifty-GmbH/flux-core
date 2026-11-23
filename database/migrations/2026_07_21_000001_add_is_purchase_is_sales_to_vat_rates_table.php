<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('vat_rates', function (Blueprint $table): void {
            // Both default to true so existing rates keep appearing in every select.
            $table->boolean('is_purchase')->default(true)->after('is_default');
            $table->boolean('is_sales')->default(true)->after('is_purchase');
        });
    }

    public function down(): void
    {
        Schema::table('vat_rates', function (Blueprint $table): void {
            $table->dropColumn(['is_purchase', 'is_sales']);
        });
    }
};
