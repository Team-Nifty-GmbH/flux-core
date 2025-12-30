<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('target_user', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('target_id')->constrained('targets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('target_share', 40, 10)->nullable();
            $table->boolean('is_percentage')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_user');
    }
};
