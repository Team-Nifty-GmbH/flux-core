<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('serial_number_ranges')) {
            return;
        }

        Schema::create('serial_number_ranges', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->string('unique_key')->unique();
            $table->unsignedBigInteger('client_id')->nullable()->index('serial_number_ranges_client_id_foreign');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('type');
            $table->unsignedBigInteger('current_number')->default(0);
            $table->string('prefix')->nullable();
            $table->string('suffix')->nullable();
            $table->text('description')->nullable();
            $table->integer('length')->nullable()->comment('The length of the serial number. The Serialnumber will be padded with leading zeros.');
            $table->boolean('is_pre_filled')->default(false)->comment('A flag to indicate if the serialnumber is picked from the serial_numbers table.');
            $table->boolean('is_randomized')->default(false)->comment('A flag indicating whether this range generates a random serial number.');
            $table->boolean('stores_serial_numbers')->default(false)->comment('A flag indicating whether this range creates a new serial_numbers record.');
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_number_ranges');
    }
};
