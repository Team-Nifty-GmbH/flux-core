<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('paid_commissions');
    }

    public function down(): void
    {
        Schema::create('paid_commissions', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('commission_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('commission', 40, 10);
            $table->timestamps();

            $table->foreign('commission_id')->references('id')->on('commissions');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
