<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('document_types');
    }

    public function down(): void
    {
        Schema::create('document_types', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->json('name');
            $table->json('description')->nullable();
            $table->json('additional_header')->nullable();
            $table->json('additional_footer')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('updated_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
