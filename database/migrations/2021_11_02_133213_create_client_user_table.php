<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('client_user', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('user_id');

            $table->primary(['client_id', 'user_id']);
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_user');
    }
};
