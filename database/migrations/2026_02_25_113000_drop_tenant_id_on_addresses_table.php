<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('language_id');
        });

        // Fill tenant_id from contacts table
        DB::statement('
            UPDATE addresses
            INNER JOIN contacts ON contacts.id = addresses.contact_id
            SET addresses.tenant_id = contacts.tenant_id'
        );

        DB::table('addresses')
            ->whereNull('tenant_id')
            ->delete();

        Schema::table('addresses', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();

            $table->foreign('tenant_id')->references('id')->on('tenants');
        });
    }
};
