<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('address_address_type')) {
            return;
        }

        Schema::create('address_address_type', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')->comment('A unique identifier number for the table addresses.');
            $table->unsignedBigInteger('address_type_id')->index('address_address_type_address_type_id_foreign')->comment('A unique identifier number for the table address types.');

            $table->primary(['address_id', 'address_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_address_type');
    }
};
