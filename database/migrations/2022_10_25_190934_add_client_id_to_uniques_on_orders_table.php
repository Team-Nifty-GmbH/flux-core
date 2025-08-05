<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique('orders_order_number_unique');
            $table->unique(['order_number', 'client_id']);

            $table->dropUnique('orders_invoice_number_unique');
            $table->unique(['invoice_number', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique('orders_order_number_client_id_unique');
            $table->unique('order_number');

            $table->dropUnique('orders_invoice_number_client_id_unique');
            $table->unique('invoice_number');
        });
    }
};
