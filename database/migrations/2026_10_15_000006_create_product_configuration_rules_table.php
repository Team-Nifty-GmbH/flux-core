<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('product_configuration_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_configuration_group_item_id')
                ->constrained('product_configuration_group_items', indexName: 'pc_rules_item_id_foreign')
                ->cascadeOnDelete();
            $table->string('rule_type_enum');
            $table->foreignId('target_group_id')
                ->nullable()
                ->constrained('product_configuration_groups', indexName: 'pc_rules_target_group_id_foreign')
                ->cascadeOnDelete();

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_configuration_rules');
    }
};
