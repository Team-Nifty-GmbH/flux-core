<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('resource_bookings', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('resource_id');
            $table->nullableMorphs('assignable');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->dateTimeTz('start');
            $table->dateTimeTz('end');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->foreign('resource_id')
                ->references('id')
                ->on('resources')
                ->cascadeOnDelete();
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->nullOnDelete();

            $table->index(['resource_id', 'start', 'end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_bookings');
    }
};
