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
        Schema::create('discount_discount_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discount_id');
            $table->unsignedBigInteger('discount_group_id');

            $table->foreign('discount_id')
                ->references('id')
                ->on('discounts')
                ->cascadeOnDelete();

            $table->foreign('discount_group_id')
                ->references('id')
                ->on('discount_groups')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_discount_group');
    }
};
