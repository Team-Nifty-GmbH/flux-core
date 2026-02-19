<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('discount_discount_group', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('discount_group_id')->constrained('discount_groups')->cascadeOnDelete();
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();

            $table->unique(['discount_group_id', 'discount_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_discount_group');
    }
};
