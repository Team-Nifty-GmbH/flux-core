<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->foreignId('unit_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('units')
                ->nullOnDelete()
                ->comment('The unit this order-position was ordered in, when it differs from the article unit.');
        });
    }

    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('unit_id');
        });
    }
};
