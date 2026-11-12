<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('product_configuration_rule_items', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('product_configuration_rule_id')
                ->constrained('product_configuration_rules', indexName: 'pc_rule_items_rule_id_foreign')
                ->cascadeOnDelete();
            $table->foreignId('product_configuration_group_item_id')
                ->constrained('product_configuration_group_items', indexName: 'pc_rule_items_item_id_foreign')
                ->cascadeOnDelete();

            $table->unique(
                ['product_configuration_rule_id', 'product_configuration_group_item_id'],
                'pc_rule_items_rule_item_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_configuration_rule_items');
    }
};
