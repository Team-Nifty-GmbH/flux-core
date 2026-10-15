<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('product_configuration_defaults', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products', indexName: 'pc_defaults_product_id_foreign')
                ->cascadeOnDelete();
            $table->foreignId('product_configuration_group_item_id')
                ->constrained('product_configuration_group_items', indexName: 'pc_defaults_item_id_foreign')
                ->cascadeOnDelete();
            $table->unsignedInteger('amount')->default(1);

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();

            $table->unique(['product_id', 'product_configuration_group_item_id'], 'pc_defaults_product_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_configuration_defaults');
    }
};
