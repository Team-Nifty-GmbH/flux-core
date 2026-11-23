<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('storage_areas', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('storage_areas')
                ->cascadeOnDelete();
            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();

            $table->string('code')
                ->comment('The scannable code of this storage area, unique per warehouse among the live ones.');
            $table->string('name')->nullable();
            $table->string('storage_area_type_enum');
            $table->unsignedInteger('sort_number')
                ->default(0)
                ->comment('Walking order within the warehouse, used for picking routes.');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_storage_location')
                ->default(false)
                ->comment('Only storage areas flagged as storage location may carry stock.');

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->index(['warehouse_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_areas');
    }
};
