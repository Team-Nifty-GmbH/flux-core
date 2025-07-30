<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('client_payment_type', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('payment_type_id')->constrained('payment_types')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_payment_type');
    }
};
