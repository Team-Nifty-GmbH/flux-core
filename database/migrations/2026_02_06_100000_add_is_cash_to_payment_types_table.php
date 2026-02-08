<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('payment_types', function (Blueprint $table): void {
            $table->foreignId('bank_connection_id')
                ->nullable()
                ->after('uuid')
                ->constrained('bank_connections')
                ->nullOnDelete();
            $table->boolean('is_cash')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('payment_types', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('bank_connection_id');
            $table->dropColumn('is_cash');
        });
    }
};
