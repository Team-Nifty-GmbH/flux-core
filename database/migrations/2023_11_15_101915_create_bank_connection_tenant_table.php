<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bank_connection_tenant')) {
            return;
        }

        Schema::create('bank_connection_tenant', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('bank_connection_id')->constrained('bank_connections')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            $table->unique(['bank_connection_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_connection_tenant');
    }
};
