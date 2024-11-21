<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('address_serial_number')) {
            return;
        }

        Schema::create('address_serial_number', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('address_id')->index('address_serial_number_address_id_foreign');
            $table->unsignedBigInteger('serial_number_id')->index('address_serial_number_serial_number_id_foreign');
            $table->unsignedInteger('quantity')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_serial_number');
    }
};
