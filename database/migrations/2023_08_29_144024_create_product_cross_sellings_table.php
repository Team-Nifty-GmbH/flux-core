<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_cross_sellings', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name');
            $table->integer('order_column')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_cross_sellings');
    }
};
