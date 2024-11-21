<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_user')) {
            return;
        }

        Schema::create('order_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index('order_user_order_id_foreign');
            $table->unsignedBigInteger('user_id')->index('order_user_user_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_user');
    }
};
