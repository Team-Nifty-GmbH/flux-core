<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('order_type_tenant', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('order_type_id')->constrained('order_types')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
        });

        // Fill table from order_types table
        DB::statement(
            'INSERT INTO order_type_tenant (order_type_id, tenant_id)
            SELECT order_types.id, order_types.tenant_id
            FROM order_types'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('order_type_tenant');
    }
};
