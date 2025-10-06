<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('print_layout_snippets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('print_layout_id');
            $table->foreign('print_layout_id')
                ->references('id')->on('print_layouts')
                ->onDelete('cascade');
            $table->longText('content');

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_layout_snippets');
    }
};
