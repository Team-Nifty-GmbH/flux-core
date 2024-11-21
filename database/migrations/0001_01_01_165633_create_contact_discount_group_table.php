<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contact_discount_group')) {
            return;
        }

        Schema::create('contact_discount_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('discount_group_id')->index('contact_discount_group_discount_group_id_foreign');

            $table->unique(['contact_id', 'discount_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_discount_group');
    }
};
