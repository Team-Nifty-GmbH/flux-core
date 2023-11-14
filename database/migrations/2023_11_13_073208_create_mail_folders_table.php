<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_folders', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('mail_account_id')->constrained('mail_accounts')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('mail_folders')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_folders');
    }
};
