<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table): void {
            $table->foreignId('tax_exemption_id')
                ->nullable()
                ->after('currency_id')
                ->constrained('vat_rates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tax_exemption_id');
        });
    }
};
