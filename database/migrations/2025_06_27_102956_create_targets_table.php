<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->decimal('target_value', 40, 4);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('model_type');
            $table->string('timeframe_column');
            $table->string('aggregate_type');
            $table->string('aggregate_column');
            $table->string('owner_column');
            $table->json('constraints')->nullable();
            $table->unsignedTinyInteger('priority')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->foreign('parent_id')->references('id')->on('targets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
