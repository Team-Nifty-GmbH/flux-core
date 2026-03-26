<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropUnique('contacts_customer_number_tenant_id_unique');
            $table->dropConstrainedForeignId('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('record_origin_id');
        });

        // Fill tenant_id from contact_tenant table
        DB::statement('
            UPDATE contacts
            LEFT JOIN contact_tenant AS ct ON ct.contact_id = contacts.id
            SET contacts.tenant_id = COALESCE(
                ct.tenant_id,
                (SELECT id FROM tenants WHERE deleted_at IS NULL ORDER BY is_default DESC LIMIT 1)
            )'
        );

        DB::table('contacts')
            ->whereNull('tenant_id')
            ->delete();

        Schema::table('contacts', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();

            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->unique(['customer_number', 'tenant_id']);
        });
    }
};
