<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('address_address_type', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('address_id')->constrained('addresses')->cascadeOnDelete();
            $table->foreignId('address_type_id')->constrained('address_types')->cascadeOnDelete();

            $table->unique(['address_id', 'address_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_address_type');
    }
};
