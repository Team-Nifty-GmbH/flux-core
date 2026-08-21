<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('lots', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('lot_number');
            $table->string('supplier_lot_number')->nullable();
            $table->date('produced_at')->nullable();
            $table->date('expires_at')
                ->nullable()
                ->comment('Best before date, the sort key for FEFO stock removal.');
            $table->timestamp('blocked_at')
                ->nullable()
                ->comment('A blocked lot still counts towards stock but is excluded from every removal.');
            $table->text('description')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->unique(['product_id', 'lot_number']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};
