<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('categorizables', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->morphs('categorizable');

            $table->unique(
                ['category_id', 'categorizable_id', 'categorizable_type'], 'categorizables_ids_type_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorizables');
    }
};
