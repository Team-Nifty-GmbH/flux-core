<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categorizable')) {
            return;
        }

        Schema::create('categorizable', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();
            $table->morphs('categorizable');

            $table->unique(
                ['category_id', 'categorizable_id', 'categorizable_type'],
                'categorizable_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorizable');
    }
};
