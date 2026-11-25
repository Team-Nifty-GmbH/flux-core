<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_files', function (Blueprint $table): void {
            $table->id();
            $table->string('hash', 32)->unique();
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_files');
    }
};
