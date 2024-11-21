<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('snapshots')) {
            return;
        }

        Schema::create('snapshots', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->json('snapshot');
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
