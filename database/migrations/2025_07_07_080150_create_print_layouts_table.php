<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('print_layouts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('model_type');
            $table->string('name');
            $table->json('margin')->nullable();
            $table->json('header')->nullable();
            $table->json('footer')->nullable();
            $table->json('first_page_header')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_layouts');
    }
};
