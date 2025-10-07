<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->date('payment_target_date')->nullable()->after('invoice_date');
            $table->date('payment_discount_target_date')->nullable()->after('payment_target_date');
            $table->decimal('payment_discount_percent', 11, 10)
                ->nullable()
                ->after('payment_discount_target_date');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->dropColumn([
                'payment_target_date',
                'payment_discount_target_date',
                'payment_discount_percent',
            ]);
        });
    }
};
