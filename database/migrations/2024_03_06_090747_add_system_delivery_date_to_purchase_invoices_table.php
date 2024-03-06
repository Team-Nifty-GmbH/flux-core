<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->date('system_delivery_date_end')->nullable()->after('invoice_date');
            $table->date('system_delivery_date')->nullable()->after('invoice_date');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropColumn(['system_delivery_date', 'system_delivery_date_end']);
        });
    }
};
