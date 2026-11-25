<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('order_project', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->unique(['order_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_project');
    }
};
