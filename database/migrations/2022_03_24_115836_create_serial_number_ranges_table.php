<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('serial_number_ranges', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('unique_key')->unique();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('type');
            $table->bigInteger('current_number', false, true)->default(0);
            $table->string('prefix')->nullable();
            $table->string('suffix')->nullable();
            $table->text('description')->nullable();
            $table->integer('length')->nullable()
                ->comment('The length of the serial number. The serial number will be padded with leading zeros.');
            $table->boolean('is_pre_filled')->default(false)
                ->comment('A flag to indicate if the serial number is picked from the serial_numbers table.');
            $table->boolean('is_randomized')->default(false)
                ->comment('A flag indicating whether this range generates a random serial number.');
            $table->boolean('stores_serial_numbers')->default(false)
                ->comment('A flag indicating whether this range creates a new serial_numbers record.');

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_number_ranges');
    }
};
