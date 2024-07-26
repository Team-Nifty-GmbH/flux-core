<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table) {
            $table->string('mandate_reference_number')->after('contact_bank_connection_id');

            $table->unique(['client_id', 'mandate_reference_number']);
        });
    }

    public function down(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table) {
            $table->dropUnique('sepa_mandates_client_id_mandate_reference_number_unique');
            $table->dropColumn('mandate_reference_number');
        });
    }
};
