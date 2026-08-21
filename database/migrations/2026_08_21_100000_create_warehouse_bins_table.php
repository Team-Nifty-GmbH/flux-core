<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_bins', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('warehouse_bins')
                ->nullOnDelete();

            $table->string('code')
                ->comment('The scannable code of this bin, unique within its warehouse.');
            $table->string('name')->nullable();
            $table->string('warehouse_bin_type_enum');
            $table->boolean('is_storage_location')
                ->default(false)
                ->comment('Only bins flagged as storage location may carry stock.');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')
                ->default(0)
                ->comment('Walking order within the warehouse, used for picking routes.');

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->unique(['warehouse_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_bins');
    }
};
