<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('mail_account_user', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('mail_account_id')->constrained('mail_accounts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->unique(['mail_account_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_account_user');
    }
};
