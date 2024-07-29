<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->boolean('is_invoice_address')->after('is_main_address')->default(false);
            $table->boolean('is_delivery_address')->after('is_invoice_address')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['is_invoice_address', 'is_delivery_address']);
        });
    }
};
