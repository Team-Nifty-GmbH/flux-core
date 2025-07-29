<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('record_origins')) {
            Schema::create('record_origins', function (Blueprint $table): void {
                $table->id();
                $table->string('model_type')->index();
                $table->string('name');
                $table->boolean('is_active')->default(true);

                $table->timestamp('created_at')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_by')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('record_origins');
    }
};
