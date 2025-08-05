<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->foreignId('contact_bank_connection_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->foreignId('contact_bank_connection_id')->nullable(false)->change();
        });
    }
};
