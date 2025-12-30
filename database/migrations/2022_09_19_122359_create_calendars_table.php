<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('calendars', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('calendars')
                ->nullOnDelete();
            $table->string('model_type')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->default('#3f51b5');
            $table->json('custom_properties')->nullable();
            $table->boolean('has_notifications')->default(true);
            $table->boolean('has_repeatable_events')->default(true);
            $table->boolean('is_editable')->default(true);
            $table->boolean('is_group')->default(false);
            $table->boolean('is_public')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};
