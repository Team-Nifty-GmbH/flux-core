<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contact_discount')) {
            return;
        }

        Schema::create('contact_discount', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('discount_id')->index('contact_discount_discount_id_foreign');

            $table->unique(['contact_id', 'discount_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_discount');
    }
};
