<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);

            $table->string('name');
            $table->date('date')->nullable()->index();
            $table->year('effective_from')->nullable();
            $table->year('effective_until')->nullable();
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedTinyInteger('day')->nullable();
            $table->string('day_part_enum');

            $table->boolean('is_active')->default(true);
            $table->boolean('is_half_day')->default(false);
            $table->boolean('is_recurring')->default(false);

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->index(['month', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
