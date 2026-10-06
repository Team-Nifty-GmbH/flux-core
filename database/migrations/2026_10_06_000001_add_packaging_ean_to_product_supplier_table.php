<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('product_supplier', function (Blueprint $table): void {
            $table->string('packaging_ean')
                ->nullable()
                ->index()
                ->after('items_per_packaging');
        });
    }

    public function down(): void
    {
        Schema::table('product_supplier', function (Blueprint $table): void {
            $table->dropIndex(['packaging_ean']);
            $table->dropColumn('packaging_ean');
        });
    }
};
