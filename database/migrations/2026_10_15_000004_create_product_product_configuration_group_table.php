<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('product_product_configuration_group', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('product_id')
                ->constrained('products', indexName: 'ppcg_product_id_foreign')
                ->cascadeOnDelete();
            $table->foreignId('product_configuration_group_id')
                ->constrained('product_configuration_groups', indexName: 'ppcg_group_id_foreign')
                ->cascadeOnDelete();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort')->default(0);

            $table->unique(['product_id', 'product_configuration_group_id'], 'ppcg_product_group_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_product_configuration_group');
    }
};
