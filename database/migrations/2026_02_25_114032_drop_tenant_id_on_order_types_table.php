<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('email_template_id');
        });

        // Fill tenant_id from order_type_tenant table
        DB::statement('
            UPDATE order_types
            LEFT JOIN order_type_tenant AS ott ON ott.order_type_id = order_types.id
            SET order_types.tenant_id = COALESCE(
                ott.tenant_id,
                (SELECT id FROM tenants WHERE deleted_at IS NULL ORDER BY is_default DESC LIMIT 1)
            )'
        );

        DB::table('order_types')
            ->whereNull('tenant_id')
            ->delete();

        Schema::table('order_types', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();

            $table->foreign('tenant_id')->references('id')->on('tenants');
        });
    }
};
