<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tenants', 'product_variant_inheritance_enabled')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table): void {
            $table->boolean('product_variant_inheritance_enabled')
                ->default(false)
                ->after('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            if (Schema::hasColumn('tenants', 'product_variant_inheritance_enabled')) {
                $table->dropColumn('product_variant_inheritance_enabled');
            }
        });
    }
};
