<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('media_folders')
                ->cascadeOnDelete();
            $table->string('name');
            $table->text('slug');
            $table->unsignedInteger('max_files')->nullable();
            $table->json('mime_types')->nullable();
            $table->boolean('is_readonly')->default(false);
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
        Schema::dropIfExists('media_folders');
    }
};
