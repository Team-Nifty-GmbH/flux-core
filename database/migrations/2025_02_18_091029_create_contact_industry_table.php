<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('contact_industry', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('industry_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_industries');
    }
};
