<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('address_serial_number', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_id')->constrained('addresses')->cascadeOnDelete();
            $table->foreignId('serial_number_id')->constrained('serial_numbers')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_serial_number');
    }
};
