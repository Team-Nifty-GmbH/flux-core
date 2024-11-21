<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('address_serial_number', function (Blueprint $table) {
            $table->foreign(['address_id'])->references(['id'])->on('addresses')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['serial_number_id'])->references(['id'])->on('serial_numbers')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('address_serial_number', function (Blueprint $table) {
            $table->dropForeign('address_serial_number_address_id_foreign');
            $table->dropForeign('address_serial_number_serial_number_id_foreign');
        });
    }
};
