<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('serial_numbers', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('serial_number_range_id')->nullable();
            $table->string('serial_number');
            $table->string('supplier_serial_number')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();

            $table->foreign('serial_number_range_id')
                ->references('id')
                ->on('serial_number_ranges')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_numbers');
    }
};
