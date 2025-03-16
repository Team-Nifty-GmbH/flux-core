<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('mail_account_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mail_account_id')->constrained('mail_accounts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_account_user');
    }
};
