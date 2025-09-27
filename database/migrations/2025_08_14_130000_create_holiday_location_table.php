<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('holiday_location', function (Blueprint $table): void {
            $table->id('pivot_id');

            $table->foreignId('holiday_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('location_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['holiday_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_location');
    }
};
