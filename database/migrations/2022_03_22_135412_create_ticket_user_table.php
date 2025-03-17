<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_user', function (Blueprint $table): void {
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id');

            $table->primary(['ticket_id', 'user_id']);
            $table->foreign('ticket_id')->references('id')->on('tickets');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_user');
    }
};
