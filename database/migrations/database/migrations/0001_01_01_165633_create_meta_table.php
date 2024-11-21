<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('meta')) {
            return;
        }

        Schema::create('meta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('additional_column_id')->nullable()->index('meta_additional_column_id_foreign');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('key')->nullable();
            $table->longText('value')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta');
    }
};
