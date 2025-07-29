<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('discounts')) {
            Schema::create('discounts', function (Blueprint $table): void {
                $table->id();
                $table->char('uuid', 36);
                $table->morphs('model');
                $table->string('name')->nullable();
                $table->decimal('discount', 40, 10)
                    ->comment('The number containing the actual discount.');
                $table->decimal('discount_percentage', 40, 10)
                    ->nullable();
                $table->decimal('discount_flat', 40, 10)
                    ->nullable();
                $table->timestamp('from')->nullable();
                $table->timestamp('till')->nullable();
                $table->unsignedInteger('order_column')->nullable();
                $table->boolean('is_percentage')->default(true)
                    ->comment('A boolean deciding if this discount is a percentage instead of a discount in its respective currency.');
                $table->timestamp('created_at')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->string('deleted_by')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
