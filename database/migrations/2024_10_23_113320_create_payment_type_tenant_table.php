<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_type_tenant')) {
            return;
        }

        Schema::create('payment_type_tenant', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('payment_type_id')->constrained('payment_types')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_type_tenant');
    }
};
