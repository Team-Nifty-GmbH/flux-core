<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('contact_industry', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('contact_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('industry_id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_industry');
    }
};
