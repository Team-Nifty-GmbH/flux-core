<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('work_times', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_position_id')->nullable()->unique();
            $table->unsignedBigInteger('work_time_type_id')->nullable();
            $table->nullableMorphs('trackable');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_pause')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('order_position_id')->references('id')->on('order_positions')->nullOnDelete();
            $table->foreign('work_time_type_id')->references('id')->on('work_time_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_times');
    }
};
