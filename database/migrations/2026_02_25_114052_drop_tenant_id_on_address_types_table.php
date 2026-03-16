<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('address_types', function (Blueprint $table): void {
            $table->dropUnique('address_types_tenant_id_address_type_code_unique');

            $table->dropConstrainedForeignId('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('address_types', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('uuid');
        });

        // Fill tenant_id from address_type_tenant table
        DB::statement('
            UPDATE address_types
            LEFT JOIN address_type_tenant AS att ON att.address_type_id = address_types.id
            SET address_types.tenant_id = COALESCE(
                att.tenant_id,
                (SELECT id FROM tenants WHERE deleted_at IS NULL ORDER BY is_default DESC LIMIT 1)
            )'
        );

        DB::table('address_types')
            ->whereNull('tenant_id')
            ->delete();

        Schema::table('address_types', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();

            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->unique(['tenant_id', 'address_type_code']);
        });
    }
};
