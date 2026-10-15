<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('product_configuration_group_items', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('product_configuration_group_id')
                ->constrained('product_configuration_groups', indexName: 'pcg_items_group_id_foreign')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('products', indexName: 'pcg_items_product_id_foreign')
                ->cascadeOnDelete();
            $table->decimal('surcharge', 40, 10)->nullable();
            $table->unsignedInteger('max_amount')->default(1);
            $table->unsignedInteger('sort')->default(0);

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();

            $table->unique(['product_configuration_group_id', 'product_id'], 'pcg_items_group_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_configuration_group_items');
    }
};
