<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_carryover_rules', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);

            $table->string('name');
            $table->decimal('max_days', 8, 2)->nullable();
            $table->unsignedTinyInteger('expires_at_month')->nullable();
            $table->unsignedTinyInteger('expires_at_day')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

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
        Schema::dropIfExists('vacation_carryover_rules');
    }
};
