<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('product_supplier', function (Blueprint $table): void {
            $table->foreignId('packaging_unit_id')
                ->nullable()
                ->after('contact_id')
                ->constrained('units')
                ->nullOnDelete();
            $table->string('supplier_product_number')
                ->nullable()
                ->after('manufacturer_product_number');
            $table->string('supplier_product_name')
                ->nullable()
                ->after('supplier_product_number');
            $table->decimal('packaging_amount', 40, 10)
                ->nullable()
                ->after('supplier_product_name');
            $table->text('note')
                ->nullable()
                ->after('purchase_price');
        });
    }

    public function down(): void
    {
        Schema::table('product_supplier', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('packaging_unit_id');
            $table->dropColumn([
                'supplier_product_number',
                'supplier_product_name',
                'packaging_amount',
                'note',
            ]);
        });
    }
};
