<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table) {
            $table->renameColumn('bank_connection_id', 'contact_bank_connection_id');
        });
    }

    public function down(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table) {
            $table->renameColumn('contact_bank_connection_id', 'bank_connection_id');
        });
    }
};
