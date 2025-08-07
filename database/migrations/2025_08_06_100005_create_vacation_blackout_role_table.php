<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_blackout_role', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vacation_blackout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            
            $table->unique(['vacation_blackout_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_blackout_role');
    }
};