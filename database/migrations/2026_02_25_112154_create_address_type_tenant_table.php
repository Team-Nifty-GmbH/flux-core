<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('address_type_tenant', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('address_type_id')->constrained('address_types')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
        });

        // Fill table from address_types table
        DB::statement(
            'INSERT INTO address_type_tenant (address_type_id, tenant_id)
            SELECT address_types.id, address_types.tenant_id
            FROM address_types'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('address_type_tenant');
    }
};
