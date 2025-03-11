<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('payment_types', function (Blueprint $table): void {
            $table->boolean('is_direct_debit')->default(false)->after('is_active');
            $table->boolean('is_purchase')->default(false)->after('is_default');
            $table->boolean('is_sales')->default(true)->after('is_purchase');
            $table->boolean('requires_manual_transfer')->default(false)->after('is_sales');
        });
    }

    public function down(): void
    {
        Schema::table('payment_types', function (Blueprint $table): void {
            $table->dropColumn([
                'is_direct_debit',
                'is_purchase',
                'is_sales',
                'requires_manual_transfer',
            ]);
        });
    }
};
