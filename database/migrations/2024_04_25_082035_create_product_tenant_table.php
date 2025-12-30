<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_tenant')) {
            return;
        }

        Schema::create('product_tenant', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            $table->unique(['product_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_tenant');
    }
};
