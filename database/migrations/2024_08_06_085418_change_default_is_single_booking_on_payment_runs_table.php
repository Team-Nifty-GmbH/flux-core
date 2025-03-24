<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('payment_runs', function (Blueprint $table): void {
            $table->boolean('is_single_booking')->default(true)->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_runs', function (Blueprint $table): void {
            $table->boolean('is_single_booking')->default(false)->change();
        });
    }
};
