<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('form_builder_forms');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->integer('extension_item_id')->nullable();
            $table->string('status')->default('NEW');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_responses');
    }
};
