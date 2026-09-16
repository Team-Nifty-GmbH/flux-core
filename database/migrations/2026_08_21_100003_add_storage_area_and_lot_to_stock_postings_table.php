<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('stock_postings', function (Blueprint $table): void {
            $table->foreignId('lot_id')
                ->nullable()
                ->after('uuid')
                ->constrained('lots')
                ->nullOnDelete();
            $table->foreignId('storage_area_id')
                ->nullable()
                ->after('serial_number_id')
                ->comment('Null means the stock sits in the warehouse without a known storage area.')
                ->constrained('storage_areas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_postings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('lot_id');
            $table->dropConstrainedForeignId('storage_area_id');
        });
    }
};
