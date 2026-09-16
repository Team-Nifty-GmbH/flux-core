<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('product_supplier', function (Blueprint $table): void {
            $table->unsignedInteger('items_per_packaging')
                ->nullable()
                ->after('packaging_amount');
        });
    }

    public function down(): void
    {
        Schema::table('product_supplier', function (Blueprint $table): void {
            $table->dropColumn('items_per_packaging');
        });
    }
};
