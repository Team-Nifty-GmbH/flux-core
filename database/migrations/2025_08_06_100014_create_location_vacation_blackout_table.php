<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('location_vacation_blackout', function (Blueprint $table): void {
            $table->id('pivot_id');

            $table->foreignId('location_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('vacation_blackout_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['location_id', 'vacation_blackout_id'], 'location_vacation_blackout_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_vacation_blackout');
    }
};
