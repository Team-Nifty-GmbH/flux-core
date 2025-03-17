<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table): void {
            $table->foreignId('credit_note_order_position_id')
                ->after('order_position_id')
                ->nullable()
                ->constrained('order_positions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('credit_note_order_position_id');
        });
    }
};
