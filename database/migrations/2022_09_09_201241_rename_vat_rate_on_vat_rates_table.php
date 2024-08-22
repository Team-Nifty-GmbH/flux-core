<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('vat_rates', function (Blueprint $table) {
            $table->renameColumn('rate', 'rate_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('vat_rates', function (Blueprint $table) {
            $table->renameColumn('rate_percentage', 'rate');
        });
    }
};
