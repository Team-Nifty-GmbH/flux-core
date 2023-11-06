<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paid_commissions', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->unsignedBigInteger('commission_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('commission', 40, 10);
            $table->timestamps();

            $table->foreign('commission_id')->references('id')->on('commissions');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paid_commissions');
    }
};
