<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('vat_rate_id')
                ->after('responsible_user_id')
                ->nullable()
                ->constrained('vat_rates')
                ->nullOnDelete();

            $table->dropColumn('tax_exemption_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_exemption_id')->nullable();
            $table->dropConstrainedForeignId('vat_rate_id');
        });
    }
};
