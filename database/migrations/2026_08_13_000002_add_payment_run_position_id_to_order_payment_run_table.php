<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_payment_run', function (Blueprint $table): void {
            $table->foreignId('payment_run_position_id')
                ->nullable()
                ->after('payment_run_id')
                ->constrained('payment_run_positions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_payment_run', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('payment_run_position_id');
        });
    }
};
