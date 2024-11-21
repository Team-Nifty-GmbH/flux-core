<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('discount_discount_group')) {
            return;
        }

        Schema::create('discount_discount_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discount_id')->index('discount_discount_group_discount_id_foreign');
            $table->unsignedBigInteger('discount_group_id')->index('discount_discount_group_discount_group_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_discount_group');
    }
};
