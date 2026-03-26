<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('contact_tenant', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
        });

        // Fill table from contacts table
        DB::statement(
            'INSERT INTO contact_tenant (contact_id, tenant_id)
            SELECT contacts.id, contacts.tenant_id
            FROM contacts'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_tenant');
    }
};
